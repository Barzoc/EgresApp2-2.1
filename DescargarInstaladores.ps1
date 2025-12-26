# ============================================================================
# DESCARGADOR DE INSTALADORES OFFLINE - EGRESAPP2
# ============================================================================

$InstallersDir = "$PSScriptRoot\installers"

if (-not (Test-Path $InstallersDir)) {
    New-Item -ItemType Directory -Path $InstallersDir -Force | Out-Null
    Write-Host "Carpeta 'installers' creada."
}

[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12

$Downloads = @(
    @{ Name = "Tesseract OCR"; Url = "https://digi.bib.uni-mannheim.de/tesseract/tesseract-ocr-w64-setup-5.3.3.20231005.exe"; Output = "tesseract-installer.exe" },
    @{ Name = "ImageMagick"; Url = "https://imagemagick.org/archive/binaries/ImageMagick-7.1.1-41-Q16-HDRI-x64-dll.exe"; Output = "imagemagick-installer.exe" },
    @{ Name = "LibreOffice"; Url = "https://download.documentfoundation.org/libreoffice/stable/24.8.3/win/x86_64/LibreOffice_24.8.3_Win_x86-64.msi"; Output = "libreoffice-installer.msi" },
    @{ Name = "Composer"; Url = "https://getcomposer.org/Composer-Setup.exe"; Output = "composer-setup.exe" }
)

Write-Host "Iniciando descargas..."

foreach ($item in $Downloads) {
    $outputPath = Join-Path $InstallersDir $item.Output
    
    if (Test-Path $outputPath) {
        Write-Host "Ya existe: $($item.Name)"
        continue
    }

    Write-Host "Descargando: $($item.Name)"
    
    $userAgent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
    
    $curlCommand = "curl.exe"
    if (Get-Command $curlCommand -ErrorAction SilentlyContinue) {
        # Agregar User-Agent a curl
        $args = @("-L", "-A", $userAgent, "-o", $outputPath, $item.Url)
        $p = Start-Process -FilePath $curlCommand -ArgumentList $args -Wait -NoNewWindow -PassThru
        if ($p.ExitCode -eq 0) {
            Write-Host "OK (curl)"
        } else {
            Write-Host "Error (curl): $($p.ExitCode)"
        }
    } else {
        # Agregar User-Agent a Invoke-WebRequest
        Invoke-WebRequest -Uri $item.Url -OutFile $outputPath -UseBasicParsing -UserAgent $userAgent -ErrorAction SilentlyContinue
        if ($?) {
            Write-Host "OK (PS)"
        } else {
            Write-Host "Error (PS)"
        }
    }
}

Write-Host "Fin."
