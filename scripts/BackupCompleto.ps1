#Requires -RunAsAdministrator

# ============================================================================
# BACKUP COMPLETO - EGRESAPP2
# ============================================================================
# Crea un backup completo del sistema: BD, código y archivos
# ============================================================================

param(
    [string]$ProjectRoot = "C:\laragon\www\EGRESAPP2",
    [string]$BackupRoot = "C:\EGRESAPP2_Backups",
    [string]$DBName = "gestion_egresados"
)

$Timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$BackupDir = "$BackupRoot\$Timestamp"

Write-Host ""
Write-Host "╔═══════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║          BACKUP COMPLETO - EGRESAPP2                  ║" -ForegroundColor Yellow
Write-Host "╚═══════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""
Write-Host "Timestamp: $Timestamp" -ForegroundColor White
Write-Host "Destino:   $BackupDir" -ForegroundColor White
Write-Host ""

# Crear directorio de backup
New-Item -ItemType Directory -Path $BackupDir -Force | Out-Null

# ============================================================================
# 1. BACKUP DE BASE DE DATOS
# ============================================================================
Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "1. BACKUP DE BASE DE DATOS" -ForegroundColor Yellow
Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

# Buscar mysqldump
$mysqlBinPaths = @(
    "C:\laragon\bin\mysql\mysql-8.0.30\bin",
    "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin",
    "C:\xampp\mysql\bin"
)

$mysqldump = $null
foreach ($binPath in $mysqlBinPaths) {
    $testPath = "$binPath\mysqldump.exe"
    if (Test-Path $testPath) {
        $mysqldump = $testPath
        break
    }
}

if (-not $mysqldump) {
    Write-Host "✗ mysqldump no encontrado" -ForegroundColor Red
    exit 1
}

Write-Host "Exportando base de datos '$DBName'..." -ForegroundColor Cyan

try {
    $sqlFile = "$BackupDir\$DBName`_backup.sql"
    
    & $mysqldump `
        --user=root `
        --single-transaction `
        --quick `
        --lock-tables=false `
        --routines `
        --triggers `
        --events `
        --set-charset `
        --default-character-set=utf8mb4 `
        $DBName | Out-File -FilePath $sqlFile -Encoding UTF8
    
    if ($LASTEXITCODE -eq 0) {
        $sqlSize = (Get-Item $sqlFile).Length / 1MB
        Write-Host "✓ Backup SQL creado: $([math]::Round($sqlSize, 2)) MB" -ForegroundColor Green
        
        # Calcular checksum
        $hash = Get-FileHash -Path $sqlFile -Algorithm SHA256
        $hash.Hash | Out-File "$BackupDir\backup_checksum.txt"
        Write-Host "✓ Checksum guardado: $($hash.Hash.Substring(0,16))..." -ForegroundColor Green
        
        # Comprimir SQL
        Write-Host "Comprimiendo backup SQL..." -ForegroundColor Cyan
        Compress-Archive -Path $sqlFile -DestinationPath "$BackupDir\$DBName`_backup.zip" -CompressionLevel Optimal
        
        $zipSize = (Get-Item "$BackupDir\$DBName`_backup.zip").Length / 1MB
        Write-Host "✓ SQL comprimido: $([math]::Round($zipSize, 2)) MB" -ForegroundColor Green
        
        # Eliminar SQL sin comprimir para ahorrar espacio
        Remove-Item $sqlFile -Force
    } else {
        Write-Host "✗ Error al crear backup de BD" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Error: $_" -ForegroundColor Red
}

Write-Host ""

# ============================================================================
# 2. BACKUP DE CÓDIGO
# ============================================================================
Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "2. BACKUP DE CÓDIGO" -ForegroundColor Yellow
Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

$codigoDir = "$BackupDir\codigo"
New-Item -ItemType Directory -Path $codigoDir -Force | Out-Null

Write-Host "Copiando código fuente..." -ForegroundColor Cyan

# Copiar con exclusiones
$excludeDirs = @("vendor", "temp", "tmp", "node_modules")
$excludeFiles = @("*.log", "*.cache", "instalacion_*.txt")

try {
    # Copiar archivos principales
    Get-ChildItem -Path $ProjectRoot -File | 
        Where-Object { $_.Name -notmatch "\.log$|\.cache$|^instalacion_" } |
        Copy-Item -Destination $codigoDir -Force
    
    # Copiar directorios (excluyendo temporales)
    Get-ChildItem -Path $ProjectRoot -Directory |
        Where-Object { $_.Name -notin $excludeDirs } |
        ForEach-Object {
            $destPath = "$codigoDir\$($_.Name)"
            Copy-Item -Path $_.FullName -Destination $destPath -Recurse -Force
        }
    
    Write-Host "✓ Código copiado" -ForegroundColor Green
    
    # Comprimir código
    Write-Host "Comprimiendo código..." -ForegroundColor Cyan
    Compress-Archive -Path $codigoDir -DestinationPath "$BackupDir\EGRESAPP2_codigo.zip"
    
    $codigoZipSize = (Get-Item "$BackupDir\EGRESAPP2_codigo.zip").Length / 1MB
    Write-Host "✓ Código comprimido: $([math]::Round($codigoZipSize, 2)) MB" -ForegroundColor Green
    
    # Eliminar carpeta temporal
    Remove-Item -Path $codigoDir -Recurse -Force
} catch {
    Write-Host "✗ Error al copiar código: $_" -ForegroundColor Red
}

Write-Host ""

# ============================================================================
# 3. BACKUP DE ARCHIVOS SUBIDOS
# ============================================================================
Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "3. BACKUP DE ARCHIVOS SUBIDOS" -ForegroundColor Yellow
Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

$uploadsSource = "$ProjectRoot\assets\expedientes\expedientes_subidos"
$uploadsBackup = "$BackupDir\expedientes_uploads"

if (Test-Path $uploadsSource) {
    # Verificar tamaño
    $uploadsSize = (Get-ChildItem -Path $uploadsSource -Recurse -File -ErrorAction SilentlyContinue | 
        Measure-Object -Property Length -Sum).Sum / 1GB
    
    Write-Host "Tamaño de uploads: $([math]::Round($uploadsSize, 2)) GB" -ForegroundColor Cyan
    
    # Copiar archivos
    Write-Host "Copiando archivos subidos..." -ForegroundColor Cyan
    robocopy $uploadsSource $uploadsBackup /E /R:3 /W:5 /NFL /NDL /NJH /NJS | Out-Null
    
    if ($LASTEXITCODE -le 7) {
        Write-Host "✓ Archivos copiados" -ForegroundColor Green
        
        # Generar manifiesto
        Get-ChildItem -Path $uploadsBackup -Recurse -File | 
            Select-Object @{N='Archivo';E={$_.FullName.Replace($uploadsBackup, '')}}, 
                         @{N='Tamaño_KB';E={[math]::Round($_.Length/1KB, 2)}}, 
                         LastWriteTime |
            Export-Csv "$BackupDir\uploads_manifest.csv" -NoTypeInformation
        
        Write-Host "✓ Manifiesto creado" -ForegroundColor Green
        
        # Comprimir solo si es pequeño
        if ($uploadsSize -lt 2) {
            Write-Host "Comprimiendo uploads..." -ForegroundColor Cyan
            Compress-Archive -Path $uploadsBackup -DestinationPath "$BackupDir\uploads.zip"
            Write-Host "✓ Uploads comprimidos" -ForegroundColor Green
            Remove-Item -Path $uploadsBackup -Recurse -Force
        } else {
            Write-Host "⚠ Uploads muy grandes, se mantienen sin comprimir" -ForegroundColor Yellow
        }
    } else {
        Write-Host "✗ Error al copiar uploads (código: $LASTEXITCODE)" -ForegroundColor Red
    }
} else {
    Write-Host "ℹ No hay archivos subidos para respaldar" -ForegroundColor Cyan
}

Write-Host ""

# ============================================================================
# 4. GENERAR REPORTE
# ============================================================================
Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "4. GENERANDO REPORTE" -ForegroundColor Yellow
Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

$report = @"
╔═══════════════════════════════════════════════════════╗
║          REPORTE DE BACKUP - EGRESAPP2                ║
╚═══════════════════════════════════════════════════════╝

FECHA: $Timestamp
UBICACIÓN: $BackupDir

CONTENIDO DEL BACKUP:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

"@

# Listar archivos del backup
Get-ChildItem -Path $BackupDir -File | ForEach-Object {
    $size = [math]::Round($_.Length / 1MB, 2)
    $report += "`n  • $($_.Name) - $size MB"
}

$report += @"


INSTRUCCIONES DE RESTAURACIÓN:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

1. BASE DE DATOS:
   Descomprimir: $DBName`_backup.zip
   Importar: mysql -u root $DBName < $DBName`_backup.sql

2. CÓDIGO:
   Descomprimir EGRESAPP2_codigo.zip en C:\laragon\www\

3. UPLOADS:
   Copiar contenido de uploads a assets/expedientes/expedientes_subidos/

CHECKSUM SHA256:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
$(Get-Content "$BackupDir\backup_checksum.txt")

"@

$report | Out-File "$BackupDir\LEEME_BACKUP.txt" -Encoding UTF8

Write-Host "✓ Reporte generado: LEEME_BACKUP.txt" -ForegroundColor Green

Write-Host ""
Write-Host "╔═══════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║               BACKUP COMPLETADO                       ║" -ForegroundColor Green
Write-Host "╚═══════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

# Calcular tamaño total
$totalSize = (Get-ChildItem -Path $BackupDir -Recurse -File | 
    Measure-Object -Property Length -Sum).Sum / 1GB

Write-Host "Ubicación: $BackupDir" -ForegroundColor White
Write-Host "Tamaño total: $([math]::Round($totalSize, 2)) GB" -ForegroundColor White
Write-Host ""
Write-Host "✅ Backup guardado exitosamente" -ForegroundColor Green
Write-Host ""

# Abrir carpeta de backup
Start-Process explorer.exe $BackupDir
