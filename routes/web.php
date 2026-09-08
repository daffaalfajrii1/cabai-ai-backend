<?php

use App\Http\Controllers\Admin\AiServiceController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DetectionController;
use App\Http\Controllers\Admin\DiseaseController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'active', 'admin'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('diseases', DiseaseController::class)->except(['destroy']);
        Route::resource('users', UserController::class)->only(['index', 'show', 'edit', 'update']);
        Route::get('detections', [DetectionController::class, 'index'])->name('detections.index');
        Route::get('detections/{detection}', [DetectionController::class, 'show'])->name('detections.show');
        Route::patch('detections/{detection}/review', [DetectionController::class, 'updateReview'])->name('detections.review.update');
        Route::get('reviews', [DetectionController::class, 'reviewQueue'])->name('reviews.index');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
        Route::get('ai-service', AiServiceController::class)->name('ai-service.index');
        Route::get('audit-logs', AuditLogController::class)->name('audit-logs.index');
    });
});
