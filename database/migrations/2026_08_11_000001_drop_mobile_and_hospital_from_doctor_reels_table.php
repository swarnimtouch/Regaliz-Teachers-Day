<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_reels', function (Blueprint $table): void {
            $table->dropColumn(['mobile', 'hospital_name']);
        });
    }

    public function down(): void
    {
        Schema::table('doctor_reels', function (Blueprint $table): void {
            $table->string('mobile', 20)->nullable()->after('city');
            $table->string('hospital_name')->nullable()->after('mobile');
        });
    }
};
