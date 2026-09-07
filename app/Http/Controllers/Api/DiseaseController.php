<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DiseaseResource;
use App\Models\Disease;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DiseaseController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return DiseaseResource::collection(
            Disease::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
        )->additional([
            'success' => true,
        ]);
    }

    public function show(Disease $disease): JsonResponse
    {
        abort_if(! $disease->is_active, 404);

        return response()->json([
            'success' => true,
            'data' => [
                'disease' => new DiseaseResource($disease),
            ],
        ]);
    }
}
