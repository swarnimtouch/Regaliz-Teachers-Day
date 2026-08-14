<?php

use App\Http\Controllers\Frontend\CampaignController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CampaignController::class, 'index'])->name('campaign.landing');
Route::post('/register', [CampaignController::class, 'store'])->middleware('throttle:20,1')->name('campaign.store');
Route::post('/logout', [CampaignController::class, 'logoutCampaign'])->name('campaign.logout');
Route::get('/choose-format', [CampaignController::class, 'chooseFormat'])->name('campaign.choose-format');
Route::post('/choose-format', [CampaignController::class, 'selectFormat'])->name('campaign.select-format');
Route::get('/record', [CampaignController::class, 'record'])->name('campaign.record');
Route::post('/record', [CampaignController::class, 'upload'])->middleware('throttle:10,1')->name('campaign.upload');
Route::get('/processing', [CampaignController::class, 'processing'])->name('campaign.processing');
Route::get('/reel-status', [CampaignController::class, 'status'])->name('campaign.status');
Route::get('/your-reel', [CampaignController::class, 'result'])->name('campaign.result');
Route::get('/download-reel', [CampaignController::class, 'download'])->name('campaign.download');
Route::get('/preview-reel', [CampaignController::class, 'previewReel'])->name('campaign.preview-reel');
Route::get('/record-audio', [CampaignController::class, 'recordAudio'])->name('campaign.record-audio');
Route::post('/record-audio', [CampaignController::class, 'uploadAudio'])->middleware('throttle:10,1')->name('campaign.upload-audio');
Route::get('/create-card', [CampaignController::class, 'createCard'])->name('campaign.create-card');
Route::post('/create-card', [CampaignController::class, 'storeCard'])->name('campaign.store-card');
Route::get('/download-card', [CampaignController::class, 'downloadCard'])->name('campaign.download-card');
Route::get('/preview-card', [CampaignController::class, 'previewCard'])->name('campaign.preview-card');

Route::get('/health', fn () => response()->json([
    'status' => 'ok',
    'application' => config('app.name'),
]))->name('health');

require __DIR__.'/admin.php';
