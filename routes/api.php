<?php

use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;


Route::prefix('users')->name('users.')->group(function () {
    Route::get('/{user}', [UserController::class, 'show'])->name('show');
});
