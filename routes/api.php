<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/cities', [DirectoryController::class, 'cities']);
Route::get('/categories', [DirectoryController::class, 'categories']);

Route::get('/masters', [MasterController::class, 'index']);
Route::get('/masters/{id}', [MasterController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/profile/update', [ProfileController::class, 'update']);
    Route::post('/profile/status', [ProfileController::class, 'status']);
    Route::post('/profile/portfolio', [ProfileController::class, 'portfolio']);
});
