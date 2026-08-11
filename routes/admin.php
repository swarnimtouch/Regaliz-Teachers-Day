<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DoctorController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'create'])->name('login');
        Route::post('/login', [AuthController::class, 'store'])
            ->middleware('throttle:5,1')
            ->name('login.store');
    });

    Route::middleware('admin')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/video-recordings', [DoctorController::class, 'videos'])->name('videos.index');
        Route::get('/audio-messages', [DoctorController::class, 'audios'])->name('audios.index');
        Route::get('/greeting-cards', [DoctorController::class, 'cards'])->name('cards.index');
        Route::get('/doctors/export', [DoctorController::class, 'export'])->name('doctors.export');
        Route::post('/doctors/{doctorReel}/regenerate', [DoctorController::class, 'regenerate'])->name('doctors.regenerate');
        Route::get('/doctors/{doctorReel}/download', [DoctorController::class, 'download'])->name('doctors.download');
        Route::get('/doctors/{doctorReel}/media/{kind}', [DoctorController::class, 'media'])->name('doctors.media');
        Route::resource('doctors', DoctorController::class)->only(['index', 'show', 'destroy'])->parameters(['doctors' => 'doctorReel']);
        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::get('/profile/password', [ProfileController::class, 'editPassword'])->name('profile.password.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
        Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    });
});
