@echo off
title LegalCloud AI
cd /d "%~dp0"

if not exist php\php.exe (
  echo Falta PHP en esta carpeta. Ejecute primero INSTALAR.bat
  pause
  exit /b 1
)

if not exist .env copy /Y .env.listo .env >nul
if not exist database\database.sqlite type nul > database\database.sqlite
if not exist storage\framework\cache\data mkdir storage\framework\cache\data
if not exist storage\framework\sessions mkdir storage\framework\sessions
if not exist storage\framework\views mkdir storage\framework\views
if not exist storage\logs mkdir storage\logs
if not exist bootstrap\cache mkdir bootstrap\cache

php\php.exe artisan migrate --force >nul 2>&1
if not exist storage\app\.listo-semilla (
  php\php.exe artisan db:seed --class=LegalCloudSeeder --force
  echo ok> storage\app\.listo-semilla
)
php\php.exe artisan storage:link >nul 2>&1

echo.
echo LegalCloud esta encendido.
echo Abra el navegador en: http://127.0.0.1:8000/app
echo Usuario: admin@legalcloud.com
echo Clave: password
echo NO cierre esta ventana.
echo.
start "" http://127.0.0.1:8000/app
php\php.exe artisan serve --host=127.0.0.1 --port=8000
pause
