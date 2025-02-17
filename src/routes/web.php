<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/item/{id}', [ItemController::class, 'show']);

Route::post('/addlike/{itemId}', [ItemController::class, 'addlike'])->name('addlike');

Route::post('/addcomment/{itemId}', [ItemController::class, 'addcomment'])->name('addcomment');

Route::get('/purchase/{itemId}', [PurchaseController::class, 'purchase'])->name('purchase');

Route::post('/checkout', [PurchaseController::class, 'checkout'])->name('checkout');

Route::get('/checkout/success', [PurchaseController::class, 'success'])->name('checkout.success');
Route::get('/checkout/cancel', [PurchaseController::class, 'cancel'])->name('checkout.cancel');

Route::get('/address/{itemId}', [UserController::class, 'address'])->name('address');

Route::post('/purchase/address/{itemId}', [UserController::class, 'addressEdit'])->name('addressEdit');

Route::get('/profile', [UserController::class, 'profile'])->name('profile');