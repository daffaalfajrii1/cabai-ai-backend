<?php

namespace Tests\Feature;

use App\Models\Detection;
use App\Models\Disease;
use App\Models\User;
use Database\Seeders\DiseaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DetectionApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed(DiseaseSeeder::class);
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_valid_prediction_saved(): void
    {
        Http::fake([
            '*/predict' => Http::response($this->aiPayload(), 200),
        ]);

        $response = $this->post('/api/detections', [
            'image' => UploadedFile::fake()->image('leaf.jpg', 600, 400),
        ], ['Accept' => 'application/json']);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.detection.prediction.class_name', 'Curl Virus')
            ->assertJsonPath('data.detection.disease.slug', 'virus-daun-keriting')
            ->assertJsonPath('data.detection.needs_retake', false)
            ->assertJsonPath('data.detection.valid_input', true);

        $detection = Detection::firstOrFail();

        $this->assertSame($this->user->id, $detection->user_id);
        $this->assertSame('Curl Virus', $detection->ai_class_name);
        $this->assertNotNull($detection->disease_id);
        Storage::disk('public')->assertExists($detection->image_path);
    }

    public function test_invalid_content_business_response_is_not_service_unavailable(): void
    {
        $message = 'Foto tidak sesuai. Silakan masukkan foto tanaman atau daun cabai.';

        Http::fake([
            '*/predict' => Http::response([
                'success' => false,
                'valid_input' => false,
                'needs_retake' => true,
                'reason' => 'invalid_content',
                'message' => $message,
                'prediction' => null,
                'top_predictions' => [],
            ], 200),
        ]);

        $this->post('/api/detections', [
            'image' => UploadedFile::fake()->image('person.jpg', 600, 400),
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', $message)
            ->assertJsonPath('data.detection.valid_input', false)
            ->assertJsonPath('data.detection.needs_retake', true)
            ->assertJsonPath('data.detection.reason', 'invalid_content')
            ->assertJsonPath('data.detection.prediction', null);

        $this->assertDatabaseHas('detections', [
            'valid_input' => false,
            'needs_retake' => true,
            'disease_id' => null,
            'ai_class_name' => null,
            'message' => $message,
            'review_status' => Detection::REVIEW_PENDING,
        ]);

        $detection = Detection::firstOrFail();
        $this->assertNull($detection->confidence);
        $this->assertSame('invalid_content', $detection->raw_ai_response['reason'] ?? null);
        Storage::disk('public')->assertExists($detection->image_path);
    }

    public function test_needs_retake_business_response_is_not_service_unavailable(): void
    {
        $message = 'Hasil kurang jelas. Silakan ambil ulang foto daun cabai.';

        Http::fake([
            '*/predict' => Http::response([
                'success' => false,
                'valid_input' => true,
                'needs_retake' => true,
                'reason' => 'needs_retake',
                'message' => $message,
                'prediction' => null,
                'top_predictions' => [],
            ], 200),
        ]);

        $this->post('/api/detections', [
            'image' => UploadedFile::fake()->image('leaf.jpg', 600, 400),
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', $message)
            ->assertJsonPath('data.detection.valid_input', true)
            ->assertJsonPath('data.detection.needs_retake', true)
            ->assertJsonPath('data.detection.prediction', null);

        $this->assertDatabaseHas('detections', [
            'valid_input' => true,
            'needs_retake' => true,
            'disease_id' => null,
            'ai_class_name' => null,
            'message' => $message,
            'review_status' => Detection::REVIEW_PENDING,
        ]);
    }

    public function test_connection_exception_returns_service_unavailable(): void
    {
        Http::fake(function () {
            throw new ConnectionException('Connection refused');
        });

        $this->post('/api/detections', [
            'image' => UploadedFile::fake()->image('leaf.jpg', 600, 400),
        ], ['Accept' => 'application/json'])
            ->assertStatus(503)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Layanan AI sedang tidak tersedia. Silakan coba kembali.');

        $this->assertDatabaseCount('detections', 0);
        $this->assertCount(0, Storage::disk('public')->allFiles('detections'));
    }

    public function test_fastapi_failure_returns_service_unavailable(): void
    {
        Http::fake([
            '*/predict' => Http::response(['detail' => 'down'], 500),
        ]);

        $this->post('/api/detections', [
            'image' => UploadedFile::fake()->image('leaf.jpg', 600, 400),
        ], ['Accept' => 'application/json'])
            ->assertStatus(503)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Layanan AI sedang tidak tersedia. Silakan coba kembali.');

        $this->assertDatabaseCount('detections', 0);
        $this->assertCount(0, Storage::disk('public')->allFiles('detections'));
    }

    public function test_low_confidence_success_keeps_prediction_and_pending_review(): void
    {
        Http::fake([
            '*/predict' => Http::response($this->aiPayload(
                needsRetake: false,
                className: 'Curl Virus',
                confidence: 0.65,
                confidencePercent: 65.0,
            ), 200),
        ]);

        $this->post('/api/detections', [
            'image' => UploadedFile::fake()->image('leaf.jpg', 600, 400),
        ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.detection.needs_retake', false)
            ->assertJsonPath('data.detection.prediction.class_name', 'Curl Virus')
            ->assertJsonPath('data.detection.prediction.confidence', 0.65)
            ->assertJsonPath('data.detection.review.status', Detection::REVIEW_PENDING);

        $detection = Detection::firstOrFail();

        $this->assertFalse($detection->needs_retake);
        $this->assertSame('Curl Virus', $detection->ai_class_name);
        $this->assertNotNull($detection->disease_id);
        $this->assertSame(Detection::REVIEW_PENDING, $detection->review_status);
        $this->assertTrue($detection->isLowConfidence());
        $this->assertContains('Confidence < 70%', $detection->reviewReasons());
    }

    public function test_unknown_ai_class_name_is_saved_without_disease(): void
    {
        Http::fake([
            '*/predict' => Http::response($this->aiPayload(className: 'Unmapped Class'), 200),
        ]);

        $this->post('/api/detections', [
            'image' => UploadedFile::fake()->image('leaf.jpg', 600, 400),
        ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.detection.prediction.class_name', 'Unmapped Class')
            ->assertJsonPath('data.detection.disease', null);

        $this->assertDatabaseHas('detections', [
            'ai_class_name' => 'Unmapped Class',
            'disease_id' => null,
        ]);
    }

    public function test_invalid_image_is_rejected(): void
    {
        Http::fake();

        $this->post('/api/detections', [
            'image' => UploadedFile::fake()->create('leaf.txt', 1, 'text/plain'),
        ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['image']);

        $this->assertDatabaseCount('detections', 0);
    }

    public function test_user_cannot_view_other_users_detection(): void
    {
        $otherUser = User::factory()->create();
        $disease = Disease::where('ai_class_name', 'Curl Virus')->firstOrFail();
        $detection = Detection::create([
            'user_id' => $otherUser->id,
            'disease_id' => $disease->id,
            'image_path' => 'detections/test.jpg',
            'ai_class_id' => 2,
            'ai_class_name' => 'Curl Virus',
            'confidence' => 0.904637,
            'confidence_percent' => 90.46,
            'needs_retake' => false,
            'top_predictions' => [],
            'raw_ai_response' => [],
            'status' => 'success',
        ]);

        $this->getJson('/api/detections/'.$detection->id)
            ->assertForbidden();
    }

    private function aiPayload(
        bool $needsRetake = false,
        string $className = 'Curl Virus',
        float $confidence = 0.904637,
        float $confidencePercent = 90.46,
        bool $success = true,
        bool $validInput = true,
    ): array {
        return [
            'success' => $success,
            'valid_input' => $validInput,
            'needs_retake' => $needsRetake,
            'prediction' => [
                'class_id' => 2,
                'class_name' => $className,
                'confidence' => $confidence,
                'confidence_percent' => $confidencePercent,
            ],
            'top_predictions' => [
                [
                    'class_id' => 2,
                    'class_name' => $className,
                    'confidence' => $confidence,
                ],
            ],
        ];
    }
}
