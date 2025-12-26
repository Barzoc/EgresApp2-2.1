
$conexionFile = "c:\laragon\www\EGRESAPP2\modelo\Conexion.php"
$content = Get-Content $conexionFile -Raw

# These are the patterns we just put into InstaladorMaestro.ps1
$hostPattern = 'private \$host\s*=\s*[''"].*?[''"]\s*;'
$userPattern = 'private \$user\s*=\s*[''"].*?[''"]\s*;'
$passPattern = 'private \$pass\s*=\s*[''"].*?[''"]\s*;'
$dbnamePattern = 'private \$dbname\s*=\s*[''"].*?[''"]\s*;'

Write-Host "Original Content Sample:"
$content.Split("`n")[5..10] | ForEach-Object { Write-Host $_ }

if ($content -match $hostPattern) {
    Write-Host "`nMatch FOUND for host pattern!" -ForegroundColor Green
}
else {
    Write-Host "`nMatch NOT FOUND for host pattern!" -ForegroundColor Red
}

# Simulate the replacement
$content = $content -replace $hostPattern, 'private $host = ''VERIFIED_LOCALHOST'';'
$content = $content -replace $userPattern, 'private $user = ''VERIFIED_ROOT'';'

Write-Host "`nModified Content Sample:"
$content.Split("`n")[5..10] | ForEach-Object { Write-Host $_ }

if ($content -match 'VERIFIED_LOCALHOST') {
    Write-Host "`nReplacement SUCCESSFUL!" -ForegroundColor Green
}
else {
    Write-Host "`nReplacement FAILED!" -ForegroundColor Red
}
