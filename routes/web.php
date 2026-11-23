<?php

use App\Http\Controllers\EmailVerificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/email/verify/{id}/{hash}', EmailVerificationController::class)
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::get('/reset-password/{token}', function (Request $request, string $token) {
    return redirect('/reset-password?token=' . urlencode($token) . '&email=' . urlencode((string) $request->query('email')));
})->name('password.reset');

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
