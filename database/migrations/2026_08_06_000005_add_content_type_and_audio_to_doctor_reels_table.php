<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_reels', function (Blueprint $table) {
            $table->string('content_type', 20)->nullable()->after('consent')->index();
            $table->string('original_audio')->nullable()->after('original_video');
        });
    }

    public function down(): void
    {
        Schema::table('doctor_reels', function (Blueprint $table) {
            $table->dropIndex(['content_type']);
            $table->dropColumn(['content_type', 'original_audio']);
        });
    }
};
