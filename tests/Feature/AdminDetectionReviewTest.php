<?php

namespace Tests\Feature;

use App\Models\Detection;
use App\Models\Disease;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDetectionReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_verify_detection(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $detection = $this->detection();

        $this->actingAs($admin)
            ->patch(route('admin.detections.review.update', $detection), [
                'review_status' => Detection::REVIEW_VERIFIED,
                'review_notes' => 'AI sesuai hasil inspeksi.',
            ])
            ->assertRedirect(route('admin.detections.show', $detection));

        $detection->refresh();

        $this->assertSame(Detection::REVIEW_VERIFIED, $detection->review_status);
        $this->assertSame($admin->id, $detection->reviewed_by);
        $this->assertNotNull($detection->reviewed_at);
        $this->assertNull($detection->corrected_disease_id);
    }

    public function test_admin_can_correct_detection_without_overwriting_original_ai_result(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $originalDisease = $this->disease('Curl Virus', 'virus-daun-keriting', 'Virus Daun Keriting');
        $correctedDisease = $this->disease('Bacterial Spot', 'bacterial-spot', 'Bacterial Spot');
        $detection = $this->detection($originalDisease);

        $this->actingAs($admin)
            ->patch(route('admin.detections.review.update', $detection), [
                'review_status' => Detection::REVIEW_CORRECTED,
                'corrected_disease_id' => $correctedDisease->id,
                'review_notes' => 'Lesi lebih cocok ke bacterial spot.',
            ])
            ->assertRedirect(route('admin.detections.show', $detection));

        $detection->refresh();

        $this->assertSame(Detection::REVIEW_CORRECTED, $detection->review_status);
        $this->assertSame($correctedDisease->id, $detection->corrected_disease_id);
        $this->assertSame('Curl Virus', $detection->ai_class_name);
        $this->assertSame($originalDisease->id, $detection->disease_id);
    }

    private function detection(?Disease $disease = null): Detection
    {
        $disease ??= $this->disease('Curl Virus', 'virus-daun-keriting', 'Virus Daun Keriting');

        return Detection::create([
            'user_id' => User::factory()->create()->id,
            'disease_id' => $disease->id,
            'image_path' => 'detections/test.jpg',
            'ai_class_id' => 2,
            'ai_class_name' => 'Curl Virus',
            'confidence' => 0.904637,
            'confidence_percent' => 90.46,
            'needs_retake' => false,
            'valid_input' => true,
            'classification_source' => 'classifier',
            'top_predictions' => [],
            'raw_ai_response' => [],
            'status' => 'success',
        ]);
    }

    private function disease(string $aiClassName, string $slug, string $name): Disease
    {
        return Disease::firstOrCreate(
            ['ai_class_name' => $aiClassName],
            [
                'slug' => $slug,
                'name' => $name,
                'is_active' => true,
            ]
        );
    }
}
