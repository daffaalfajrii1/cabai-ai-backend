<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Detection;
use App\Models\Disease;
use App\Models\User;
use App\Services\AiPredictionService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(AiPredictionService $aiPredictionService): View
    {
        $aiHealth = $aiPredictionService->health();

        return view('admin.dashboard', [
            'totalUsers' => User::count(),
            'totalDetections' => Detection::count(),
            'detectionsToday' => Detection::whereDate('created_at', today())->count(),
            'invalidInputs' => Detection::where('valid_input', false)->count(),
            'needsRetake' => Detection::where('needs_retake', true)->count(),
            'pendingReviews' => Detection::where(function ($query): void {
                $query->where('review_status', Detection::REVIEW_PENDING)->orWhereNull('review_status');
            })->count(),
            'correctedDetections' => Detection::where('review_status', Detection::REVIEW_CORRECTED)->count(),
            'averageConfidence' => Detection::whereNotNull('confidence_percent')->avg('confidence_percent'),
            'latestDetections' => Detection::with(['user', 'disease', 'correctedDisease'])->latest()->limit(8)->get(),
            'topDiseases' => Disease::withCount([
                'detections' => fn ($query) => $query->where('valid_input', true),
            ])
                ->whereHas('detections', fn ($query) => $query->where('valid_input', true))
                ->orderByDesc('detections_count')
                ->limit(5)
                ->get(),
            'reviewDetections' => Detection::needsAdminReview()
                ->with(['user', 'disease'])
                ->latest()
                ->limit(5)
                ->get(),
            'aiHealth' => $aiHealth,
            'aiEngine' => [
                'validator' => 'CLIP',
                'detector' => 'YOLOE',
                'classifier' => 'EfficientNet V4',
                'classes' => Disease::where('is_active', true)->count(),
            ],
        ]);
    }
}
