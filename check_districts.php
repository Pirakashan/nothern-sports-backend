<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "=== DISTRICTS ===\n";
$districts = App\Models\District::all(['id', 'name']);
foreach ($districts as $d) {
    echo "ID: {$d->id} | Name: {$d->name}\n";
}

echo "\n=== CONFIRMED BOOKINGS BY DISTRICT ===\n";
$bookings = App\Models\Booking::where('status', 'confirmed')
    ->with('district')
    ->orderBy('district_id', 'asc')
    ->get();

foreach ($bookings as $b) {
    $districtName = $b->district?->name ?? 'NO DISTRICT';
    echo "ID: {$b->id} | District ID: {$b->district_id} | District Name: {$districtName}\n";
}

echo "\n=== TEST: Fetch calendar data for each district ===\n";
foreach ($districts as $d) {
    $count = App\Models\Booking::where('district_id', $d->id)
        ->where('status', 'confirmed')
        ->count();
    echo "District: {$d->name} (ID: {$d->id}) - Confirmed Bookings: {$count}\n";
}
