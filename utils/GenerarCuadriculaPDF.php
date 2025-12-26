<?php
require_once __DIR__ . '/../vendor/autoload.php';

use setasign\Fpdi\Tcpdf\Fpdi;

// Crear PDF con cuadrícula de coordenadas
$pdf = new Fpdi();
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();

// Importar el PDF base
$templatePath = __DIR__ . '/../certificados/LLENADO CERTIFICADO TÍTULO CAMPOS VACÍOS.pdf';
if (file_exists($templatePath)) {
    $pdf->setSourceFile($templatePath);
    $tplId = $pdf->importPage(1);
    $pdf->useTemplate($tplId, 0, 0, 210, 297, true);
}

// Dibujar cuadrícula cada 10mm
$pdf->SetDrawColor(200, 200, 200); // Gris claro
$pdf->SetLineWidth(0.1);

// Líneas verticales
for ($x = 0; $x <= 210; $x += 10) {
    $pdf->Line($x, 0, $x, 297);
}

// Líneas horizontales
for ($y = 0; $y <= 297; $y += 10) {
    $pdf->Line(0, $y, 210, $y);
}

// Números en las líneas principales (cada 10mm)
$pdf->SetFont('helvetica', '', 6);
$pdf->SetTextColor(255, 0, 0); // Rojo

// Números verticales (X)
for ($x = 0; $x <= 210; $x += 10) {
    $pdf->SetXY($x - 3, 2);
    $pdf->Cell(6, 3, (string)$x, 0, 0, 'C');
}

// Números horizontales (Y)
for ($y = 10; $y <= 297; $y += 10) {
    $pdf->SetXY(2, $y - 2);
    $pdf->Cell(6, 3, (string)$y, 0, 0, 'C');
}

// Guardar
$outputPath = __DIR__ . '/../certificados/CUADRICULA_COORDENADAS.pdf';
$pdf->Output($outputPath, 'F');

echo "✅ Cuadrícula generada en: $outputPath\n";
echo "\nInstrucciones:\n";
echo "1. Abre el archivo CUADRICULA_COORDENADAS.pdf\n";
echo "2. Verás tu certificado con una cuadrícula superpuesta\n";
echo "3. Los números rojos indican las coordenadas X (horizontal) y Y (vertical)\n";
echo "4. Encuentra dónde debe ir cada campo y anota las coordenadas\n";
echo "5. Las coordenadas están en milímetros (mm)\n";
echo "\nEjemplo: Si un campo está en la intersección de X=120 y Y=110,\n";
echo "las coordenadas son: X=120, Y=110\n";
