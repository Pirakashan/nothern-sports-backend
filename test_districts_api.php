<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Http\Controllers\Api\DistrictController;

echo "=== TESTING DISTRICTS API ===\n";

$controller = new DistrictController();
$request = new \Illuminate\Http\Request();

try {
    $response = $controller->index();
    $data = json_decode($response->getContent(), true);
    
    echo "Districts API Response:\n";
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
