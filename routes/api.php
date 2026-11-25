<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])->middleware('throttle:6,1');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:6,1');
Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])->middleware('throttle:6,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return new UserResource($request->user());
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/tickets', [TicketController::class, 'index']);
    Route::get('/tickets/{ticket}', [TicketController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/users', [UserController::class, 'index']);

    Route::middleware('throttle:60,1')->group(function () {
        Route::post('/tickets', [TicketController::class, 'store']);
        Route::put('/tickets/{ticket}', [TicketController::class, 'update']);
        Route::patch('/tickets/{ticket}/assignment', [TicketController::class, 'assign']);
        Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus']);

        Route::post('/tickets/{ticket}/replies', [ReplyController::class, 'store']);
        Route::put('/replies/{reply}', [ReplyController::class, 'update']);

        Route::post('/tickets/{ticket}/notes', [NoteController::class, 'store']);
        Route::put('/notes/{note}', [NoteController::class, 'update']);
        Route::delete('/notes/{note}', [NoteController::class, 'destroy']);

        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
    });
});
