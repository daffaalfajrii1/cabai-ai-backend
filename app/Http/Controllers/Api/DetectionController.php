<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\AiServiceUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreDetectionRequest;
use App\Http\Resources\DetectionResource;
use App\Models\Detection;
use App\Models\Disease;
use App\Services\AiPredictionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DetectionController extends Controller
{
    public function __construct(private readonly AiPredictionService $aiPredictionService)
    {
    }

    public function store(StoreDetectionRequest $request): JsonResponse
    {
        $image = $request->file('image');
        $directory = 'detections/'.now()->format('Y/m');
        $filename = (string) Str::uuid().'.'.$image->extension();
        $path = $image->storeAs($directory, $filename, 'public');

        try {
            $aiResponse = $this->aiPredictionService->predict($image);
        } catch (AiServiceUnavailableException) {
            Storage::disk('public')->delete($path);

            return response()->json([
                'success' => false,
                'message' => 'Layanan AI sedang tidak tersedia. Silakan coba kembali.',
            ], 503);
        }

        $prediction = $aiResponse['prediction'] ?? [];
        $className = $prediction['class_name'] ?? null;
        $disease = $className ? Disease::where('ai_class_name', $className)->first() : null;

        if ($className && ! $disease) {
            Log::warning('AI class name was not mapped to a disease.', [
                'ai_class_name' => $className,
            ]);
        }

        $needsRetake = (bool) ($aiResponse['needs_retake'] ?? false);
        $message = $needsRetake
            ? 'Confidence terlalu rendah. Silakan ambil ulang foto.'
            : 'Deteksi berhasil.';

        $detection = Detection::create([
            'user_id' => $request->user()->id,
            'disease_id' => $disease?->id,
            'image_path' => $path,
            'ai_class_id' => $prediction['class_id'] ?? null,
            'ai_class_name' => $className,
            'confidence' => $prediction['confidence'] ?? null,
            'confidence_percent' => $prediction['confidence_percent'] ?? null,
            'needs_retake' => $needsRetake,
            'message' => $message,
            'top_predictions' => $aiResponse['top_predictions'] ?? [],
            'raw_ai_response' => $aiResponse,
            'status' => 'success',
        ])->load(['disease', 'user']);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'detection' => new DetectionResource($detection),
            ],
        ], 201);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Detection::class);

        $query = Detection::query()
            ->with(['disease', 'user'])
            ->latest();

        if (! $request->user()->isAdmin()) {
            $query->where('user_id', $request->user()->id);
        }

        return DetectionResource::collection(
            $query->paginate($request->integer('per_page', 15))
        )->additional([
            'success' => true,
        ]);
    }

    public function show(Detection $detection): JsonResponse
    {
        $this->authorize('view', $detection);

        return response()->json([
            'success' => true,
            'data' => [
                'detection' => new DetectionResource($detection->load(['disease', 'user'])),
            ],
        ]);
    }
}
