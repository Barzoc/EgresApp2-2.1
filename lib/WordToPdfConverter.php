<?php

class WordToPdfConverter
{
    private string $libreOfficePath;
    private string $tempDir;
    
    public function __construct()
    {
        // Ruta a LibreOffice
        $this->libreOfficePath = 'C:\\Program Files\\LibreOffice\\program\\soffice.exe';
        
        // Directorio temporal
        $this->tempDir = __DIR__ . '/../temp';
        
        // Crear directorio temp si no existe
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0777, true);
        }
    }
    
    /**
     * Convierte un archivo Word (.docx) a PDF usando LibreOffice
     * 
     * @param string $wordPath Ruta absoluta al archivo .docx
     * @param string $outputDir Directorio donde guardar el PDF
     * @return string|false Ruta al PDF generado o false en caso de error
     */
    public function convertToPdf(string $wordPath, string $outputDir): string|false
    {
        if (!file_exists($wordPath)) {
            error_log("WordToPdfConverter: Archivo Word no encontrado: $wordPath");
            return false;
        }
        
        if (!file_exists($this->libreOfficePath)) {
            error_log("WordToPdfConverter: LibreOffice no encontrado en: {$this->libreOfficePath}");
            return false;
        }
        
        // Asegurar que el directorio de salida existe
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }
        
        // Comando de LibreOffice para conversión headless
        $command = sprintf(
            '"%s" --headless --convert-to pdf --outdir "%s" "%s" 2>&1',
            $this->libreOfficePath,
            $outputDir,
            $wordPath
        );
        
        // Ejecutar conversión
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            error_log("WordToPdfConverter: Error en conversión. Código: $returnCode. Output: " . implode("\n", $output));
            return false;
        }
        
        // Calcular nombre del PDF generado
        $wordFilename = basename($wordPath);
        $pdfFilename = preg_replace('/\\.docx$/i', '.pdf', $wordFilename);
        $pdfPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $pdfFilename;
        
        // Verificar que el PDF se creó
        if (!file_exists($pdfPath)) {
            error_log("WordToPdfConverter: PDF no fue generado en: $pdfPath");
            return false;
        }
        
        return $pdfPath;
    }
    
    /**
     * Limpia archivos temporales
     * 
     * @param array $files Lista de rutas de archivos a eliminar
     */
    public function cleanup(array $files): void
    {
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}
