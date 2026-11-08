<?php

/*
| Turhost web kökü (~/public_html/index.php) — TD-063.
| Uygulama web kökünün DIŞINDA, ~/gab klasöründedir (.env, vendor, storage web'den erişilemez).
| Web kökü yolu ~/gab/.public_path dosyasından okunur (bootstrap/app.php).
*/

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$base = __DIR__.'/../gab';

if (file_exists($maintenance = $base.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $base.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $base.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
