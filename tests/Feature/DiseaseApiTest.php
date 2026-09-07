<?php

namespace Tests\Feature;

use App\Models\Disease;
use App\Models\User;
use Database\Seeders\DiseaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DiseaseApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DiseaseSeeder::class);
        Sanctum::actingAs(User::factory()->create());
    }

    public function test_list_diseases(): void
    {
        $response = $this->getJson('/api/diseases');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(6, 'data');
    }

    public function test_show_disease_by_slug(): void
    {
        $disease = Disease::where('ai_class_name', 'Curl Virus')->firstOrFail();

        $this->getJson('/api/diseases/'.$disease->slug)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.disease.ai_class_name', 'Curl Virus')
            ->assertJsonPath('data.disease.name', 'Virus Daun Keriting');
    }
}
