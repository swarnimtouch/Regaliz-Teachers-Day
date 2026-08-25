<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_reels', function (Blueprint $table): void {
            $table->text('original_video_url')->nullable()->after('original_video');
            $table->text('original_audio_url')->nullable()->after('original_audio');
            $table->text('generated_video_url')->nullable()->after('generated_video');
            $table->text('generated_card_url')->nullable()->after('generated_card');
        });

        Schema::table('audio_messages', function (Blueprint $table): void {
            $table->text('original_audio_url')->nullable()->after('original_audio');
            $table->text('generated_video_url')->nullable()->after('generated_video');
        });

        Schema::table('greeting_cards', function (Blueprint $table): void {
            $table->text('generated_card_url')->nullable()->after('generated_card');
        });
    }

    public function down(): void
    {
        Schema::table('doctor_reels', fn (Blueprint $table) => $table->dropColumn(['original_video_url', 'original_audio_url', 'generated_video_url', 'generated_card_url']));
        Schema::table('audio_messages', fn (Blueprint $table) => $table->dropColumn(['original_audio_url', 'generated_video_url']));
        Schema::table('greeting_cards', fn (Blueprint $table) => $table->dropColumn('generated_card_url'));
    }
};
