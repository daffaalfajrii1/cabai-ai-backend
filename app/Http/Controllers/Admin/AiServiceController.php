<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Disease;
use App\Services\AiPredictionService;
use Illuminate\View\View;

class AiServiceController extends Controller
{
    public function __invoke(AiPredictionService $aiPredictionService): View
    {
        return view('admin.ai_service.index', [
            'aiHealth' => $aiPredictionService->health(),
            'modelInfo' => $aiPredictionService->modelInfo(),
            'classesCount' => Disease::where('is_active', true)->count(),
        ]);
    }
}
