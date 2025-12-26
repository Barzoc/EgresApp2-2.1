<?php

use setasign\Fpdi\Tcpdf\Fpdi;

class PdfTemplateFiller extends Fpdi
{
    public function __construct()
    {
        parent::__construct();
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);
        $this->SetMargins(0, 0, 0);
        $this->SetAutoPageBreak(false);
    }

    /**
     * Rellena un PDF plantilla con datos
     * 
     * @param string $templatePath Ruta al PDF base
     * @param string $outputPath Ruta donde guardar el PDF generado
     * @param array $data Datos a escribir (texto, x, y, font, size, align)
     * @return bool
     */
    public function fillTemplate(string $templatePath, string $outputPath, array $data): bool
    {
        try {
            // Establecer fuente por defecto
            $this->SetFont('helvetica', '', 12);
            
            // Agregar página
            $this->AddPage();
            
            // Importar página del PDF plantilla
            $this->setSourceFile($templatePath);
            $tplId = $this->importPage(1);
            
            // Usar la plantilla importada
            $this->useTemplate($tplId, 0, 0, 210, 297, true);
            
            // Escribir datos
            foreach ($data as $item) {
                $text = $item['text'] ?? '';
                $x = $item['x'] ?? 0;
                $y = $item['y'] ?? 0;
                $w = $item['w'] ?? 0; // Ancho de celda (0 = hasta el margen derecho)
                $h = $item['h'] ?? 0;
                $font = $item['font'] ?? 'helvetica';
                $style = $item['style'] ?? ''; // B = Bold, I = Italic
                $size = $item['size'] ?? 12;
                $align = $item['align'] ?? 'L'; // L, C, R
                
                $this->SetFont($font, $style, $size);
                $this->SetXY($x, $y);
                $this->Cell($w, $h, $text, 0, 0, $align);
            }
            
            // Guardar archivo
            $this->Output($outputPath, 'F');
            
            return true;
        } catch (Throwable $e) {
            error_log("Error al rellenar plantilla PDF: " . $e->getMessage());
            return false;
        }
    }
}
