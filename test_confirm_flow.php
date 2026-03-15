<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\Booking;

echo "=== BEFORE & AFTER CONFIRMATION TEST ===\n";

// Find a pending booking
$pending = Booking::where('status', 'pending')->first();

if ($pending) {
    echo "Found pending booking ID: {$pending->id}\n";
    echo "Before update - Status: {$pending->status}\n";
    
    // Simulate what confirmBooking does
    $pending->update(['status' => 'confirmed']);
    $pending->refresh();
    
    echo "After update - Status: {$pending->status}\n";
    echo "Booking is now in database with status: confirmed\n";
    
    // Now check if the calendar API would return it
    $inCalendar = Booking::where('id', $pending->id)
        ->where('status', 'confirmed')
        ->first();
    
    if ($inCalendar) {
        echo "✓ Calendar API WOULD return this booking\n";
    } else {
        echo "✗ Calendar API would NOT return this booking\n";
    }
} else {
    echo "No pending bookings found to test\n";
}

echo "\n=== SUMMARY OF CURRENT BOOKINGS ===\n";
$summary = Booking::selectRaw('status, count(*) as total')
    ->groupBy('status')
    ->get();

foreach ($summary as $row) {
    echo "Status '{$row->status}': {$row->total} bookings\n";
}
