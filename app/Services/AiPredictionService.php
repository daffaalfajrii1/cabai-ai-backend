<?php

namespace App\Services;

use App\Exceptions\AiServiceUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class AiPredictionService
{
    public function predict(UploadedFile $image): array
    {
        $handle = fopen($image->getRealPath(), 'r');
        $endpoint = $this->endpoint('/predict');

        if ($handle === false) {
            Log::warning('AI prediction aborted because uploaded image could not be opened.', [
                'ai_url' => $endpoint,
            ]);

            throw new AiServiceUnavailableException('Uploaded image could not be opened.');
        }

        try {
            $response = Http::timeout($this->timeout())
                ->acceptJson()
                ->attach(
                    'image',
                    $handle,
                    $image->getClientOriginalName() ?: $image->hashName(),
                    ['Content-Type' => $image->getMimeType() ?: 'application/octet-stream']
                )
                ->post($endpoint);
        } catch (ConnectionException $exception) {
            Log::warning('AI prediction service connection failed.', [
                'ai_url' => $endpoint,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            throw new AiServiceUnavailableException('AI service connection failed.');
        } catch (Throwable $exception) {
            Log::warning('AI prediction service request failed.', [
                'ai_url' => $endpoint,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            throw new AiServiceUnavailableException('AI service request failed.');
        } finally {
            fclose($handle);
        }

        $status = $response->status();

        if ($this->isUpstreamServerError($status)) {
            Log::warning('AI prediction service returned an upstream error status.', [
                'ai_url' => $endpoint,
                'http_status' => $status,
                'body' => mb_substr($response->body(), 0, 1000),
            ]);

            throw new AiServiceUnavailableException('AI service returned an error status.');
        }

        if (! $response->successful()) {
            Log::warning('AI prediction service returned an unexpected HTTP status.', [
                'ai_url' => $endpoint,
                'http_status' => $status,
                'body' => mb_substr($response->body(), 0, 1000),
            ]);

            throw new AiServiceUnavailableException('AI service returned an unexpected status.');
        }

        $payload = $response->json();

        if (! $this->isAcceptablePredictionPayload($payload)) {
            Log::warning('AI prediction service returned an invalid payload.', [
                'ai_url' => $endpoint,
                'http_status' => $status,
                'reason' => is_array($payload) ? ($payload['reason'] ?? null) : null,
            ]);

            throw new AiServiceUnavailableException('AI service returned an invalid payload.');
        }

        if (($payload['success'] ?? null) === false) {
            Log::info('AI prediction returned a business response.', [
                'ai_url' => $endpoint,
                'http_status' => $status,
                'reason' => $payload['reason'] ?? null,
                'valid_input' => $payload['valid_input'] ?? null,
                'needs_retake' => $payload['needs_retake'] ?? null,
            ]);
        }

        return $payload;
    }

    public function health(): array
    {
        $startedAt = microtime(true);
        $endpoint = $this->endpoint('/health');

        try {
            $response = Http::timeout(5)->acceptJson()->get($endpoint);
        } catch (Throwable $exception) {
            Log::info('AI health check failed.', [
                'ai_url' => $endpoint,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return [
                'online' => false,
                'data' => null,
                'checked_at' => now(),
                'response_time_ms' => round((microtime(true) - $startedAt) * 1000, 2),
            ];
        }

        return [
            'online' => $response->successful(),
            'data' => $response->json(),
            'status' => $response->status(),
            'checked_at' => now(),
            'response_time_ms' => round((microtime(true) - $startedAt) * 1000, 2),
        ];
    }

    public function modelInfo(): array
    {
        $startedAt = microtime(true);
        $endpoint = $this->endpoint('/model-info');

        try {
            $response = Http::timeout(5)->acceptJson()->get($endpoint);
        } catch (Throwable $exception) {
            Log::info('AI model info check failed.', [
                'ai_url' => $endpoint,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return [
                'online' => false,
                'data' => null,
                'checked_at' => now(),
                'response_time_ms' => round((microtime(true) - $startedAt) * 1000, 2),
            ];
        }

        return [
            'online' => $response->successful(),
            'data' => $response->json(),
            'status' => $response->status(),
            'checked_at' => now(),
            'response_time_ms' => round((microtime(true) - $startedAt) * 1000, 2),
        ];
    }

    /**
     * Accept both successful diagnoses and FastAPI business responses
     * (invalid_content / needs_retake). JSON field "success" is not an HTTP failure.
     */
    private function isAcceptablePredictionPayload(mixed $payload): bool
    {
        if (! is_array($payload)) {
            return false;
        }

        if (! array_key_exists('success', $payload) || ! is_bool($payload['success'])) {
            return false;
        }

        if (! array_key_exists('needs_retake', $payload) || ! is_bool($payload['needs_retake'])) {
            return false;
        }

        if (! array_key_exists('top_predictions', $payload) || ! is_array($payload['top_predictions'])) {
            return false;
        }

        if (! array_key_exists('prediction', $payload)) {
            return false;
        }

        $prediction = $payload['prediction'];

        if ($prediction === null) {
            return $payload['success'] === false;
        }

        if (! is_array($prediction)) {
            return false;
        }

        if ($payload['success'] === true) {
            return isset($prediction['class_name'])
                && is_string($prediction['class_name'])
                && array_key_exists('class_id', $prediction);
        }

        return true;
    }

    private function isUpstreamServerError(int $status): bool
    {
        return in_array($status, [500, 502, 503, 504], true);
    }

    private function endpoint(string $path): string
    {
        return rtrim((string) config('services.ai.url'), '/').$path;
    }

    private function timeout(): int
    {
        return max(1, (int) config('services.ai.timeout', 30));
    }
}
