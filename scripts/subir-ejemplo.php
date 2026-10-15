<?php

use App\Http\Controllers\DocumentoController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = User::where('email', 'consultor@legalcloud.com')->firstOrFail();
Auth::login($user);

$request = Request::create('/documentos/ejemplo', 'POST');
$request->setUserResolver(fn () => $user);

$respuesta = app(DocumentoController::class)->storeEjemplo($request);
echo 'redirect='.$respuesta->getTargetUrl().PHP_EOL;
echo 'docs='.App\Models\Documento::count().PHP_EOL;
