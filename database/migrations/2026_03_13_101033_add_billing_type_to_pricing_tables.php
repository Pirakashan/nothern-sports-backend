<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pricing_tables', function (Blueprint $table) {
            $table->string('billing_type')->default('hourly')->after('sports_list'); // hourly, daily
            $table->decimal('price_per_day', 10, 2)->default(0)->after('price_per_hour');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricing_tables', function (Blueprint $table) {
            $table->dropColumn(['billing_type', 'price_per_day']);
        });
    }
};
