# Script PowerShell para probar conexión a MySQL
Write-Host "=== TEST DE CONEXIÓN A BASE DE DATOS ===" -ForegroundColor Cyan
Write-Host ""

# Configuración de la base de datos (ajusta según tu Conexion.php)
$server = "localhost"
$database = "gestion_egresados"
$user = "root"
$password = ""

Write-Host "Configuración:" -ForegroundColor Yellow
Write-Host "  Servidor: $server"
Write-Host "  Base de datos: $database"
Write-Host "  Usuario: $user"
Write-Host ""

# Crear cadena de conexión
$connectionString = "server=$server;database=$database;uid=$user;pwd=$password;charset=utf8mb4"

try {
    # Cargar el ensamblado MySQL
    Write-Host "1. Cargando driver MySQL..." -ForegroundColor Yellow
    [void][System.Reflection.Assembly]::LoadWithPartialName("MySql.Data")
    
    # Crear conexión
    Write-Host "2. Creando conexión..." -ForegroundColor Yellow
    $connection = New-Object MySql.Data.MySqlClient.MySqlConnection
    $connection.ConnectionString = $connectionString
    
    # Abrir conexión
    Write-Host "3. Abriendo conexión..." -ForegroundColor Yellow
    $connection.Open()
    
    Write-Host "✓ CONEXIÓN EXITOSA" -ForegroundColor Green
    Write-Host ""
    
    # Ejecutar consulta de prueba
    Write-Host "4. Ejecutando consulta: SELECT COUNT(*) FROM egresado" -ForegroundColor Yellow
    $command = $connection.CreateCommand()
    $command.CommandText = "SELECT COUNT(*) as total FROM egresado"
    $result = $command.ExecuteScalar()
    
    Write-Host "✓ Total de egresados en la tabla: $result" -ForegroundColor Green
    Write-Host ""
    
    # Obtener primeros 5 registros
    Write-Host "5. Obteniendo primeros 5 registros..." -ForegroundColor Yellow
    $command.CommandText = "SELECT identificacion, nombreCompleto, carnet, sexo FROM egresado LIMIT 5"
    $reader = $command.ExecuteReader()
    
    $count = 0
    while ($reader.Read()) {
        $count++
        Write-Host "  Registro $count:" -ForegroundColor Cyan
        Write-Host "    ID: $($reader['identificacion'])"
        Write-Host "    Nombre: $($reader['nombreCompleto'])"
        Write-Host "    Carnet: $($reader['carnet'])"
        Write-Host "    Sexo: $($reader['sexo'])"
        Write-Host ""
    }
    $reader.Close()
    
    # Verificar tablas titulo y tituloegresado
    Write-Host "6. Verificando existencia de tablas..." -ForegroundColor Yellow
    $command.CommandText = "SHOW TABLES LIKE 'titulo'"
    $tituloExists = $command.ExecuteScalar()
    
    $command.CommandText = "SHOW TABLES LIKE 'tituloegresado'"
    $tituloEgresadoExists = $command.ExecuteScalar()
    
    if ($tituloExists) {
        Write-Host "  ✓ Tabla 'titulo' existe" -ForegroundColor Green
    } else {
        Write-Host "  ✗ Tabla 'titulo' NO existe" -ForegroundColor Red
    }
    
    if ($tituloEgresadoExists) {
        Write-Host "  ✓ Tabla 'tituloegresado' existe" -ForegroundColor Green
    } else {
        Write-Host "  ✗ Tabla 'tituloegresado' NO existe" -ForegroundColor Red
    }
    
    # Cerrar conexión
    $connection.Close()
    Write-Host ""
    Write-Host "=== TEST COMPLETADO EXITOSAMENTE ===" -ForegroundColor Green
    
} catch {
    Write-Host ""
    Write-Host "✗ ERROR: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host ""
    Write-Host "Detalles del error:" -ForegroundColor Yellow
    Write-Host $_.Exception.ToString()
    
    if ($connection.State -eq 'Open') {
        $connection.Close()
    }
}

Write-Host ""
Write-Host "Presiona cualquier tecla para salir..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
