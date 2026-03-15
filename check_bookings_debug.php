<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Booking;
use App\Models\User;

echo "=== Bookings in DB ===\n";
echo "Total: " . Booking::count() . "\n\n";

foreach (Booking::with(['facility', 'district'])->get() as $b) {
    $distName = $b->district ? $b->district->name : 'N/A';
    $facName = $b->facility ? $b->facility->name : 'N/A';
    echo "ID:{$b->id} | district_id:{$b->district_id} ({$distName}) | facility:{$facName} | status:{$b->status} | date:{$b->booking_date} | user_id:{$b->user_id} | guest:{$b->guest_name}\n";
}

echo "\n=== Sub Admin Users ===\n";
foreach (User::where('role', 'sub_admin')->get() as $u) {
    echo "ID:{$u->id} | name:{$u->name} | district_id:{$u->district_id} | role:{$u->role}\n";
}

echo "\n=== Districts ===\n";
foreach (\App\Models\District::all() as $d) {
    echo "ID:{$d->id} | name:{$d->name}\n";
}
