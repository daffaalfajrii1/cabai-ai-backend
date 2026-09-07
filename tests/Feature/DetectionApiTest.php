<?php

namespace Tests\Feature;

use App\Models\Detection;
use App\Models\Disease;
use App\Models\User;
use Database\Seeders\DiseaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->assertJsonPath('data.detection.disease.slug', 'virus-daun-keriting');

        $detection = Detection::firstOrFail();

        $this->assertSame($this->user->id, $detection->user_id);
        $this->assertSame('Curl Virus', $detection->ai_class_name);
        $this->assertNotNull($detection->disease_id);
        Storage::disk('public')->assertExists($detection->image_path);
    }

    public function test_needs_retake_true_is_saved(): void
    {
        Http::fake([
            '*/predict' => Http::response($this->aiPayload(needsRetake: true), 200),
        ]);

        $this->post('/api/detections', [
            'image' => UploadedFile::fake()->image('leaf.jpg', 600, 400),
        ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('message', 'Confidence terlalu rendah. Silakan ambil ulang foto.')
            ->assertJsonPath('data.detection.needs_retake', true);

        $this->assertDatabaseHas('detections', [
            'needs_retake' => true,
        ]);
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

    private function aiPayload(bool $needsRetake = false, string $className = 'Curl Virus'): array
    {
        return [
            'success' => true,
            'needs_retake' => $needsRetake,
            'prediction' => [
                'class_id' => 2,
                'class_name' => $className,
                'confidence' => 0.904637,
                'confidence_percent' => 90.46,
            ],
            'top_predictions' => [
                [
                    'class_id' => 2,
                    'class_name' => $className,
                    'confidence' => 0.904637,
                ],
            ],
        ];
    }
}
