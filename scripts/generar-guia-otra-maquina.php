<?php

use App\Support\PdfSimple;

require dirname(__DIR__).'/vendor/autoload.php';

$cuerpo = <<<'TXT'
Esta guía es para instalar LegalCloud AI en OTRA computadora con Windows. En el PDF seleccione el comando (la línea que empieza con COPIAR), pulse Ctrl+C y péguelo en la terminal con clic derecho. Un comando a la vez.

FORMA MÁS FÁCIL (recomendado)
Si le pasaron la carpeta LegalCloud-listo (USB, Drive o Discord):

1. Copie TODA la carpeta a la otra PC (por ejemplo a C:\LegalCloud-listo).
2. Abra la carpeta.
3. Doble clic en INICIAR.bat
4. Se abre el navegador en http://127.0.0.1:8000/app
5. Entre con admin@legalcloud.com y clave password
6. NO cierre la ventana negra. Esa ventana es el servidor.

No necesita Git, Composer, Node ni winget.
Si INICIAR.bat dice que falta PHP, doble clic en INSTALAR.bat y luego otra vez INICIAR.bat.
Si pide VCRUNTIME140.dll, instale https://aka.ms/vs/17/release/vc_redist.x64.exe y vuelva a INICIAR.bat.

Si NO tiene esa carpeta, siga desde el paso 1.

1. ABRIR LA TERMINAL CORRECTA
Abra "Símbolo del sistema" (cmd). No use PowerShell si un comando "no se reconoce".
En Inicio escriba cmd y pulse Enter.

2. PROGRAMAS (solo si faltan)
Primero compruebe. Seleccione, Ctrl+C, péguelo en cmd:

COPIAR: git --version
COPIAR: php -v
COPIAR: node -v
COPIAR: npm -v

Si escribe solo git y sale "usage: git...", Git YA está.
PHP debe ser 8.3 o 8.5. Si es 8.2 o menos, instale PHP otra vez.
Si dice que no se reconoce, instale ese programa, CIERRE cmd y abra otro.

Instalar con winget (en cmd también sirve):

PASO 1 - Git
COPIAR: winget install --id Git.Git -e --accept-package-agreements --accept-source-agreements

PASO 2 - PHP 8.5
COPIAR: winget install --id PHP.PHP.8.5 -e --accept-package-agreements --accept-source-agreements

PASO 3 - Composer NO está en winget. PHP debe funcionar. Luego:

COPIAR: curl.exe -L https://getcomposer.org/Composer-Setup.exe -o %TEMP%\Composer-Setup.exe
COPIAR: %TEMP%\Composer-Setup.exe

Se abre el instalador. Siguiente, siguiente. Cierre cmd. Abra otro.
COPIAR: composer -V

PASO 4 - Node.js
COPIAR: winget install --id OpenJS.NodeJS.LTS -e --accept-package-agreements --accept-source-agreements

Cierre cmd y abra otro. Vuelva a comprobar git, php, composer, node y npm.

Si no quiere winget, descargue a mano:
Git: https://git-scm.com/download/win
PHP 8.3 o 8.5: https://windows.php.net/download/
Composer: https://getcomposer.org/download/  (Composer-Setup.exe)
Node.js LTS: https://nodejs.org

3. SI YA ESTÁ EN C:\repo\LegalCloud
Solo encender:

COPIAR: cd /d C:\repo\LegalCloud
COPIAR: php artisan serve --host=127.0.0.1 --port=8000

No cierre esa ventana. Navegador: http://127.0.0.1:8000/app

Si hay código nuevo en GitHub:

COPIAR: cd /d C:\repo\LegalCloud
COPIAR: git pull
COPIAR: composer install
COPIAR: npm install
COPIAR: php artisan migrate
COPIAR: npm run build
COPIAR: php artisan serve --host=127.0.0.1 --port=8000

4. PRIMERA VEZ (aún no está el proyecto)
Use Símbolo del sistema (cmd). Un comando por vez.

PASO 7
COPIAR: cd /d C:\

PASO 8
COPIAR: if not exist repo mkdir repo

PASO 9
COPIAR: cd /d C:\repo

PASO 10
COPIAR: git clone https://github.com/ArontiDumpti/LegalCloudv1.git LegalCloud

Si el clone falla:
COPIAR: git clone https://github.com/neutrosomar/LegalCloudv1.git LegalCloud

PASO 11
COPIAR: cd /d C:\repo\LegalCloud

PASO 12
COPIAR: copy .env.example .env

PASO 13
COPIAR: if not exist database\database.sqlite type nul > database\database.sqlite

PASO 14
COPIAR: composer install

PASO 15
COPIAR: npm install

PASO 16
COPIAR: php artisan key:generate

PASO 17
COPIAR: php artisan migrate

PASO 18
COPIAR: php artisan db:seed --class=LegalCloudSeeder

PASO 19
COPIAR: php artisan storage:link

PASO 20
COPIAR: npm run build

PASO 21 - Encender. NO cierre esta ventana.
COPIAR: php artisan serve --host=127.0.0.1 --port=8000

PASO 22 - En OTRA ventana cmd:
COPIAR: start http://127.0.0.1:8000/app

No use http://localhost sin :8000.

5. CUENTAS (contraseña de todos: password)
admin@legalcloud.com - Administradora
documental@legalcloud.com - Documentos
analista@legalcloud.com - Especialista
consultor@legalcloud.com - Solo consulta
williams@legalcloud.com - Administrador

6. SI ALGO FALLA
- "no se reconoce": está en PowerShell o falta el programa. Use cmd, o la carpeta LegalCloud-listo.
- Composer no se encuentra con winget: es normal. Use Composer-Setup.exe (paso 3).
- php no se reconoce: C:\xampp\php\php.exe o instale PHP 8.5 y abra otro cmd.
- Página en blanco: npm run build
- ERR_CONNECTION_REFUSED: no está corriendo artisan serve o INICIAR.bat
- PowerShell bloquea: use cmd, no PowerShell.

Guía PDF para instalar LegalCloud AI en otra computadora Windows.
TXT;

$pdf = PdfSimple::generar('Instalar LegalCloud en otra computadora', $cuerpo);
$destino = dirname(__DIR__).DIRECTORY_SEPARATOR.'Como-ejecutar-LegalCloud-en-otra-maquina.pdf';
file_put_contents($destino, $pdf);

echo $destino.PHP_EOL;
echo 'bytes='.strlen($pdf).PHP_EOL;
