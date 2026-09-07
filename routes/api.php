<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DetectionController;
use App\Http\Controllers\Api\DiseaseController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/diseases', [DiseaseController::class, 'index']);
    Route::get('/diseases/{disease:slug}', [DiseaseController::class, 'show']);

    Route::post('/detections', [DetectionController::class, 'store'])
        ->middleware('throttle:detections');
    Route::get('/detections', [DetectionController::class, 'index']);
    Route::get('/detections/{detection}', [DetectionController::class, 'show']);
});
