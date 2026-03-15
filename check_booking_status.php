<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Booking;
use App\Models\User;

echo "=== ALL USERS ===" . PHP_EOL;
foreach (User::all() as $u) {
    echo "  ID:{$u->id} name:{$u->name} role:{$u->role} district_id:{$u->district_id}" . PHP_EOL;
}

echo PHP_EOL . "=== ALL BOOKINGS ===" . PHP_EOL;
foreach (Booking::all() as $b) {
    echo "  ID:{$b->id} user_id:{$b->user_id} guest:{$b->guest_name} district_id:{$b->district_id} facility_id:{$b->facility_id} status:{$b->status} date:{$b->booking_date}" . PHP_EOL;
}

echo PHP_EOL . "=== DISTRICTS ===" . PHP_EOL;
foreach (\App\Models\District::all() as $d) {
    echo "  ID:{$d->id} name:{$d->name}" . PHP_EOL;
}
