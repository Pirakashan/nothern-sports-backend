<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Http\Request;
use App\Http\Controllers\Api\CalendarController;

echo "=== TESTING CALENDAR API ENDPOINT ===\n";

// Test for Vavuniya (district_id = 1)
$request = new Request(['district_id' => 1]);
$controller = new CalendarController();

try {
    $response = $controller->index($request);
    $data = json_decode($response->getcontent(), true);
    
    echo "District ID: 1 (VavuniySUya)\n";
    echo "Response Status: 200\n";
    echo "Number of bookings: " . count($data['calendar']) . "\n";
    
    if (count($data['calendar']) > 0) {
        echo "\nFirst booking:\n";
        echo json_encode($data['calendar'][0], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    }
} catch (\Exception $e) {
    echo "Error at district 1: " . $e->getMessage() . "\n";
}

echo "\n";

// Test for Kilinochchi (district_id = 2)
$request = new Request(['district_id' => 2]);

try {
    $response = $controller->index($request);
    $data = json_decode($response->getContent(), true);
    
    echo "District ID: 2 (Kilinochchi)\n";
    echo "Response Status: 200\n";
    echo "Number of bookings: " . count($data['calendar']) . "\n";
    
    if (count($data['calendar']) > 0) {
        echo "\nFirst booking:\n";
        echo json_encode($data['calendar'][0], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    }
} catch (\Exception $e) {
    echo "Error at district 2: " . $e->getMessage() . "\n";
}

echo "\n=== CHECKING DATABASE DIRECTLY ===\n";
$vavuniya_bookings = App\Models\Booking::where('district_id', 1)
    ->where('status', 'confirmed')
    ->with(['facility:id,name,slug'])
    ->get();

echo "Vavuniya confirmed bookings (direct query): " . $vavuniya_bookings->count() . "\n";

foreach ($vavuniya_bookings as $b) {
    echo sprintf(
        "  ID: %d | Facility: %s | Slug: %s | Date: %s\n",
        $b->id,
        $b->facility?->name ?? 'NULL',
        $b->facility?->slug ?? 'NULL',
        $b->booking_date->format('Y-m-d')
    );
}
