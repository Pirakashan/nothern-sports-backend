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
            $table->string('event_name')->nullable()->after('type');
            $table->text('sports_list')->nullable()->after('event_name');
            $table->decimal('price_gov_schools', 10, 2)->default(0)->after('price_per_hour');
            $table->decimal('price_club_institute', 10, 2)->default(0)->after('price_gov_schools');
            $table->decimal('price_intl_schools', 10, 2)->default(0)->after('price_club_institute');
            $table->decimal('price_intl', 10, 2)->default(0)->after('price_intl_schools');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricing_tables', function (Blueprint $table) {
            $table->dropColumn([
                'event_name',
                'sports_list',
                'price_gov_schools',
                'price_club_institute',
                'price_intl_schools',
                'price_intl'
            ]);
        });
    }
};
