# InstalarTarea.ps1

# Auto-Elevación: Si no es Admin, se reinicia pidiendo permiso
if (!([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole] "Administrator")) {
    Write-Host "Solicitando permisos de Administrador..."
    Start-Process powershell.exe "-NoProfile -ExecutionPolicy Bypass -File `"$PSCommandPath`"" -Verb RunAs
    exit
}

$taskName = "SincronizarEgresApp"
$actionPath = "C:\laragon\www\EGRESAPP2\Sincronizar.bat"
$workingDir = "C:\laragon\www\EGRESAPP2"

Write-Host "Creando tarea programada: $taskName..."

# Definir la acción (Ejecutar el .bat)
$action = New-ScheduledTaskAction -Execute $actionPath -WorkingDirectory $workingDir

# Definir el disparador (Al iniciar sesión)
$trigger = New-ScheduledTaskTrigger -AtLogon

# Definir configuración
$settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -ExecutionTimeLimit (New-TimeSpan -Minutes 5)

# Registrar la tarea
try {
    Register-ScheduledTask -Action $action -Trigger $trigger -Settings $settings -TaskName $taskName -Description "Sincroniza BD Egresados via VPN" -ErrorAction Stop
    Write-Host "¡Tarea programada EXITOSAMENTE!" -ForegroundColor Green
    Write-Host "La sincronización se ejecutará al iniciar sesión."
} catch {
    Write-Host "Error al crear la tarea: $_" -ForegroundColor Red
    Write-Host "INTENTA EJECUTAR COMO ADMINISTRADOR." -ForegroundColor Yellow
}

Write-Host "Presiona Enter para salir..."
Read-Host
