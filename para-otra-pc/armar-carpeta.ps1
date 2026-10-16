$ErrorActionPreference = "Stop"
$origen = "C:\repo\example-app"
$destino = "C:\repo\LegalCloud-listo"
$phpZip = "https://downloads.php.net/~windows/releases/php-8.5.11-nts-Win32-vs17-x64.zip"

if (Test-Path $destino) {
    Remove-Item $destino -Recurse -Force
}
New-Item -ItemType Directory -Path $destino | Out-Null

Write-Host "Copiando proyecto..."
robocopy $origen $destino /E /NFL /NDL /NJH /NJS /nc /ns /np `
    /XD node_modules .git .cursor tests .phpunit.cache bootstrap\ssr `
    /XF Homestead.json Homestead.yaml | Out-Null

Copy-Item "$origen\para-otra-pc\INICIAR.bat" $destino -Force
Copy-Item "$origen\para-otra-pc\INSTALAR.bat" $destino -Force
Copy-Item "$origen\para-otra-pc\LEEME.txt" $destino -Force
Copy-Item "$origen\para-otra-pc\.env.listo" "$destino\.env.listo" -Force
Copy-Item "$origen\para-otra-pc\.env.listo" "$destino\.env" -Force
Copy-Item "$origen\Instalar-LegalCloud.html" $destino -Force -ErrorAction SilentlyContinue

New-Item -ItemType Directory -Force -Path "$destino\storage\framework\cache\data" | Out-Null
New-Item -ItemType Directory -Force -Path "$destino\storage\framework\sessions" | Out-Null
New-Item -ItemType Directory -Force -Path "$destino\storage\framework\views" | Out-Null
New-Item -ItemType Directory -Force -Path "$destino\storage\logs" | Out-Null
New-Item -ItemType Directory -Force -Path "$destino\bootstrap\cache" | Out-Null
New-Item -ItemType Directory -Force -Path "$destino\database" | Out-Null
if (-not (Test-Path "$destino\database\database.sqlite")) {
    New-Item "$destino\database\database.sqlite" -ItemType File | Out-Null
}

Write-Host "Descargando PHP portable..."
$zip = Join-Path $env:TEMP "php-legalcloud.zip"
curl.exe -L $phpZip -o $zip
if (-not (Test-Path $zip) -or (Get-Item $zip).Length -lt 1000000) {
    throw "No se pudo descargar PHP"
}
New-Item -ItemType Directory -Force -Path "$destino\php" | Out-Null
Expand-Archive -Path $zip -DestinationPath "$destino\php" -Force
Copy-Item "$destino\php\php.ini-production" "$destino\php\php.ini" -Force
$ini = Get-Content "$destino\php\php.ini"
$ini = $ini -replace ';extension_dir = "ext"', 'extension_dir = "ext"'
foreach ($ext in @("openssl", "curl", "mbstring", "fileinfo", "pdo_sqlite", "sqlite3", "zip", "intl")) {
    $ini = $ini -replace ";extension=$ext", "extension=$ext"
}
Set-Content "$destino\php\php.ini" $ini
Remove-Item $zip -Force -ErrorAction SilentlyContinue

Write-Host "Preparando base y clave..."
Push-Location $destino
& "$destino\php\php.exe" artisan key:generate --force
& "$destino\php\php.exe" artisan migrate --force
& "$destino\php\php.exe" artisan db:seed --class=LegalCloudSeeder --force
New-Item -ItemType File -Force -Path "$destino\storage\app\.listo-semilla" | Out-Null
& "$destino\php\php.exe" artisan storage:link
Pop-Location

Write-Host "LISTO: $destino"
Get-ChildItem $destino | Select-Object Name
