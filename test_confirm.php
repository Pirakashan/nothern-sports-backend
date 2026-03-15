<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$b = App\Models\Booking::where('status', 'pending')->first();
if (!$b) {
    die("No pending bookings found\n");
}

echo "Confirming booking #{$b->id} for facility #{$b->facility_id} on {$b->booking_date}\n";

try {
    // Mock user login for the request
    $user = App\Models\User::where('role', 'system_admin')->first();
    Auth::login($user);
    
    $controller = new App\Http\Controllers\Api\SubAdminController();
    $request = request();
    
    $response = $controller->confirmBooking($request, $b->id);
    
    echo "Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Body: " . json_encode($response->getData(), JSON_PRETTY_PRINT) . "\n";
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
