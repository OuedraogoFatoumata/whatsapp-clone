<?php
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));


file_put_contents(__DIR__.'/../storage/logs/diagnostic.log', 
    "[".date('Y-m-d H:i:s')."] ETAPE 1: index.php charge\n", FILE_APPEND);


if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// LOG 2 - Autoloader
file_put_contents(__DIR__.'/../storage/logs/diagnostic.log', 
    "[".date('Y-m-d H:i:s')."] ETAPE 2: avant autoloader\n", FILE_APPEND);

require __DIR__.'/../vendor/autoload.php';

// LOG 3 - Bootstrap
file_put_contents(__DIR__.'/../storage/logs/diagnostic.log', 
    "[".date('Y-m-d H:i:s')."] ETAPE 3: avant bootstrap app\n", FILE_APPEND);

$app = require_once __DIR__.'/../bootstrap/app.php';

// LOG 4 - Handle request
file_put_contents(__DIR__.'/../storage/logs/diagnostic.log', 
    "[".date('Y-m-d H:i:s')."] ETAPE 4: avant handleRequest\n", FILE_APPEND);

$app->handleRequest(Request::capture());

// LOG 5 - Fin
file_put_contents(__DIR__.'/../storage/logs/diagnostic.log', 
    "[".date('Y-m-d H:i:s')."] ETAPE 5: requete terminee\n", FILE_APPEND);