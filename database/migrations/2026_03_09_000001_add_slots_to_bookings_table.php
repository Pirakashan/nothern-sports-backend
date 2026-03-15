<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('booking_mode')->default('full')->after('event_type'); // full, half, slot
            $table->json('slots')->nullable()->after('booking_mode'); // e.g. [1,3] for selected slot numbers
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['booking_mode', 'slots']);
        });
    }
};
