<?php
require_once __DIR__ . '/modelo/Conexion.php';

try {
    $db = new Conexion();
    $pdo = $db->pdo;

    $stmt = $pdo->query("SELECT identificacion, nombrecompleto, expediente_pdf FROM egresado");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($records as $row) {
        $original = $row['expediente_pdf'];
        if (empty($original))
            continue;

        // Clean the filename
        // 1. Remove any non-printable characters (including newlines)
        $clean = preg_replace('/[\x00-\x1F\x7F]/', '', $original);

        // 2. Remove any characters that are not letters, numbers, underscores, dots, or dashes
        $clean = preg_replace('/[^a-zA-Z0-9_.-]/', '', $clean);

        // 3. Ensure it ends with .pdf (case insensitive)
        if (!preg_match('/\.pdf$/i', $clean)) {
            // If it doesn't end in .pdf, maybe it has garbage at the end?
            // Try to find .pdf and take everything up to it
            if (preg_match('/(.*\.pdf)/i', $clean, $matches)) {
                $clean = $matches[1];
            } else {
                $clean .= '.pdf';
            }
        }

        if ($original !== $clean) {
            echo "ID: " . $row['identificacion'] . "\n";
            echo "Original: " . bin2hex($original) . "\n";
            echo "Cleaned:  " . $clean . "\n";

            $update = $pdo->prepare("UPDATE egresado SET expediente_pdf = :pdf WHERE identificacion = :id");
            $update->execute([':pdf' => $clean, ':id' => $row['identificacion']]);
            echo "Updated.\n----------------\n";
        }
    }

    echo "Done cleaning records.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
