<?php

use App\Http\Controllers\EmailVerificationController;
use Illuminate\Support\Facades\Route;

Route::get('/email/verify/{id}/{hash}', EmailVerificationController::class)
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
