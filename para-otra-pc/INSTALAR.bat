@echo off
title Instalar LegalCloud AI
cd /d "%~dp0"
echo Este instalador usa comandos de CMD, no de PowerShell.
echo.

if not exist php\php.exe (
  echo Descargando PHP portable...
  curl.exe -L "https://downloads.php.net/~windows/releases/php-8.5.11-nts-Win32-vs17-x64.zip" -o php.zip
  if errorlevel 1 (
    echo No se pudo descargar PHP. Revise internet.
    pause
    exit /b 1
  )
  mkdir php 2>nul
  tar -xf php.zip -C php
  del php.zip
  if exist php\php.ini-production copy /Y php\php.ini-production php\php.ini >nul
  powershell -NoProfile -Command "(Get-Content php\php.ini) -replace ';extension_dir = \"ext\"','extension_dir = \"ext\"' -replace ';extension=openssl','extension=openssl' -replace ';extension=curl','extension=curl' -replace ';extension=mbstring','extension=mbstring' -replace ';extension=fileinfo','extension=fileinfo' -replace ';extension=pdo_sqlite','extension=pdo_sqlite' -replace ';extension=sqlite3','extension=sqlite3' -replace ';extension=zip','extension=zip' -replace ';extension=intl','extension=intl' | Set-Content php\php.ini"
)

if not exist vendor\autoload.php (
  echo Descargando Composer...
  curl.exe -L "https://getcomposer.org/download/latest-stable/composer.phar" -o composer.phar
  php\php.exe composer.phar install --no-dev --no-interaction
)

if not exist .env copy /Y .env.listo .env >nul
if not exist database\database.sqlite type nul > database\database.sqlite
php\php.exe artisan key:generate --force
php\php.exe artisan migrate --force
php\php.exe artisan db:seed --class=LegalCloudSeeder --force
echo ok> storage\app\.listo-semilla
php\php.exe artisan storage:link >nul 2>&1

echo.
echo Listo. Ahora ejecute INICIAR.bat
pause
