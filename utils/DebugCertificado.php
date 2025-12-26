<?php
// Script de depuración para verificar alineación
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../lib/PdfTemplateFiller.php';

use setasign\Fpdi\Tcpdf\Fpdi;

$templatePath = __DIR__ . '/../certificados/LLENADO CERTIFICADO TÍTULO CAMPOS VACÍOS.pdf';
$outputPath = __DIR__ . '/../certificados/debug_alignment.pdf';

$pdf = new Fpdi();
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false);

// Página 1: Solo Plantilla
$pdf->AddPage();
if (file_exists($templatePath)) {
    $pdf->setSourceFile($templatePath);
    $tplId = $pdf->importPage(1);
    $pdf->useTemplate($tplId, 0, 0, 210, 297, true);
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->SetTextColor(255, 0, 0);
    $pdf->SetXY(10, 10);
    $pdf->Cell(0, 0, 'Página 1: Solo Plantilla (Verificar márgenes)', 0, 1);
} else {
    $pdf->Cell(0, 0, 'Error: Plantilla no encontrada', 0, 1);
}

// Página 2: Solo Texto y Cuadrícula (Sin Plantilla)
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(0, 0, 0);

// Dibujar cuadrícula
$pdf->SetDrawColor(200, 200, 200);
for ($i=0; $i<=210; $i+=10) $pdf->Line($i, 0, $i, 297);
for ($i=0; $i<=297; $i+=10) $pdf->Line(0, $i, 210, $i);

// Datos de prueba con las coordenadas que creemos correctas (Offset +15)
$datos = [
    ['text' => 'FECHA TITULO (Y=127)', 'x' => 118, 'y' => 127],
    ['text' => 'NOMBRE (Y=136)', 'x' => 70, 'y' => 136],
    ['text' => 'RUT (Y=145)', 'x' => 35, 'y' => 145],
    ['text' => 'TITULO (Y=145)', 'x' => 85, 'y' => 145],
    ['text' => 'REGISTRO (Y=172)', 'x' => 118, 'y' => 172],
    ['text' => 'EMISION (Y=198)', 'x' => 118, 'y' => 198],
];

foreach ($datos as $d) {
    $pdf->SetXY($d['x'], $d['y']);
    $pdf->Cell(0, 0, $d['text']);
    // Dibujar punto rojo en la coordenada exacta
    $pdf->SetFillColor(255, 0, 0);
    $pdf->Rect($d['x']-1, $d['y']-1, 2, 2, 'F');
}

$pdf->Output($outputPath, 'F');
echo "Debug PDF generado en: $outputPath\n";
