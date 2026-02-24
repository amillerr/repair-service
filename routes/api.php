<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\RequestController;
use App\Http\Controllers\Api\MasterRequestController;

// Публичные
Route::get('/health', [HealthController::class, 'index']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

// Требуют авторизации (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me'])->name('me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::post('/requests', [RequestController::class, 'store'])->name('requests.store');

    // Пример: только мастер может взять заявку в работу
    Route::post('/requests/{request}/take', [RequestController::class, 'takeInWork'])
        ->middleware('role:master')
        ->name('requests.take');

    Route::prefix('master')->middleware('role:master')->group(function () {
        Route::get('/requests', [MasterRequestController::class, 'index'])
            ->name('master.requests.index');
        Route::post('/requests/{request}/take', [MasterRequestController::class, 'take'])
            ->name('master.requests.take');
        Route::post('/requests/{request}/complete', [MasterRequestController::class, 'complete'])
            ->name('master.requests.complete');
    });
});
