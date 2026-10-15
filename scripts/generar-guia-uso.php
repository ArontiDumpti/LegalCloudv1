<?php

use App\Support\PdfSimple;

require dirname(__DIR__).'/vendor/autoload.php';

$cuerpo = <<<'TXT'
Esta guía explica qué debe hacer una persona para usar LegalCloud AI (mockup de demostración).

1. QUÉ ES LEGALCLOUD AI
Plataforma de gestión documental para organizaciones legales o administrativas. El OCR y el asistente RAG están simulados: no hay inteligencia artificial real. Sirve para mostrar expedientes, documentos, consultas y roles.

2. QUÉ NECESITA ANTES DE EMPEZAR
- Windows con XAMPP (MySQL encendido)
- PHP 8.5 (o PHP 8.3 o superior)
- Composer
- Node.js y npm
- El código del proyecto (carpeta local o GitHub)

Repositorio: https://github.com/neutrosomar/LegalCloudv1

3. PASOS PARA DEJARLO LISTO
Paso A. Encender MySQL en el panel de XAMPP.

Paso B. Crear la base de datos legalcloud en phpMyAdmin (http://localhost/phpmyadmin).

Paso C. Abrir una terminal en la carpeta del proyecto.

Paso D. Instalar dependencias:
composer install
npm install

Paso E. Copiar el archivo de entorno:
copy .env.example .env

Paso F. Editar .env y poner:
APP_NAME="LegalCloud AI"
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=legalcloud
DB_USERNAME=root
DB_PASSWORD=
(Deje la contraseña vacía si XAMPP no tiene clave en root.)

Paso G. Generar la clave y preparar la base:
php artisan key:generate
php artisan migrate
php artisan db:seed --class=LegalCloudSeeder
php artisan storage:link
npm run build

4. CÓMO ABRIR LA APLICACIÓN
En la carpeta del proyecto ejecute:
php artisan serve --host=127.0.0.1 --port=8000

Luego abra el navegador en:
http://127.0.0.1:8000/login

Si cierra esa terminal, el servidor se apaga y el navegador dirá ERR_CONNECTION_REFUSED. Vuelva a ejecutar el comando serve.

No use http://localhost sin puerto: eso es XAMPP, no LegalCloud.

5. PERSONAL, ROLES, CORREOS Y CONTRASEÑAS
La contraseña de todo el personal de demostración es: password

1) Armas López Tatiana de Fátima Lucía
   Rol: Administradora
   Correo: admin@legalcloud.com
   Contraseña: password

2) Arteaga Benites Almendra Nicolle
   Rol: Responsable documental
   Correo: documental@legalcloud.com
   Contraseña: password

3) Domínguez Alba Carlos Daniel
   Rol: Especialista / Analista
   Correo: analista@legalcloud.com
   Contraseña: password

4) Lozano Díaz Omar Jack
   Rol: Usuario consultor
   Correo: consultor@legalcloud.com
   Contraseña: password

5) Segura Venturo Williams Aaron
   Rol: Administrador
   Correo: williams@legalcloud.com
   Contraseña: password

6. QUÉ PUEDE HACER CADA ROL
Administrador (Tatiana y Williams):
- Entra a todos los módulos.
- Gestiona usuarios y roles.
- Ve la auditoría (bitácora).
- Puede usar documentos, expedientes, búsqueda y asistente.

Responsable documental (Almendra):
- Administra expedientes, categorías y documentos.
- Puede entrar a Usuarios y roles y a Auditoría.
- No es el perfil de solo consulta.

Especialista / Analista (Carlos Daniel):
- Clasifica documentos, revisa OCR y analiza.
- Puede entrar a Usuarios y roles y a Auditoría.

Usuario consultor (Omar Jack):
- Consulta expedientes, busca y pregunta al asistente RAG.
- NO puede entrar a Usuarios y roles.
- NO puede entrar a Auditoría.
- Esas opciones no aparecen en su menú. Si escribe la URL, recibe acceso denegado (403).

7. CÓMO PROBARLO RÁPIDO
1. Entre con consultor@legalcloud.com y password.
2. Confirme que no ve Usuarios y roles ni Auditoría.
3. Cierre sesión.
4. Entre con williams@legalcloud.com y password.
5. Confirme que sí ve Usuarios y roles y Auditoría.
6. En Documentos puede subir un PDF o usar el documento de ejemplo.
7. En Asistente RAG pregunte: ¿Cuáles son los requisitos establecidos para realizar determinado procedimiento?

8. SI ALGO NO FUNCIONA
- El navegador no conecta: el servidor artisan no está corriendo.
- Página en blanco: ejecute npm run build y borre public/hot si existe.
- Error de base de datos: MySQL de XAMPP debe estar encendido y la base debe llamarse legalcloud.
- No entra con un correo: use exactamente @legalcloud.com y la contraseña password.

Documento de uso interno para la demostración de LegalCloud AI.
TXT;

$pdf = PdfSimple::generar('Guía para usar LegalCloud AI', $cuerpo);
$destino = dirname(__DIR__).DIRECTORY_SEPARATOR.'Guia-para-usar-LegalCloud.pdf';
file_put_contents($destino, $pdf);

echo $destino.PHP_EOL;
echo 'bytes='.strlen($pdf).PHP_EOL;
