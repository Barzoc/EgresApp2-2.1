# Script de Diagnostico EGRESAPP2 - Version Mejorada
# Ejecutar con PowerShell

Clear-Host
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "   DIAGNOSTICO DE INSTALACION EGRESAPP2   " -ForegroundColor Yellow
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

$errors = 0
$warnings = 0

# 1. Verificar Archivos
Write-Host "1. Verificando archivos en C:\laragon\www\EGRESAPP2..." -ForegroundColor White
if (Test-Path "C:\laragon\www\EGRESAPP2\index.php") {
    Write-Host "[OK] Archivos encontrados." -ForegroundColor Green
} else {
    Write-Host "[ERROR] No se encuentran los archivos en C:\laragon\www\EGRESAPP2" -ForegroundColor Red
    $errors++
}

# 2. Verificar Laragon
Write-Host "`n2. Verificando Laragon..." -ForegroundColor White
if (Get-Process "laragon" -ErrorAction SilentlyContinue) {
    Write-Host "[OK] Laragon esta en ejecucion." -ForegroundColor Green
} else {
    Write-Host "[WARN] Laragon NO esta en ejecucion." -ForegroundColor Yellow
    $warnings++
}

# 3. Verificar MySQL
Write-Host "`n3. Verificando MySQL..." -ForegroundColor White
if (Get-Process "mysqld" -ErrorAction SilentlyContinue) {
    Write-Host "[OK] MySQL esta en ejecucion." -ForegroundColor Green
    
    # Buscar mysql
    $mysqlPath = $null
    $laragonMysqlDir = "C:\laragon\bin\mysql"
    
    if (Test-Path $laragonMysqlDir) {
        $foundMysql = Get-ChildItem -Path $laragonMysqlDir -Filter "mysql.exe" -Recurse -ErrorAction SilentlyContinue | Sort-Object LastWriteTime -Descending | Select-Object -First 1
        if ($foundMysql) {
            $mysqlPath = $foundMysql.FullName
        }
    }

    if ($mysqlPath) {
        Write-Host "   Usando MySQL: $mysqlPath" -ForegroundColor Gray
        
        # Verificar base de datos
        $dbCheck = & $mysqlPath -u root -e "SHOW DATABASES LIKE 'gestion_egresados';" 2>&1
        if ($dbCheck -match "gestion_egresados") {
            Write-Host "[OK] Base de datos 'gestion_egresados' existe." -ForegroundColor Green
            
            # Verificar tablas
            $tables = & $mysqlPath -u root -D gestion_egresados -e "SHOW TABLES;" 2>&1
            $tableCount = ($tables | Where-Object { $_ -notmatch "Tables_in" -and $_.Trim() -ne "" } | Measure-Object).Count
            
            if ($tableCount -gt 0) {
                Write-Host "[OK] La base de datos contiene $tableCount tablas." -ForegroundColor Green
                
                # Verificar tabla de usuarios
                $userCheck = & $mysqlPath -u root -D gestion_egresados -e "SELECT COUNT(*) as count FROM usuarios WHERE rol='admin';" 2>&1
                if ($userCheck -match "\d+") {
                    Write-Host "[OK] Tabla de usuarios existe con datos." -ForegroundColor Green
                }
            } else {
                Write-Host "[ERROR] La base de datos esta vacia." -ForegroundColor Red
                $errors++
            }
        } else {
            Write-Host "[ERROR] La base de datos 'gestion_egresados' NO existe." -ForegroundColor Red
            $errors++
        }
    } else {
        Write-Host "[WARN] No se pudo encontrar el ejecutable de mysql para verificar la BD." -ForegroundColor Yellow
        $warnings++
    }
} else {
    Write-Host "[ERROR] MySQL NO esta en ejecucion." -ForegroundColor Red
    Write-Host "   Por favor, abre Laragon y haz clic en 'Start All'" -ForegroundColor Yellow
    $errors++
}

# 4. Verificar Composer
Write-Host "`n4. Verificando Dependencias (Composer)..." -ForegroundColor White
if (Test-Path "C:\laragon\www\EGRESAPP2\vendor") {
    Write-Host "[OK] Carpeta 'vendor' existe." -ForegroundColor Green
} else {
    Write-Host "[ERROR] Carpeta 'vendor' no existe. Composer no se instalo correctamente." -ForegroundColor Red
    $errors++
}

# 5. Verificar Apache
Write-Host "`n5. Verificando Apache..." -ForegroundColor White
if (Get-Process "httpd" -ErrorAction SilentlyContinue) {
    Write-Host "[OK] Apache esta en ejecucion." -ForegroundColor Green
} else {
    Write-Host "[WARN] Apache NO esta en ejecucion." -ForegroundColor Yellow
    $warnings++
}

Write-Host ""
Write-Host "==========================================" -ForegroundColor Cyan
if ($errors -eq 0 -and $warnings -eq 0) {
    Write-Host "   TODO ESTA CORRECTO" -ForegroundColor Green
    Write-Host ""
    Write-Host "Puedes acceder a la aplicacion en:" -ForegroundColor White
    Write-Host "   http://localhost/EGRESAPP2" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Credenciales por defecto:" -ForegroundColor White
    Write-Host "   Usuario: admin@test.com" -ForegroundColor Cyan
    Write-Host "   Password: admin123" -ForegroundColor Cyan
} elseif ($errors -eq 0) {
    Write-Host "   INSTALACION FUNCIONAL CON ADVERTENCIAS" -ForegroundColor Yellow
    Write-Host "   Advertencias: $warnings" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "La aplicacion deberia funcionar." -ForegroundColor White
    Write-Host "Accede a: http://localhost/EGRESAPP2" -ForegroundColor Cyan
} else {
    Write-Host "   SE ENCONTRARON $errors ERRORES" -ForegroundColor Red
    Write-Host "   Advertencias: $warnings" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Acciones recomendadas:" -ForegroundColor Yellow
    Write-Host "1. Asegurate de que Laragon este abierto" -ForegroundColor White
    Write-Host "2. Haz clic en 'Start All' en Laragon" -ForegroundColor White
    Write-Host "3. Ejecuta ImportarBD_Manual.bat si la BD esta vacia" -ForegroundColor White
}
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Presione cualquier tecla para salir..."
$null = $Host.UI.RawUI.ReadKey('NoEcho,IncludeKeyDown')
