<?php
$pdfPath = 'C:/Users/xerox/Desktop/EXPEDIENTES/LISTO PARA SUBIR/EDUARDO ANDRÉS GUERRERO TORRES.pdf';
$binary = 'C:/Program Files/poppler/Library/bin/pdftotext.exe';

function wrapShellArg($arg)
{
    if (DIRECTORY_SEPARATOR === '\\') {
        $escaped = str_replace('"', '\\"', $arg);
        return '"' . $escaped . '"';
    }
    return escapeshellarg($arg);
}

echo "TEST DIRECTO DE PDFTOTEXT\n\n";

// Test 1: pdftotext directo sin opciones
$cmd1 = sprintf('%s %s - 2>&1', wrapShellArg($binary), wrapShellArg($pdfPath));
echo "[1] Comando sin opciones:\n$cmd1\n\n";
$output1 = shell_exec($cmd1);
echo "Output length: " . strlen($output1 ?? '') . "\n";
echo "First 500 chars:\n" . substr($output1 ?? '(null)', 0, 500) . "\n\n";

// Test 2: pdftotext solo primera página
$cmd2 = sprintf('%s -f 1 -l 1 %s - 2>&1', wrapShellArg($binary), wrapShellArg($pdfPath));
echo "[2] Comando con primera página:\n$cmd2\n\n";
$output2 = shell_exec($cmd2);
echo "Output length: " . strlen($output2 ?? '') . "\n";
echo "First 500 chars:\n" . substr($output2 ?? '(null)', 0, 500) . "\n\n";

// Test 3: usando exec
$cmd3 = sprintf('%s -f 1 -l 1 %s -', wrapShellArg($binary), wrapShellArg($pdfPath));
echo "[3] Usando exec en lugar de shell_exec:\n$cmd3\n\n";
exec($cmd3, $output3Lines, $returnCode);
echo "Return code: $returnCode\n";
echo "Lines: " . count($output3Lines) . "\n";
echo "Output: " . implode("\n", array_slice($output3Lines, 0, 20)) . "\n";
