<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$routes = Route::getRoutes();
foreach ($routes as $route) {
    if (in_array('PUT', $route->methods())) {
        echo implode('|', $route->methods()) . ' ' . $route->uri() . "\n";
    }
}
