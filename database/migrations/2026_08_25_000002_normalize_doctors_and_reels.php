<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('speciality')->default('Not specified')->index();
            $table->string('city')->index();
            $table->timestamps();
        });

        Schema::table('doctor_reels', function (Blueprint $table): void {
            $table->foreignId('doctor_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        DB::table('doctor_reels')->orderBy('id')->each(function (object $reel): void {
            $doctorId = DB::table('doctors')->insertGetId([
                'name' => $reel->doctor_name,
                'speciality' => $reel->speciality ?: 'Not specified',
                'city' => $reel->city,
                'created_at' => $reel->created_at,
                'updated_at' => $reel->updated_at,
            ]);
            DB::table('doctor_reels')->where('id', $reel->id)->update(['doctor_id' => $doctorId]);
        });

        Schema::table('doctor_reels', function (Blueprint $table): void {
            $table->dropIndex(['speciality']);
            $table->dropIndex(['city']);
        });

        Schema::table('doctor_reels', function (Blueprint $table): void {
            $table->dropColumn(['doctor_name', 'speciality', 'city']);
        });
    }

    public function down(): void
    {
        Schema::table('doctor_reels', function (Blueprint $table): void {
            $table->string('doctor_name')->nullable();
            $table->string('speciality')->nullable()->index();
            $table->string('city')->nullable()->index();
        });

        DB::table('doctor_reels')->join('doctors', 'doctors.id', '=', 'doctor_reels.doctor_id')->update([
            'doctor_reels.doctor_name' => DB::raw('doctors.name'),
            'doctor_reels.speciality' => DB::raw('doctors.speciality'),
            'doctor_reels.city' => DB::raw('doctors.city'),
        ]);

        Schema::table('doctor_reels', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('doctor_id');
        });
        Schema::dropIfExists('doctors');
    }
};
