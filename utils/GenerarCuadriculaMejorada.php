<?php
require_once __DIR__ . '/../vendor/autoload.php';

use setasign\Fpdi\Tcpdf\Fpdi;

// Crear PDF con cuadrícula de coordenadas mejorada
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

// Dibujar cuadrícula cada 5mm (más precisa)
$pdf->SetDrawColor(150, 150, 150); // Gris
$pdf->SetLineWidth(0.1);

// Líneas verticales cada 5mm
for ($x = 0; $x <= 210; $x += 5) {
    if ($x % 10 == 0) {
        $pdf->SetDrawColor(100, 100, 100); // Más oscuro cada 10mm
        $pdf->SetLineWidth(0.2);
    } else {
        $pdf->SetDrawColor(180, 180, 180); // Más claro
        $pdf->SetLineWidth(0.1);
    }
    $pdf->Line($x, 0, $x, 297);
}

// Líneas horizontales cada 5mm
for ($y = 0; $y <= 297; $y += 5) {
    if ($y % 10 == 0) {
        $pdf->SetDrawColor(100, 100, 100); // Más oscuro cada 10mm
        $pdf->SetLineWidth(0.2);
    } else {
        $pdf->SetDrawColor(180, 180, 180); // Más claro
        $pdf->SetLineWidth(0.1);
    }
    $pdf->Line(0, $y, 210, $y);
}

// Números más grandes y visibles
$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetTextColor(255, 0, 0); // Rojo brillante

// Números verticales (X) cada 10mm
for ($x = 0; $x <= 210; $x += 10) {
    $pdf->SetXY($x - 4, 1);
    $pdf->Cell(8, 4, (string)$x, 0, 0, 'C', false);
}

// Números horizontales (Y) cada 10mm
for ($y = 10; $y <= 297; $y += 10) {
    $pdf->SetXY(1, $y - 2);
    $pdf->Cell(8, 4, (string)$y, 0, 0, 'C', false);
}

// Guardar
$outputPath = __DIR__ . '/../certificados/CUADRICULA_MEJORADA.pdf';
$pdf->Output($outputPath, 'F');

echo "✅ Cuadrícula mejorada generada en: $outputPath\n";
echo "\nMejoras:\n";
echo "- Cuadrícula cada 5mm (más precisa)\n";
echo "- Líneas más oscuras cada 10mm\n";
echo "- Números más grandes y en rojo brillante\n";
echo "- Mejor visibilidad\n";
