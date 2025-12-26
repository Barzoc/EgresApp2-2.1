<?php



/**
 * Clase para convertir HTML a PDF usando TCPDF
 * Optimizada para generar certificados
 */
class HtmlToPdfConverter extends TCPDF
{
    /**
     * Constructor con configuración predeterminada para certificados
     */
    public function __construct()
    {
        parent::__construct('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Configuración del documento
        $this->SetCreator('EgresApp2');
        $this->SetAuthor('Liceo Bicentenario Domingo Santa María');
        $this->SetTitle('Certificado de Título');
        $this->SetSubject('Certificado de Título de Egresado');
        
        // Desactivar encabezado y pie de página
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);
        
        // Configurar márgenes (muy pequeños para que el HTML controle el diseño)
        $this->SetMargins(0, 0, 0);
        $this->SetAutoPageBreak(false, 0);
        
        // Configurar fuentes
        $this->SetFont('times', '', 12);
    }
    
    /**
     * Convierte HTML a PDF
     * 
     * @param string $html Contenido HTML a convertir
     * @param array $options Opciones adicionales (orientation, format, etc.)
     * @return string Contenido del PDF en formato string
     */
    public function convertHtmlToPdf(string $html, array $options = []): string
    {
        // Agregar página
        $orientation = $options['orientation'] ?? 'P';
        $format = $options['format'] ?? 'A4';
        $this->AddPage($orientation, $format);
        
        // Escribir el HTML
        $this->writeHTML($html, true, false, true, false, '');
        
        // Retornar el PDF como string
        return $this->Output('', 'S');
    }
    
    /**
     * Convierte HTML a PDF y lo guarda en un archivo
     * 
     * @param string $html Contenido HTML a convertir
     * @param string $filePath Ruta donde guardar el archivo PDF
     * @param array $options Opciones adicionales
     * @return bool True si se guardó correctamente, false en caso contrario
     */
    public function convertAndSave(string $html, string $filePath, array $options = []): bool
    {
        try {
            $pdfContent = $this->convertHtmlToPdf($html, $options);
            
            // Crear directorio si no existe
            $dir = dirname($filePath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            // Guardar el archivo
            $result = file_put_contents($filePath, $pdfContent);
            
            return $result !== false;
        } catch (Throwable $e) {
            error_log("Error al convertir y guardar PDF: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Procesa una plantilla HTML reemplazando placeholders con datos
     * 
     * @param string $templatePath Ruta al archivo de plantilla HTML
     * @param array $data Datos para reemplazar en la plantilla
     * @return string HTML procesado con los datos
     * @throws RuntimeException Si no se puede leer la plantilla
     */
    public static function processTemplate(string $templatePath, array $data): string
    {
        if (!file_exists($templatePath)) {
            throw new RuntimeException("Plantilla no encontrada: {$templatePath}");
        }
        
        $html = file_get_contents($templatePath);
        
        if ($html === false) {
            throw new RuntimeException("No se pudo leer la plantilla: {$templatePath}");
        }
        
        // Reemplazar placeholders {{key}} con valores de $data
        foreach ($data as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $html = str_replace($placeholder, htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8'), $html);
        }
        
        return $html;
    }
    
    /**
     * Convierte una plantilla HTML a PDF
     * 
     * @param string $templatePath Ruta al archivo de plantilla HTML
     * @param array $data Datos para reemplazar en la plantilla
     * @param array $options Opciones adicionales
     * @return string Contenido del PDF en formato string
     */
    public function convertTemplateToPdf(string $templatePath, array $data, array $options = []): string
    {
        $html = self::processTemplate($templatePath, $data);
        return $this->convertHtmlToPdf($html, $options);
    }
    
    /**
     * Convierte una plantilla HTML a PDF y lo guarda en un archivo
     * 
     * @param string $templatePath Ruta al archivo de plantilla HTML
     * @param array $data Datos para reemplazar en la plantilla
     * @param string $outputPath Ruta donde guardar el archivo PDF
     * @param array $options Opciones adicionales
     * @return bool True si se guardó correctamente, false en caso contrario
     */
    public function convertTemplateAndSave(string $templatePath, array $data, string $outputPath, array $options = []): bool
    {
        try {
            $html = self::processTemplate($templatePath, $data);
            return $this->convertAndSave($html, $outputPath, $options);
        } catch (Throwable $e) {
            error_log("Error al convertir plantilla a PDF: " . $e->getMessage());
            return false;
        }
    }
}
