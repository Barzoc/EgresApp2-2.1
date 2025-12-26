<?php

require_once __DIR__ . '/../modelo/ExpedienteQueue.php';
require_once __DIR__ . '/../lib/PDFProcessor.php';
require_once __DIR__ . '/../modelo/Egresado.php';
require_once __DIR__ . '/../lib/DriveFolderMapper.php';
require_once __DIR__ . '/../lib/GoogleDriveClient.php';

class ExpedienteProcessor
{
    private ExpedienteQueue $queue;
    private Egresado $egresado;
    private string $localBaseDir;
    private ?GoogleDriveClient $driveClient = null;

    public function __construct(?ExpedienteQueue $queue = null, ?Egresado $egresado = null)
    {
        $this->queue = $queue ?? new ExpedienteQueue();
        $this->egresado = $egresado ?? new Egresado();

        $base = realpath(__DIR__ . '/../assets/expedientes');
        if ($base === false) {
            $base = __DIR__ . '/../assets/expedientes';
        }
        if (!is_dir($base)) {
            mkdir($base, 0755, true);
        }
        $this->localBaseDir = $base;
    }

    public function processJobById(int $jobId): array
    {
        $job = $this->queue->acquireJobForProcessing($jobId);
        if (!$job) {
            return [
                'success' => false,
                'mensaje' => 'El expediente ya fue procesado o no existe en la cola.',
                'job_id' => $jobId,
            ];
        }

        return $this->processJob($job);
    }

    public function processJob(array $job): array
    {
        $jobId = (int) $job['id'];
        $overallStart = microtime(true);
        $lastCheckpoint = $overallStart;
        $timings = [
            'started_at' => date('c'),
        ];
        $recordTiming = function (string $label) use (&$timings, &$lastCheckpoint) {
            $now = microtime(true);
            $timings[$label] = round(($now - $lastCheckpoint) * 1000, 2);
            $lastCheckpoint = $now;
        };

        $driveInfo = [
            'drive_id' => $job['drive_id'] ?? null,
            'drive_link' => $job['drive_link'] ?? null,
        ];

        $resultPayload = [
            'filename' => $job['filename'],
            'filepath' => $job['filepath'],
            'id_expediente' => $job['id_expediente'],
            'drive_info' => $driveInfo,
            'timings' => $timings,
        ];

        $fields = [];
        $text = '';

        try {
            if (!file_exists($job['filepath'])) {
                throw new RuntimeException('El archivo PDF ya no existe en la ruta especificada.');
            }

            $resultadoOCR = PDFProcessor::extractStructuredData($job['filepath']);
            $recordTiming('ocr_ms');
            $text = $resultadoOCR['text'] ?? '';
            $fields = $resultadoOCR['fields'] ?? [];

            // Prepare persistable fields for relocation
            $rutExtraido = $fields['rut'] ?? null;
            $numeroCertificado = $fields['numero_certificado'] ?? null;
            $fechaEntregaIso = $this->convertToIsoDate($fields['fecha_entrega'] ?? null);
            $persistableFields = $fields;
            $persistableFields['fecha_entrega'] = $fechaEntregaIso;
            $persistableFields['expediente_drive_id'] = $driveInfo['drive_id'];
            $persistableFields['expediente_drive_link'] = $driveInfo['drive_link'];

            // CRITICAL: Relocate file BEFORE validation to ensure folder organization even if processing fails
            $this->relocateExpedienteAssets($job, $persistableFields, $driveInfo);
            $resultPayload['filename'] = $job['filename'];
            $resultPayload['filepath'] = $job['filepath'];

            $resultPayload['ocr'] = [
                'source' => $resultadoOCR['source'] ?? null,
                'command' => $resultadoOCR['command'] ?? null,
                'command_output' => $resultadoOCR['command_output'] ?? null,
                'texto_muestra' => mb_substr($text, 0, 500),
                'datos_crudos' => $fields,
            ];

            if (empty($fields) || (empty($rutExtraido) && empty($fields['nombre']))) {
                throw new RuntimeException('OCR incompleto: no se extrajeron campos mínimos.');
            }

            if (empty($job['id_expediente'])) {
                if ($rutExtraido) {
                    $duplicadoRut = $this->egresado->BuscarPorRutNormalizado($rutExtraido);
                    if ($duplicadoRut) {
                        throw new RuntimeException('Expediente ya se encuentra ingresado (RUT ' . ($duplicadoRut['identificacion'] ?? 'desconocido') . ').');
                    }
                }

                if (!empty($numeroCertificado)) {
                    $duplicadoCert = $this->egresado->BuscarPorNumeroCertificado($numeroCertificado);
                    if ($duplicadoCert) {
                        throw new RuntimeException('Estos datos ya han sido ingresados (número de certificado ' . $numeroCertificado . ').');
                    }
                }

                $nuevoId = $this->egresado->CrearDesdeExpediente($persistableFields, $job['filename']);
                if (!$nuevoId) {
                    throw new RuntimeException('No se pudo crear el egresado a partir del expediente.');
                }
                $job['id_expediente'] = $nuevoId;
                $resultPayload['id_expediente'] = $nuevoId;
            } else {
                $this->egresado->CambiarExpediente($job['id_expediente'], $job['filename'], $driveInfo);
            }

            $this->egresado->ActualizarDatosCertificado($job['id_expediente'], [
                'nombre' => $fields['nombre'] ?? null,
                'rut' => $rutExtraido,
                'anio_egreso' => $fields['anio_egreso'] ?? null,
                'titulo' => DriveFolderMapper::resolveByTitle($fields['titulo'] ?? null)['titulo_nombre'] ?? null,
                'numero_certificado' => $fields['numero_certificado'] ?? null,
                'fecha_entrega' => $fechaEntregaIso,
            ]);

            $this->egresado->ActualizarTituloEgresadoDatos($job['id_expediente'], [
                'numero_documento' => $numeroCertificado,
                'fecha_grado' => $fechaEntregaIso,
                'titulo_nombre' => DriveFolderMapper::resolveByTitle($fields['titulo'] ?? null)['titulo_nombre'] ?? null,
            ]);
            $recordTiming('db_updates_ms');

            $this->writeDebugFiles($job['filepath'], $text, $resultadoOCR);

            $resultPayload['fields'] = $fields;
            $timings['total_ms'] = round((microtime(true) - $overallStart) * 1000, 2);
            $resultPayload['timings'] = $timings;
            $this->queue->markCompleted($jobId, $resultPayload);
            error_log(sprintf('ExpedienteProcessor job #%d timings: %s', $jobId, json_encode($timings)));

            return [
                'success' => true,
                'mensaje' => 'Expediente procesado correctamente',
                'job_id' => $jobId,
                'id_expediente' => $job['id_expediente'],
                'fields' => $fields,
                'payload' => $resultPayload,
                'archivo' => $job['filename'],
                'drive_info' => $driveInfo,
                'timings' => $timings,
            ];
        } catch (Throwable $e) {
            $timings['total_ms'] = round((microtime(true) - $overallStart) * 1000, 2);
            $timings['error'] = $e->getMessage();
            $resultPayload['timings'] = $timings;
            $this->queue->markFailed($jobId, $e->getMessage(), $resultPayload);
            error_log(sprintf('ExpedienteProcessor job #%d failed after %sms: %s', $jobId, $timings['total_ms'], $e->getMessage()));
            return [
                'success' => false,
                'mensaje' => $e->getMessage(),
                'job_id' => $jobId,
                'id_expediente' => $job['id_expediente'],
                'fields' => $fields,
                'payload' => $resultPayload,
                'timings' => $timings,
            ];
        }
    }

    private function writeDebugFiles(string $filePath, string $text, array $resultadoOCR): void
    {
        $debugDir = dirname($filePath);
        file_put_contents(
            $debugDir . '/debug_texto.txt',
            "TEXTO EXTRAÍDO:\n" . $text . "\n\nMETADATA:\n" . json_encode([
                'command' => $resultadoOCR['command'] ?? null,
                'command_output' => $resultadoOCR['command_output'] ?? null,
                'source' => $resultadoOCR['source'] ?? null,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        file_put_contents(
            $debugDir . '/debug_datos.txt',
            "DATOS EXTRAÍDOS:\n" . json_encode($resultadoOCR['fields'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    private function convertToIsoDate(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $normalized = str_replace(['/', '.'], '-', trim($value));

        // Ya viene en formato ISO completo - pero DEBE ser validado también
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $normalized, $matches)) {
            $year = (int) $matches[1];
            $month = (int) $matches[2];
            $day = (int) $matches[3];

            // Validar que sea una fecha real
            if (!checkdate($month, $day, $year)) {
                return null;
            }

            return $normalized;
        }

        if (!preg_match('/^(\d{1,2})-(\d{1,2})-(\d{2,4})$/', $normalized, $matches)) {
            return null;
        }

        $day = (int) $matches[1];
        $month = (int) $matches[2];
        $year = $matches[3];

        if (strlen($year) === 2) {
            $year = (intval($year) >= 50 ? '19' : '20') . $year;
        }

        $year = (int) $year;

        if (!checkdate($month, $day, $year)) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }

    private function relocateExpedienteAssets(array &$job, array $fields, array &$driveInfo): void
    {
        $title = $fields['titulo'] ?? $fields['titulo_obtenido'] ?? $fields['especialidad'] ?? null;
        $mapping = DriveFolderMapper::resolveByTitle($title);

        $baseDir = $this->localBaseDir;
        $relativeFolder = $mapping['local_folder'] ?? '';
        $currentRelative = str_replace('\\', '/', $job['filename']);
        $currentBasename = basename($currentRelative);


        if ($relativeFolder !== '') {
            $relativeFolder = DriveFolderMapper::ensureLocalDirectory($baseDir, $relativeFolder);
            $targetRelative = $relativeFolder === '' ? $currentBasename : $relativeFolder . '/' . $currentBasename;
            $targetFullPath = $baseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $targetRelative);

            error_log("ExpedienteProcessor: Relocation check - Source: {$job['filepath']} | Target: {$targetFullPath}");

            if ($targetFullPath !== $job['filepath']) {
                $targetDir = dirname($targetFullPath);
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }

                error_log("ExpedienteProcessor: Attempting to move file from {$job['filepath']} to {$targetFullPath}");

                // Try to rename (move) the file
                $moved = @rename($job['filepath'], $targetFullPath);

                // If rename failed (e.g., cross-partition), use copy+delete fallback
                if (!$moved && is_file($job['filepath'])) {
                    error_log("ExpedienteProcessor: rename() failed, trying copy+unlink");
                    if (@copy($job['filepath'], $targetFullPath)) {
                        if (@unlink($job['filepath'])) {
                            error_log("ExpedienteProcessor: Successfully copied and deleted source");
                            $moved = true;
                        } else {
                            error_log("ExpedienteProcessor: WARNING - Copied but failed to delete source: {$job['filepath']}");
                            $moved = true;
                        }
                    } else {
                        error_log("ExpedienteProcessor: ERROR - Failed to copy file");
                    }
                } else {
                    error_log("ExpedienteProcessor: rename() succeeded");
                }

                if ($moved) {
                    $job['filepath'] = $targetFullPath;
                    $job['filename'] = $targetRelative;
                    error_log("ExpedienteProcessor: File relocated successfully to {$targetFullPath}");
                } else {
                    error_log("ExpedienteProcessor: ERROR - File NOT moved, remains at {$job['filepath']}");
                }
            } else {
                error_log("ExpedienteProcessor: SKIP - File already at target location: {$targetFullPath}");
            }
        } else {
            error_log("ExpedienteProcessor: SKIP - No relocation needed (no subfolder for this career)");
        }

        if (!empty($mapping['drive_folder_id']) && !empty($driveInfo['drive_id'])) {
            $client = $this->getDriveClient();
            if ($client) {
                $client->moveFileToFolder($driveInfo['drive_id'], $mapping['drive_folder_id']);
            }
        }
    }

    private function getDriveClient(): ?GoogleDriveClient
    {
        if ($this->driveClient === null) {
            try {
                $client = new GoogleDriveClient();
                if ($client->isEnabled()) {
                    $this->driveClient = $client;
                }
            } catch (Throwable $e) {
                error_log('ExpedienteProcessor drive client error: ' . $e->getMessage());
                $this->driveClient = null;
            }
        }

        return $this->driveClient;
    }
}
