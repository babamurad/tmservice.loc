<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CityController as AdminCityController;
use App\Http\Controllers\Admin\MasterModerationController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('throttle:otp')->group(function () {
    Route::post('/auth/send-otp', [OtpController::class, 'send']);
    Route::post('/auth/verify-otp', [OtpController::class, 'verify']);
});

Route::get('/cities', [DirectoryController::class, 'cities']);
Route::get('/categories', [DirectoryController::class, 'categories']);

Route::middleware('throttle:public-read')->group(function () {
    Route::get('/masters', [MasterController::class, 'index']);
    Route::get('/masters/{id}', [MasterController::class, 'show']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile/update', [ProfileController::class, 'update']);
    Route::post('/profile/status', [ProfileController::class, 'status']);
    Route::post('/profile/portfolio', [ProfileController::class, 'portfolio']);
    Route::delete('/profile/portfolio/{id}', [ProfileController::class, 'deletePortfolio']);
    Route::post('/profile/qr', [ProfileController::class, 'generateQr']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/cities', [AdminCityController::class, 'index']);
    Route::post('/cities', [AdminCityController::class, 'store']);
    Route::put('/cities/{city}', [AdminCityController::class, 'update']);
    Route::delete('/cities/{city}', [AdminCityController::class, 'destroy']);

    Route::get('/categories', [AdminCategoryController::class, 'index']);
    Route::post('/categories', [AdminCategoryController::class, 'store']);
    Route::put('/categories/{category}', [AdminCategoryController::class, 'update']);
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy']);

    Route::get('/masters', [MasterModerationController::class, 'index']);
    Route::post('/masters/{master}/approve', [MasterModerationController::class, 'approve']);
    Route::post('/masters/{master}/reject', [MasterModerationController::class, 'reject']);

    Route::get('/users', [AdminUserController::class, 'index']);
});
