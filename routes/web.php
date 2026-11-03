<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\AnalysisStreamController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CustomerPasswordResetController;
use App\Http\Controllers\ExpertController;
use App\Http\Controllers\FaceAnalysisController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PalmReadingBookingController;
use App\Http\Controllers\PortraitUploadController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SepayWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::post('/xem-tuong', [FaceAnalysisController::class, 'store'])
    ->middleware('throttle:10,1,analysis-store')
    ->name('analyses.store');

Route::post('/xem-tuong/anh', PortraitUploadController::class)
    ->middleware('throttle:20,1,portrait-upload')
    ->name('portraits.upload');

Route::post('/xem-chi-tay', PalmReadingBookingController::class)
    ->middleware('throttle:5,1,palm-reading-store')
    ->name('palm-readings.store');

Route::controller(FaceAnalysisController::class)->prefix('ket-qua/{analysis}')->name('analyses.')->group(function () {
    Route::get('/', 'show')->name('show');
    Route::get('/chan-dung', 'portrait')->name('portrait');
    Route::get('/khuon-mat', 'face')->name('face');
    Route::post('/mo-khoa', 'unlock')->middleware('throttle:20,1,unlock')->name('unlock');
    Route::get('/don/{code}', 'orderStatus')->middleware('throttle:60,1,order-status')->name('order-status');
    Route::get('/trang-thai', 'generationStatus')->middleware('throttle:60,1,generation-status')->name('generation-status');
});

Route::get('/ket-qua/{analysis}/luan-giai', AnalysisStreamController::class)->middleware('throttle:10,1,analysis-stream')->name('analyses.stream');

Route::post('/webhooks/sepay', SepayWebhookController::class)->name('webhooks.sepay');

Route::view('/design-system', 'pages.design-system')->name('design-system');

Route::controller(PostController::class)->prefix('bai-viet')->name('posts.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/chuyen-muc/{category}', 'category')->name('category');
    Route::get('/{post}', 'show')->name('show');
});

Route::controller(ProductController::class)->prefix('san-pham')->name('products.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{product}', 'show')->name('show');
});

Route::controller(CartController::class)->prefix('gio-hang')->name('cart.')->group(function () {
    Route::get('/', 'show')->name('show');
    Route::post('/', 'store')->middleware('throttle:60,1,cart-store')->name('store');
    Route::patch('/{line}', 'update')->where('line', '[a-f0-9]{32}')->name('update');
    Route::delete('/{line}', 'destroy')->where('line', '[a-f0-9]{32}')->name('destroy');
});

Route::controller(CheckoutController::class)->prefix('thanh-toan')->name('checkout.')->group(function () {
    Route::get('/', 'show')->name('show');
    Route::post('/', 'store')->middleware('throttle:10,1,checkout-store')->name('store');
});

Route::controller(OrderController::class)->prefix('don-hang/{order}')->name('orders.')->group(function () {
    Route::get('/', 'show')->name('show');
    Route::get('/trang-thai', 'paymentStatus')->middleware('throttle:60,1,product-order-status')->name('payment-status');
});

Route::get('/chuyen-gia/{expert}', [ExpertController::class, 'show'])->name('experts.show');

Route::middleware('guest:customer')->controller(CustomerAuthController::class)->group(function () {
    Route::get('/dang-nhap', 'showLogin')->name('login');
    Route::post('/dang-nhap', 'login')->middleware('throttle:10,1,customer-login');
    Route::get('/dang-ky', 'showRegister')->name('register');
    Route::post('/dang-ky', 'register')->middleware('throttle:10,1,customer-register');
});

Route::middleware('guest:customer')->controller(GoogleAuthController::class)->prefix('dang-nhap/google')->name('auth.google')->group(function () {
    Route::get('/', 'redirect')->middleware('throttle:20,1,google-redirect')->name('');
    Route::get('/callback', 'callback')->middleware('throttle:20,1,google-callback')->name('.callback');
});

Route::middleware('guest:customer')->controller(CustomerPasswordResetController::class)->group(function () {
    Route::get('/quen-mat-khau', 'showForgot')->name('password.request');
    Route::post('/quen-mat-khau', 'sendLink')->middleware('throttle:5,1,password-email')->name('password.email');
    Route::get('/dat-lai-mat-khau/{token}', 'showReset')->name('password.reset');
    Route::post('/dat-lai-mat-khau', 'reset')->middleware('throttle:10,1,password-reset')->name('password.update');
});

Route::post('/dang-xuat', [CustomerAuthController::class, 'logout'])->middleware('auth:customer')->name('logout');

Route::middleware('auth:customer')->controller(AccountController::class)->prefix('tai-khoan')->name('account.')->group(function () {
    Route::get('/', 'show')->name('show');
    Route::get('/thong-tin', 'edit')->name('edit');
    Route::put('/thong-tin', 'update')->middleware('throttle:20,1,account-update')->name('update');
    Route::get('/mat-khau', 'editPassword')->name('password.edit');
    Route::put('/mat-khau', 'updatePassword')->middleware('throttle:6,1,account-password')->name('password.update');
});

Route::get('/affiliate', [AffiliateController::class, 'landing'])->name('affiliate.landing');
Route::get('/affiliate/dang-ky', [AffiliateController::class, 'register'])->middleware('guest:customer')->name('affiliate.register');

Route::middleware('auth:customer')->controller(AffiliateController::class)->prefix('tai-khoan/affiliate')->name('account.affiliate.')->group(function () {
    Route::get('/', 'show')->name('show');
    Route::post('/', 'store')->middleware('throttle:10,1,affiliate-join')->name('store');
    Route::put('/', 'update')->middleware('throttle:20,1,affiliate-bank')->name('update');
});

// Đặt cuối cùng: mọi đường dẫn một cấp còn lại là trang tĩnh (điều khoản, bảo mật, liên hệ…).
Route::get('/{page}', [PageController::class, 'show'])
    ->where('page', '(?!admin$|up$)[a-z0-9]+(?:-[a-z0-9]+)*')
    ->name('pages.show');
