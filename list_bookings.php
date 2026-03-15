<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "=== CONFIRMED BOOKINGS ===\n";
$confirmed = App\Models\Booking::where('status', 'confirmed')->get();
foreach ($confirmed as $c) {
    echo "ID:{$c->id} | Facility:{$c->facility_id} | Date:{$c->booking_date} | Time:{$c->start_time}-{$c->end_time}\n";
}

echo "\n=== PENDING BOOKINGS ===\n";
$pending = App\Models\Booking::where('status', 'pending')->get();
foreach ($pending as $p) {
    echo "ID:{$p->id} | Facility:{$p->facility_id} | Date:{$p->booking_date} | Time:{$p->start_time}-{$p->end_time}\n";
}
