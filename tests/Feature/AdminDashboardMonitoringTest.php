<?php

namespace Tests\Feature;

use App\Models\Detection;
use App\Models\Disease;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminDashboardMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_stays_open_when_fastapi_is_offline(): void
    {
        Http::fake([
            '*/health' => Http::response(['detail' => 'down'], 500),
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('AI Service Offline');
    }

    public function test_invalid_detection_is_not_counted_as_top_disease(): void
    {
        Http::fake([
            '*/health' => Http::response(['status' => 'ok'], 200),
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $disease = Disease::create([
            'ai_class_name' => 'Curl Virus',
            'slug' => 'virus-daun-keriting',
            'name' => 'Virus Daun Keriting',
            'is_active' => true,
        ]);

        Detection::create([
            'user_id' => $user->id,
            'disease_id' => $disease->id,
            'image_path' => 'detections/valid.jpg',
            'ai_class_id' => 2,
            'ai_class_name' => 'Curl Virus',
            'confidence' => 0.904637,
            'confidence_percent' => 90.46,
            'needs_retake' => false,
            'valid_input' => true,
            'top_predictions' => [],
            'raw_ai_response' => [],
            'status' => 'success',
        ]);

        Detection::create([
            'user_id' => $user->id,
            'disease_id' => $disease->id,
            'image_path' => 'detections/invalid.jpg',
            'ai_class_id' => 2,
            'ai_class_name' => 'Curl Virus',
            'confidence' => 0.904637,
            'confidence_percent' => 90.46,
            'needs_retake' => false,
            'valid_input' => false,
            'top_predictions' => [],
            'raw_ai_response' => [],
            'status' => 'success',
        ]);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertOk();

        $topDisease = $response->viewData('topDiseases')->firstWhere('id', $disease->id);

        $this->assertNotNull($topDisease);
        $this->assertSame(1, $topDisease->detections_count);
    }

    public function test_admin_monitoring_pages_render(): void
    {
        Http::fake([
            '*/health' => Http::response(['status' => 'ok'], 200),
            '*/model-info' => Http::response(['models' => []], 200),
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $disease = Disease::create([
            'ai_class_name' => 'Curl Virus',
            'slug' => 'virus-daun-keriting',
            'name' => 'Virus Daun Keriting',
            'is_active' => true,
        ]);
        $detection = Detection::create([
            'user_id' => $user->id,
            'disease_id' => $disease->id,
            'image_path' => 'detections/render.jpg',
            'ai_class_id' => 2,
            'ai_class_name' => 'Curl Virus',
            'confidence' => 0.654321,
            'confidence_percent' => 65.43,
            'needs_retake' => false,
            'valid_input' => true,
            'classification_source' => 'classifier',
            'top_predictions' => [
                ['class_name' => 'Curl Virus', 'confidence' => 0.654321],
            ],
            'raw_ai_response' => [
                'classification_source' => 'classifier',
                'validator' => [
                    'method' => 'CLIP',
                    'positive_score' => 0.91,
                    'best_label' => 'cabai leaf',
                    'threshold' => 0.75,
                ],
                'object_detection' => [
                    'class_name' => 'leaf',
                    'confidence' => 0.88,
                    'bbox' => [10, 20, 110, 140],
                    'image_width' => 200,
                    'image_height' => 200,
                ],
            ],
            'status' => 'success',
        ]);

        foreach ([
            '/admin/users',
            '/admin/users/'.$user->id,
            '/admin/users/'.$user->id.'/edit',
            '/admin/detections',
            '/admin/reviews',
            '/admin/detections/'.$detection->id,
            '/admin/reports',
            '/admin/audit-logs',
            '/admin/ai-service',
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }
}
