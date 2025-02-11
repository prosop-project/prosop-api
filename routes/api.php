<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('auth.')->group(function() {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::prefix('users')->name('users.')->group(function () {
    Route::middleware('auth:api')->group(function () {
        Route::get('/profile', [UserController::class, 'profile'])->name('profile');
        Route::delete('/{user}', [UserController::class, 'delete'])->name('delete');
        Route::patch('/{user}', [UserController::class, 'update'])->name('update');
    });

    Route::get('/{user}', [UserController::class, 'show'])->name('show');
});
