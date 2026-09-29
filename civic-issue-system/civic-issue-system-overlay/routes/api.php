<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ComplaintApiController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthApiController::class, 'register']);
Route::post('/login', [AuthApiController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthApiController::class, 'logout']);

    Route::get('/complaints', [ComplaintApiController::class, 'index']);
    Route::get('/complaints/{complaint}', [ComplaintApiController::class, 'show']);
    Route::post('/complaints', [ComplaintApiController::class, 'store'])->middleware('role:citizen');
    Route::patch('/complaints/{complaint}/status', [ComplaintApiController::class, 'updateStatus'])->middleware('role:staff,admin');

    Route::get('/analytics', [ComplaintApiController::class, 'analytics'])->middleware('role:admin');
});
