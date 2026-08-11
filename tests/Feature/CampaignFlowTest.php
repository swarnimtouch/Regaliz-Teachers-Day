<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_details_redirect_to_format_selection_then_video_recording(): void
    {
        $response = $this->post(route('campaign.store'), [
            'doctor_name' => 'Dr Aanya Sharma',
            'speciality' => 'Cardiology',
            'city' => 'Mumbai',
            'consent' => '1',
        ]);

        $response->assertRedirect(route('campaign.choose-format'));
        $this->assertStringEndsWith('/choose-format', $response->headers->get('Location'));
        $this->get(route('campaign.choose-format'))
            ->assertOk()
            ->assertSee('Video message')
            ->assertSee('Audio message');

        $this->post(route('campaign.select-format'), ['content_type' => 'video'])
            ->assertRedirect(route('campaign.record'));
    }
}
