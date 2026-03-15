<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['system_admin', 'sub_admin', 'user'])->default('user')->after('password');
            $table->string('phone')->nullable()->after('email');
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['district_id']);
            $table->dropColumn(['role', 'phone', 'district_id']);
        });
    }
};
