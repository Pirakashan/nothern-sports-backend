<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pricing_tables', function (Blueprint $table) {
            $table->dropColumn('price_per_day');
        });
    }

    public function down(): void
    {
        Schema::table('pricing_tables', function (Blueprint $table) {
            $table->decimal('price_per_day', 10, 2)->default(0)->after('price_per_hour');
        });
    }
};
