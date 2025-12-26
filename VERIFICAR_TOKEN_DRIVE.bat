@echo off
chcp 65001 > nul
echo.
echo ========================================
echo   VERIFICAR TOKEN GOOGLE DRIVE
echo ========================================
echo.

cd /d "%~dp0"

php verify_token_status.php

echo.
pause
