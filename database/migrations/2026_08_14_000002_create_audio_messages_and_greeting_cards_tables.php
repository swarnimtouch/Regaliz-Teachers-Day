<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audio_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_reel_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('original_audio');
            $table->string('generated_video')->nullable();
            $table->string('status', 20)->default('processing')->index();
            $table->text('error_message')->nullable();
            $table->timestamp('processing_started_at')->nullable();
            $table->timestamp('processing_completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('greeting_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_reel_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('teacher_name');
            $table->text('message');
            $table->string('generated_card')->nullable();
            $table->string('status', 20)->default('processing')->index();
            $table->timestamp('processing_completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('greeting_cards');
        Schema::dropIfExists('audio_messages');
    }
};
