<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 * 
 * Este archivo es el punto de entrada principal para todas las solicitudes HTTP
 * entrantes a la aplicación Laravel. Configura el entorno y maneja la solicitud.
 */

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

// Define el tiempo de inicio de la aplicación para medir el tiempo de ejecución
define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Verificar si la aplicación está en modo mantenimiento
|--------------------------------------------------------------------------
|
| Si la aplicación está en modo mantenimiento/demo mediante el comando "down",
| cargamos este archivo para mostrar contenido pre-renderizado en lugar de
| iniciar el framework, lo que podría causar una excepción.
|
*/

// Verifica si existe el archivo de mantenimiento
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Registrar el Autoloader
|--------------------------------------------------------------------------
|
| Composer proporciona un cargador de clases automático generado convenientemente
| para esta aplicación. Solo necesitamos utilizarlo. Simplemente lo requerimos
| en el script aquí para no tener que cargar manualmente nuestras clases.
|
*/

// Carga el autoloader de Composer
require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Ejecutar la Aplicación
|--------------------------------------------------------------------------
|
| Una vez que tenemos la aplicación, podemos manejar la solicitud entrante
| usando el kernel HTTP de la aplicación. Luego, enviaremos la respuesta
| de vuelta al navegador del cliente, permitiéndole disfrutar de nuestra app.
|
*/

// Carga la instancia de la aplicación Laravel
$app = require_once __DIR__.'/../bootstrap/app.php';

// Crea una instancia del kernel HTTP
$kernel = $app->make(Kernel::class);

// Captura la solicitud HTTP entrante y la procesa
$response = $kernel->handle(
    $request = Request::capture() // Captura la solicitud actual
)->send(); // Envía la respuesta al cliente

// Termina la ejecución de la aplicación (limpieza, etc.)
$kernel->terminate($request, $response);