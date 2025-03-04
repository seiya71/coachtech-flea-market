<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PurchaseController;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Http\Controllers\StripeWebhookController;
use Laravel\Fortify\Features;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [ItemController::class, 'index'])->name('home');

Route::post('/register', [UserController::class, 'register']);

Route::get('/profile_edit', [UserController::class, 'showEdit'])->name('profile_edit');

Route::post('/login', [UserController::class, 'login']);

Route::post('/logout', [UserController::class, 'logout'])->name('logout');

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', '確認メールを再送しました。');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::get('/item/{id}', [ItemController::class, 'show'])->name('items.show');

Route::post('/addlike/{itemId}', [ItemController::class, 'addlike'])->name('addlike');

Route::post('/addcomment/{itemId}', [ItemController::class, 'addcomment'])->name('addcomment');

Route::get('/purchase/{itemId}', [PurchaseController::class, 'purchase'])->name('purchase');

Route::post('/checkout/{itemId}', [PurchaseController::class, 'checkout'])->name('checkout');

Route::get('/checkout/success', [PurchaseController::class, 'success'])->name('checkout.success');

Route::get('/checkout/cancel', [PurchaseController::class, 'cancel'])->name('checkout.cancel');

Route::get('/address/{itemId}', [UserController::class, 'address'])->name('address');

Route::post('/purchase/address/{itemId}', [UserController::class, 'addressEdit'])->name('addressEdit');

Route::get('/profile', [UserController::class, 'profile'])->name('profile');

Route::get('/profile_edit', [UserController::class, 'showEdit'])->name('profile_edit');

Route::post('/profile_edit', [UserController::class, 'edit'])->name('edit');

Route::post('/profile_edit/image', [UserController::class, 'updateUserImage'])->name('updateUserImage');

Route::get('/sell', [ItemController::class, 'showSell'])->name('showSell');

Route::post('/sell', [ItemController::class, 'sell'])->name('sell');

Route::post('/upload-item-image', [ItemController::class, 'uploadItemImage'])->name('uploadItemImage');