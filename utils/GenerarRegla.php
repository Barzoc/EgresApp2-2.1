<?php
// Script para generar una regla visual sobre el certificado
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../lib/PdfTemplateFiller.php';

use setasign\Fpdi\Tcpdf\Fpdi;

$templatePath = __DIR__ . '/../certificados/LLENADO CERTIFICADO TÍTULO CAMPOS VACÍOS.pdf';
$outputPath = __DIR__ . '/../certificados/regla_coordenadas.pdf';

$pdf = new Fpdi();
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false);

$pdf->AddPage();
if (file_exists($templatePath)) {
    $pdf->setSourceFile($templatePath);
    $tplId = $pdf->importPage(1);
    $pdf->useTemplate($tplId, 0, 0, 210, 297, true);
}

$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetTextColor(255, 0, 0); // Rojo

// Dibujar líneas horizontales con su coordenada Y
// Rango de interés: 100mm a 200mm (donde están los datos)
for ($y = 100; $y <= 210; $y += 2) {
    $pdf->SetXY(10, $y);
    // Dibujar línea fina
    $pdf->SetDrawColor(255, 0, 0);
    $pdf->Line(10, $y, 200, $y);
    
    // Escribir coordenada
    $pdf->Cell(0, 0, "Y=$y", 0, 0, 'L');
    $pdf->SetXY(190, $y);
    $pdf->Cell(0, 0, "Y=$y", 0, 0, 'R');
}

$pdf->Output($outputPath, 'F');
echo "📏 PDF Regla generado en: $outputPath\n";
