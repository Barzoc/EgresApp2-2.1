
$content = "    private `$host='localhost';"
$hostPattern_bad = 'private \$host\s*=\s*[''"].*?[''"]\\s*;'
$hostPattern_good = 'private \$host\s*=\s*[''"].*?[''"]\s*;'

Write-Host "Testing Content: $content"
Write-Host "Bad Pattern: $hostPattern_bad"
Write-Host "Good Pattern: $hostPattern_good"

if ($content -match $hostPattern_bad) {
    Write-Host "Bad Pattern MATCHED" -ForegroundColor Red
}
else {
    Write-Host "Bad Pattern DID NOT MATCH" -ForegroundColor Green
}

if ($content -match $hostPattern_good) {
    Write-Host "Good Pattern MATCHED" -ForegroundColor Green
}
else {
    Write-Host "Good Pattern DID NOT MATCH" -ForegroundColor Red
}

$replacement = "private `$host = 'NEW_HOST';"
$newContent = $content -replace $hostPattern_good, $replacement
Write-Host "Replaced Content: $newContent"
