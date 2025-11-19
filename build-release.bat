<?php // Silence is golden.
@echo off
setlocal enabledelayedexpansion

REM ===============================
REM CONFIGURAÇÕES
REM ===============================
set PLUGIN_NAME=justicamultiportasrj-ai
set PLUGIN_FILE=justicamultiportasrj-ai.php
set UPDATE_FILE=update.json
set RELEASE_DIR=build
set ZIP_FILE=%PLUGIN_NAME%.zip
set REPO_URL=https://github.com/charlesvsouza/justica-multiportas-ai

REM ===============================
REM ESCOLHER TIPO DE VERSÃO
REM ===============================
echo ======================================
echo Escolha o tipo de incremento:
echo 1 - Patch (ex: 2.0.0 → 2.0.1)
echo 2 - Minor (ex: 2.0.0 → 2.1.0)
echo 3 - Major (ex: 2.0.0 → 3.0.0)
echo ======================================
set /p bump="Digite o número: "

REM ===============================
REM LER VERSÃO ATUAL
REM ===============================
for /f "tokens=2" %%A in ('findstr /R "Version:" %PLUGIN_FILE%') do (
    set CURRENT_VERSION=%%A
)
echo Versão atual: %CURRENT_VERSION%

for /f "tokens=1,2,3 delims=." %%a in ("%CURRENT_VERSION%") do (
    set major=%%a
    set minor=%%b
    set patch=%%c
)

if "%bump%"=="1" (set /a patch+=1)
if "%bump%"=="2" (set /a minor+=1 & set patch=0)
if "%bump%"=="3" (set /a major+=1 & set minor=0 & set patch=0)

set NEW_VERSION=%major%.%minor%.%patch%
echo Nova versão: %NEW_VERSION%

REM ===============================
REM ATUALIZA ARQUIVO PRINCIPAL
REM ===============================
powershell -Command "(Get-Content %PLUGIN_FILE%) -replace 'Version: %CURRENT_VERSION%', 'Version: %NEW_VERSION%' | Set-Content %PLUGIN_FILE%"

REM ===============================
REM ATUALIZA update.json
REM ===============================
powershell -Command ^
    "$json = Get-Content %UPDATE_FILE% | ConvertFrom-Json; ^
     $json.version = '%NEW_VERSION%'; ^
     $json.download_url = '%REPO_URL%/releases/latest/download/%PLUGIN_NAME%.zip'; ^
     $json | ConvertTo-Json -Depth 10 | Set-Content %UPDATE_FILE%"

REM ===============================
REM GERAR ZIP
REM ===============================
echo Gerando pacote ZIP...
rmdir /S /Q %RELEASE_DIR% 2>nul
mkdir %RELEASE_DIR%\%PLUGIN_NAME%
xcopy assets %RELEASE_DIR%\%PLUGIN_NAME%\assets /E /I /Y >nul
xcopy admin %RELEASE_DIR%\%PLUGIN_NAME%\admin /E /I /Y >nul
xcopy includes %RELEASE_DIR%\%PLUGIN_NAME%\includes /E /I /Y >nul
xcopy public %RELEASE_DIR%\%PLUGIN_NAME%\public /E /I /Y >nul
copy %PLUGIN_FILE% %RELEASE_DIR%\%PLUGIN_NAME% >nul
copy readme.txt %RELEASE_DIR%\%PLUGIN_NAME% >nul
copy update.json %RELEASE_DIR%\%PLUGIN_NAME% >nul

powershell -Command "Compress-Archive -Path '%RELEASE_DIR%\%PLUGIN_NAME%\*' -DestinationPath '%ZIP_FILE%' -Force"

echo ======================================
echo ✅ Build da versão %NEW_VERSION% concluído!
echo Pacote gerado: %ZIP_FILE%
echo ======================================
pause
