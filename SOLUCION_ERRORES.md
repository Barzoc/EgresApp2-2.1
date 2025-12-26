# ============================================================================
# GUÍA DE SOLUCIÓN DE PROBLEMAS - INSTALADOR EGRESAPP2
# ============================================================================

## Error: Token 'private' inesperado

**Causa:** Conflicto con variables reservadas de PowerShell ($host, $user)

**Solución:** Se corrigió el script InstaladorMaestro.ps1 usando variables para los patrones regex

**Pasos para aplicar la corrección:**

1. Descarga nuevamente los archivos corregidos del PC original
2. O reemplaza el archivo InstaladorMaestro.ps1 con la versión corregida

---

## Cómo Reinstalar

1. Elimina la carpeta donde extrajiste el instalador
2. Extrae nuevamente el ZIP con los archivos corregidos
3. Ejecuta InstaladorMaestro.bat como administrador

---

## Verificar Versión Corregida

Abre InstaladorMaestro.ps1 y busca las líneas 186-194.
Deben verse así (usando variables para los patrones regex):

```powershell
$hostPattern = 'private \$host\s*=\s*[''"].*?[''"]\s*;'
$userPattern = 'private \$user\s*=\s*[''"].*?[''"]\s*;'
$passPattern = 'private \$pass\s*=\s*[''"].*?[''"]\s*;'
$dbnamePattern = 'private \$dbname\s*=\s*[''"].*?[''"]\s*;'

$content = $content -replace $hostPattern, 'private $host = "localhost";'
$content = $content -replace $userPattern, 'private $user = "root";'
$content = $content -replace $passPattern, 'private $pass = "";'
$content = $content -replace $dbnamePattern, 'private $dbname = "gestion_egresados";'
```

---

## Contacto

Si el error persiste, revisa el log en:
`instalacion_completa_log.txt`
