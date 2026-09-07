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
        return view('admin.dashboard', [
            'totalUsers' => User::count(),
            'totalDetections' => Detection::count(),
            'detectionsToday' => Detection::whereDate('created_at', today())->count(),
            'needsRetake' => Detection::where('needs_retake', true)->count(),
            'latestDetections' => Detection::with(['user', 'disease'])->latest()->limit(8)->get(),
            'topDiseases' => Disease::withCount('detections')
                ->orderByDesc('detections_count')
                ->limit(5)
                ->get(),
            'aiHealth' => $aiPredictionService->health(),
        ]);
    }
}
