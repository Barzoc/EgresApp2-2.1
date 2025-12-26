<?php
// lib/PowerShellTemplateProcessor.php

class PowerShellTemplateProcessor
{
    private $tempDir;
    private $documentXmlPath;
    private $templatePath;
    private $workingDir;
    private $xmlContent;

    public function __construct($path)
    {
        if (!file_exists($path)) {
            throw new Exception("Template not found: $path");
        }
        $this->templatePath = $path;
        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cert_gen_' . uniqid();
        $this->extract();
    }

    private function extract()
    {
        // Crear directorio temporal
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0777, true);
        }

        $source = escapeshellarg($this->templatePath);
        $dest = escapeshellarg($this->tempDir);

        // Usar PowerShell para descomprimir
        // -Force para sobrescribir
        $cmd = "powershell -NoProfile -Command \"Expand-Archive -Path $source -DestinationPath $dest -Force\"";
        exec($cmd, $output, $return);

        if ($return !== 0) {
            throw new Exception("Error extracting .docx with PowerShell. Code: $return");
        }

        $this->documentXmlPath = $this->tempDir . '/word/document.xml';
        if (!file_exists($this->documentXmlPath)) {
            throw new Exception("Invalid .docx structure. Missing word/document.xml");
        }

        $this->xmlContent = file_get_contents($this->documentXmlPath);
    }

    public function setValue($search, $replace)
    {
        // PhpWord usa formato ${search}
        // Simplemente reemplazamos ${search} por el valor
        // Nota: PhpWord maneja escapes XML, aquí lo haremos básico pero funcional
        $searchPattern = '${' . $search . '}';
        $replaceSafe = htmlspecialchars($replace, ENT_XML1, 'UTF-8');
        
        $this->xmlContent = str_replace($searchPattern, $replaceSafe, $this->xmlContent);
    }

    public function saveAs($outputPath)
    {
        // Guardar cambios en document.xml
        file_put_contents($this->documentXmlPath, $this->xmlContent);

        $dest = escapeshellarg($outputPath);
        // PowerShell Compress-Archive requiere la ruta de los items, no la carpeta padre si queremos evitar que cree carpeta dentro de zip
        // Truco: entrar al directorio y comprimir *
        
        $prevDir = getcwd();
        chdir($this->tempDir);
        
        // Importante: Eliminar el archivo destino si existe antes de comprimir, powershell puede fallar o agregar
        if (file_exists($outputPath)) {
            unlink($outputPath);
        }

        // Comprimir todo el contenido del tempDir al destino
        // Usamos ruta absoluta para destino
        $absDest = $outputPath; 
        
        // PowerShell command to zip all files in current dir
        // Get-ChildItem * | Compress-Archive -DestinationPath '...'
        $cmd = "powershell -NoProfile -Command \"Get-ChildItem -Path . -Recurse | Compress-Archive -DestinationPath '$absDest'\"";
        
        exec($cmd, $output, $return);
        
        chdir($prevDir);

        // Limpiar
        $this->cleanup();

        if ($return !== 0 || !file_exists($outputPath)) {
            throw new Exception("Error creating .docx with PowerShell. Code: $return");
        }
    }

    private function cleanup()
    {
        // Borrar directorio temporal recursivamente
        $this->delTree($this->tempDir);
    }

    private function delTree($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }
}
