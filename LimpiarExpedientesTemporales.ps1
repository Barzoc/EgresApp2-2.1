# LimpiarExpedientesTemporales.ps1
# Script para limpiar y organizar archivos en expedientes_subidos

param(
    [switch]$Analizar,
    [switch]$Ejecutar,
    [switch]$Forzar
)

Write-Host "=======================================================" -ForegroundColor Cyan
Write-Host "  LIMPIEZA DE EXPEDIENTES TEMPORALES - EGRESAPP2" -ForegroundColor Cyan
Write-Host "=======================================================" -ForegroundColor Cyan
Write-Host ""

$baseDir = "c:\laragon\www\EGRESAPP2\assets\expedientes"
$tempDir = Join-Path $baseDir "expedientes_subidos"

if (-not (Test-Path $tempDir)) {
    Write-Host "OK: No hay carpeta de expedientes_subidos." -ForegroundColor Green
    exit 0
}

function Compare-Files {
    param($file1, $file2)
    if (-not (Test-Path $file1) -or -not (Test-Path $file2)) {
        return $false
    }
    $hash1 = (Get-FileHash $file1 -Algorithm MD5).Hash
    $hash2 = (Get-FileHash $file2 -Algorithm MD5).Hash
    return $hash1 -eq $hash2
}

$subCarpetas = Get-ChildItem $tempDir -Directory -ErrorAction SilentlyContinue
$totalArchivos = 0
$archivosDuplicados = 0
$archivosPendientes = 0
$archivosAMover = @()
$archivosAEliminar = @()

Write-Host "[1/3] Analizando archivos temporales..." -ForegroundColor Yellow
Write-Host ""

foreach ($carpeta in $subCarpetas) {
    $carpetaNombre = $carpeta.Name
    $carpetaPrincipal = Join-Path $baseDir $carpetaNombre
    
    Write-Host "Carpeta: $carpetaNombre" -ForegroundColor Cyan
    
    if (Test-Path $carpetaPrincipal) {
        $archivosTemp = Get-ChildItem $carpeta.FullName -File -Filter "*.pdf" -ErrorAction SilentlyContinue
        
        foreach ($archivoTemp in $archivosTemp) {
            $totalArchivos++
            $nombreArchivo = $archivoTemp.Name
            $archivoPrincipal = Join-Path $carpetaPrincipal $nombreArchivo
            
            if (Test-Path $archivoPrincipal) {
                $sonIguales = Compare-Files $archivoTemp.FullName $archivoPrincipal
                
                if ($sonIguales) {
                    Write-Host "  [OK] Duplicado: $nombreArchivo" -ForegroundColor Green
                    $archivosDuplicados++
                    $archivosAEliminar += @{
                        Tipo = "Duplicado"
                        Archivo = $archivoTemp.FullName
                        Carpeta = $carpetaNombre
                        Nombre = $nombreArchivo
                    }
                } else {
                    Write-Host "  [!!] Modificado: $nombreArchivo" -ForegroundColor Yellow
                    $archivosPendientes++
                }
            } else {
                Write-Host "  [->] Pendiente: $nombreArchivo" -ForegroundColor Magenta
                $archivosAMover += @{
                    Origen = $archivoTemp.FullName
                    Destino = $archivoPrincipal
                    Carpeta = $carpetaNombre
                    Nombre = $nombreArchivo
                }
                $archivosPendientes++
            }
        }
    } else {
        $archivosTemp = Get-ChildItem $carpeta.FullName -File -Filter "*.pdf" -ErrorAction SilentlyContinue
        Write-Host "  [!!] Carpeta principal no existe. Se creara." -ForegroundColor Yellow
        
        foreach ($archivoTemp in $archivosTemp) {
            $totalArchivos++
            $archivosPendientes++
            $archivosAMover += @{
                Origen = $archivoTemp.FullName
                Destino = (Join-Path $carpetaPrincipal $archivoTemp.Name)
                Carpeta = $carpetaNombre
                Nombre = $archivoTemp.Name
                CrearCarpeta = $true
                CarpetaDestino = $carpetaPrincipal
            }
        }
    }
}

Write-Host ""
Write-Host "=======================================================" -ForegroundColor Cyan
Write-Host "                 RESUMEN DE ANALISIS" -ForegroundColor Cyan
Write-Host "=======================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Total de archivos analizados:     $totalArchivos" -ForegroundColor White
Write-Host "Archivos duplicados (a eliminar): $archivosDuplicados" -ForegroundColor Green
Write-Host "Archivos pendientes (a mover):    $archivosPendientes" -ForegroundColor Magenta
Write-Host ""

if ($totalArchivos -eq 0) {
    Write-Host "OK: No hay archivos temporales para procesar." -ForegroundColor Green
    exit 0
}

if ($Analizar -or (-not $Ejecutar)) {
    Write-Host ""
    Write-Host "=======================================================" -ForegroundColor Cyan
    Write-Host "              DETALLES (Solo Analisis)" -ForegroundColor Cyan
    Write-Host "=======================================================" -ForegroundColor Cyan
    
    if ($archivosAEliminar.Count -gt 0) {
        Write-Host ""
        Write-Host "Archivos duplicados a eliminar:" -ForegroundColor Green
        foreach ($item in $archivosAEliminar) {
            Write-Host "  OK [$($item.Carpeta)] $($item.Nombre)" -ForegroundColor Gray
        }
    }
    
    if ($archivosAMover.Count -gt 0) {
        Write-Host ""
        Write-Host "Archivos pendientes a mover:" -ForegroundColor Magenta
        foreach ($item in $archivosAMover) {
            Write-Host "  -> [$($item.Carpeta)] $($item.Nombre)" -ForegroundColor Gray
        }
    }
    
    Write-Host ""
    Write-Host "Para ejecutar la limpieza:" -ForegroundColor Yellow
    Write-Host "  powershell -ExecutionPolicy Bypass -File LimpiarExpedientesTemporales.ps1 -Ejecutar" -ForegroundColor White
    Write-Host ""
    exit 0
}

Write-Host ""
Write-Host "=======================================================" -ForegroundColor Cyan
Write-Host "             EJECUTANDO LIMPIEZA" -ForegroundColor Cyan
Write-Host "=======================================================" -ForegroundColor Cyan

if (-not $Forzar) {
    Write-Host ""
    Write-Host "Se eliminaran $archivosDuplicados archivos duplicados" -ForegroundColor Yellow
    Write-Host "Se moveran $archivosPendientes archivos pendientes" -ForegroundColor Yellow
    Write-Host ""
    $confirmacion = Read-Host "Continuar? (S/N)"
    
    if ($confirmacion -ne "S" -and $confirmacion -ne "s") {
        Write-Host "Operacion cancelada." -ForegroundColor Red
        exit 1
    }
}

$eliminados = 0
$movidos = 0
$errores = 0

Write-Host ""
Write-Host "[2/3] Moviendo archivos pendientes..." -ForegroundColor Yellow

foreach ($item in $archivosAMover) {
    try {
        if ($item.CrearCarpeta -and $item.CarpetaDestino) {
            if (-not (Test-Path $item.CarpetaDestino)) {
                New-Item -ItemType Directory -Path $item.CarpetaDestino -Force | Out-Null
                Write-Host "  [OK] Carpeta creada: $($item.Carpeta)" -ForegroundColor Green
            }
        }
        
        Move-Item -Path $item.Origen -Destination $item.Destino -Force
        Write-Host "  [->] Movido: [$($item.Carpeta)] $($item.Nombre)" -ForegroundColor Green
        $movidos++
    } catch {
        Write-Host "  [X] Error moviendo [$($item.Carpeta)] $($item.Nombre): $_" -ForegroundColor Red
        $errores++
    }
}

Write-Host ""
Write-Host "[3/3] Eliminando duplicados..." -ForegroundColor Yellow

foreach ($item in $archivosAEliminar) {
    try {
        Remove-Item -Path $item.Archivo -Force
        Write-Host "  [OK] Eliminado: [$($item.Carpeta)] $($item.Nombre)" -ForegroundColor Green
        $eliminados++
    } catch {
        Write-Host "  [X] Error eliminando [$($item.Carpeta)] $($item.Nombre): $_" -ForegroundColor Red
        $errores++
    }
}

Write-Host ""
Write-Host "Limpiando carpetas vacias..." -ForegroundColor Yellow

$carpetasVacias = Get-ChildItem $tempDir -Directory | Where-Object { 
    (Get-ChildItem $_.FullName -Recurse -File).Count -eq 0 
}

foreach ($carpetaVacia in $carpetasVacias) {
    try {
        Remove-Item $carpetaVacia.FullName -Recurse -Force
        Write-Host "  [OK] Carpeta vacia eliminada: $($carpetaVacia.Name)" -ForegroundColor Green
    } catch {
        Write-Host "  [X] Error eliminando carpeta: $($carpetaVacia.Name)" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "=======================================================" -ForegroundColor Cyan
Write-Host "              LIMPIEZA COMPLETADA" -ForegroundColor Cyan
Write-Host "=======================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Archivos movidos:     $movidos" -ForegroundColor Green
Write-Host "Archivos eliminados:  $eliminados" -ForegroundColor Green
Write-Host "Errores:              $errores" -ForegroundColor $(if ($errores -gt 0) { "Red" } else { "Green" })
Write-Host ""

if ($errores -eq 0) {
    Write-Host "OK: Limpieza completada exitosamente" -ForegroundColor Green
} else {
    Write-Host "Advertencia: Limpieza completada con algunos errores" -ForegroundColor Yellow
}

Write-Host ""
