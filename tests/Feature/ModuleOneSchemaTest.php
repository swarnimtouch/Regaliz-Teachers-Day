<?php

namespace Tests\Feature;

use App\Models\DoctorReel;
use App\Models\ReelStatusHistory;
use App\Models\ReelTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleOneSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_reel_relations_and_soft_deletion_are_persisted(): void
    {
        $template = ReelTemplate::query()->create([
            'name' => 'Classic Gold',
            'slug' => 'classic-gold',
            'background_file' => 'templates/classic-gold/background.png',
            'configuration' => ['video' => ['x' => 140, 'y' => 260, 'width' => 800, 'height' => 800]],
            'is_default' => true,
        ]);

        $reel = DoctorReel::query()->create([
            'reference_id' => 'TDR-TEST-0001',
            'doctor_name' => 'Dr Test',
            'speciality' => 'Medicine',
            'city' => 'Mumbai',
            'consent' => true,
            'template_id' => $template->id,
        ]);

        ReelStatusHistory::query()->create([
            'doctor_reel_id' => $reel->id,
            'status' => 'pending',
        ]);

        $this->assertTrue($reel->fresh()->template->is($template));
        $this->assertCount(1, $reel->statusHistories);

        $reel->delete();
        $this->assertSoftDeleted($reel);
    }

    public function test_health_route_reports_the_application_is_available(): void
    {
        $this->getJson(route('health'))
            ->assertOk()
            ->assertJsonPath('status', 'ok');
    }
}
