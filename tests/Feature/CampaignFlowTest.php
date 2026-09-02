<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\DoctorReel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CampaignFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_details_redirect_to_format_selection_then_video_recording(): void
    {
        $this->get('/create')->assertNotFound();
        $this->post('/create')->assertNotFound();

        $this->get(route('campaign.landing'))
            ->assertOk()
            ->assertSee('Enter your full name')
            ->assertDontSee('name="mobile"', false)
            ->assertDontSee('name="hospital_name"', false);

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

    public function test_reel_preview_supports_iphone_byte_range_requests_and_downloads(): void
    {
        Storage::fake('local');
        config(['filesystems.media' => 'local', 'filesystems.media_prefix' => 'Teachers-Day']);
        $doctor = Doctor::query()->create(['name' => 'Dr Aanya Sharma', 'speciality' => 'Cardiology', 'city' => 'Mumbai']);
        $reel = DoctorReel::query()->create([
            'doctor_id' => $doctor->id,
            'reference_id' => 'TDR-IPHONE',
            'consent' => true,
            'content_type' => 'video',
            'generated_video' => 'Teachers-Day/videos/TDR-IPHONE.mp4',
            'status' => 'completed',
        ]);
        Storage::disk('local')->put($reel->generated_video, '0123456789');

        $preview = $this->withSession(['campaign_reel_id' => $reel->id])
            ->withHeader('Range', 'bytes=2-5')
            ->get(route('campaign.preview-reel'));

        $preview->assertStatus(206)
            ->assertHeader('Accept-Ranges', 'bytes')
            ->assertHeader('Content-Range', 'bytes 2-5/10')
            ->assertHeader('Content-Length', '4');
        $this->assertSame('2345', $preview->streamedContent());

        $this->withSession(['campaign_reel_id' => $reel->id])
            ->get(route('campaign.download'))
            ->assertOk()
            ->assertDownload('dr-aanya-sharma-video-message.mp4');
    }

    public function test_card_is_rendered_on_the_server_with_the_selected_template(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD is required for card rendering.');
        }

        Storage::fake('local');
        config(['filesystems.media' => 'local', 'filesystems.media_prefix' => 'Teachers-Day']);
        $doctor = Doctor::query()->create(['name' => 'Dr Aanya Sharma', 'speciality' => 'Cardiology', 'city' => 'Mumbai']);
        $reel = DoctorReel::query()->create([
            'doctor_id' => $doctor->id,
            'reference_id' => 'TDR-CARD',
            'consent' => true,
            'content_type' => 'card',
            'status' => 'awaiting_recording',
        ]);

        $this->withSession(['campaign_reel_id' => $reel->id])->post(route('campaign.store-card'), [
            'teacher_name' => 'Prof. Mehta',
            'card_message' => 'Thank you for guiding me.',
            'card_template' => 'golden',
        ])->assertRedirect(route('campaign.result'));

        $reel->refresh();
        Storage::disk('local')->assertExists($reel->generated_card);
        $image = getimagesize(Storage::disk('local')->path($reel->generated_card));
        $this->assertSame([1080, 1620], [$image[0], $image[1]]);
    }
}
