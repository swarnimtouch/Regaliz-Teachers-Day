<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_reels', function (Blueprint $table) {
            $table->decimal('video_zoom', 3, 2)->default(1.00)->after('original_video');
        });
    }

    public function down(): void
    {
        Schema::table('doctor_reels', function (Blueprint $table) {
            $table->dropColumn('video_zoom');
        });
    }
};
