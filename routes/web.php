<?php

use App\Http\Controllers\FaceAnalysisController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SepayWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::post('/xem-tuong', [FaceAnalysisController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('analyses.store');

Route::controller(FaceAnalysisController::class)->prefix('ket-qua/{analysis}')->name('analyses.')->group(function () {
    Route::get('/', 'show')->name('show');
    Route::get('/chan-dung', 'portrait')->name('portrait');
    Route::get('/khuon-mat', 'face')->name('face');
    Route::post('/mo-khoa', 'unlock')->middleware('throttle:20,1')->name('unlock');
    Route::get('/don/{code}', 'orderStatus')->middleware('throttle:60,1')->name('order-status');
});

Route::post('/webhooks/sepay', SepayWebhookController::class)->name('webhooks.sepay');

Route::view('/design-system', 'pages.design-system')->name('design-system');
