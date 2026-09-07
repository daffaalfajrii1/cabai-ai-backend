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

        if ($handle === false) {
            Log::warning('AI prediction aborted because uploaded image could not be opened.');

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
                ->post($this->endpoint('/predict'));
        } catch (ConnectionException $exception) {
            Log::warning('AI prediction service connection failed.', [
                'message' => $exception->getMessage(),
            ]);

            throw new AiServiceUnavailableException('AI service connection failed.');
        } catch (Throwable $exception) {
            Log::warning('AI prediction service request failed.', [
                'message' => $exception->getMessage(),
            ]);

            throw new AiServiceUnavailableException('AI service request failed.');
        } finally {
            fclose($handle);
        }

        if (! $response->successful()) {
            Log::warning('AI prediction service returned an error status.', [
                'status' => $response->status(),
                'body' => mb_substr($response->body(), 0, 1000),
            ]);

            throw new AiServiceUnavailableException('AI service returned an error status.');
        }

        $payload = $response->json();

        if (! $this->isValidPredictionPayload($payload)) {
            Log::warning('AI prediction service returned an invalid payload.', [
                'status' => $response->status(),
            ]);

            throw new AiServiceUnavailableException('AI service returned an invalid payload.');
        }

        return $payload;
    }

    public function health(): array
    {
        try {
            $response = Http::timeout(5)->acceptJson()->get($this->endpoint('/health'));
        } catch (Throwable $exception) {
            Log::info('AI health check failed.', [
                'message' => $exception->getMessage(),
            ]);

            return [
                'online' => false,
                'data' => null,
            ];
        }

        return [
            'online' => $response->successful(),
            'data' => $response->json(),
            'status' => $response->status(),
        ];
    }

    public function modelInfo(): array
    {
        try {
            $response = Http::timeout(5)->acceptJson()->get($this->endpoint('/model-info'));
        } catch (Throwable $exception) {
            Log::info('AI model info check failed.', [
                'message' => $exception->getMessage(),
            ]);

            return [
                'online' => false,
                'data' => null,
            ];
        }

        return [
            'online' => $response->successful(),
            'data' => $response->json(),
            'status' => $response->status(),
        ];
    }

    private function isValidPredictionPayload(mixed $payload): bool
    {
        if (! is_array($payload)) {
            return false;
        }

        if (($payload['success'] ?? null) !== true) {
            return false;
        }

        if (! array_key_exists('needs_retake', $payload) || ! is_bool($payload['needs_retake'])) {
            return false;
        }

        if (! isset($payload['prediction']) || ! is_array($payload['prediction'])) {
            return false;
        }

        if (! array_key_exists('top_predictions', $payload) || ! is_array($payload['top_predictions'])) {
            return false;
        }

        $prediction = $payload['prediction'];

        return isset($prediction['class_name'])
            && is_string($prediction['class_name'])
            && array_key_exists('class_id', $prediction);
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
