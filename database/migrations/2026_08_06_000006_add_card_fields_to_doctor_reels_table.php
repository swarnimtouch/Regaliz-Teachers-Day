<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_reels', function (Blueprint $table) {
            $table->string('teacher_name')->nullable()->after('content_type');
            $table->text('card_message')->nullable()->after('teacher_name');
            $table->string('generated_card')->nullable()->after('generated_video');
        });
    }

    public function down(): void
    {
        Schema::table('doctor_reels', function (Blueprint $table) {
            $table->dropColumn(['teacher_name', 'card_message', 'generated_card']);
        });
    }
};
