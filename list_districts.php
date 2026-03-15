<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$districts = App\Models\District::all();
echo "Districts in database: " . count($districts) . PHP_EOL;
foreach ($districts as $d) {
    echo "- " . $d->name . " (ID: " . $d->id . ")" . PHP_EOL;
}
