<?php

require __DIR__ . '/vendor/autoload.php';

try {
    echo "Loading Laravel app...\n";
    $app = require_once __DIR__ . '/bootstrap/app.php';
    echo "Creating kernel...\n";
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "Handling request...\n";
    $kernel->handle(Illuminate\Http\Request::capture());
    
    echo "Testing database connection...\n";
    $connected = DB::connection()->getPdo();
    echo "✓ Database connected!\n";
    
    $districts = App\Models\District::all();
    echo "✓ Retrieved " . count($districts) . " districts\n";
    
    foreach ($districts as $d) {
        echo "  - " . $d->name . " (ID: " . $d->id . ")\n";
    }
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
