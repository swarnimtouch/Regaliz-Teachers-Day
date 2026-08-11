<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_reels', function (Blueprint $table) {
            $table->id();
            $table->string('reference_id', 32)->unique();
            $table->string('doctor_name');
            $table->string('speciality')->index();
            $table->string('city')->index();
            $table->string('mobile', 20)->nullable();
            $table->string('hospital_name')->nullable();
            $table->boolean('consent');
            $table->string('original_video')->nullable();
            $table->string('details_image')->nullable();
            $table->string('generated_video')->nullable();
            $table->foreignId('template_id')->nullable()->constrained('reel_templates')->nullOnDelete();
            $table->string('status', 20)->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('download_count')->default(0);
            $table->timestamp('processing_started_at')->nullable();
            $table->timestamp('processing_completed_at')->nullable();
            $table->timestamp('processing_failed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['created_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_reels');
    }
};
