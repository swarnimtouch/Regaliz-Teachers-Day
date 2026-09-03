<?php

namespace Tests\Feature;

use App\Models\DoctorReel;
use App\Models\Doctor;
use App\Models\AudioMessage;
use App\Models\GreetingCard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_modules_and_excel_export_are_accessible_to_an_admin(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin', 'status' => true]);
        $doctor = Doctor::create(['name' => 'Dr Admin Test', 'speciality' => 'Medicine', 'city' => 'Mumbai']);
        $reel = DoctorReel::create(['doctor_id' => $doctor->id, 'reference_id' => 'TDR-ADMIN-TEST', 'consent' => true, 'content_type' => 'video', 'original_video' => 'original/video/test.mp4']);
        $audioReel = DoctorReel::create(['doctor_id' => $doctor->id, 'reference_id' => 'TDR-ADMIN-AUDIO', 'consent' => true, 'content_type' => 'audio', 'teacher_name' => 'Should Not Appear', 'card_message' => 'Card-only message']);
        $cardReel = DoctorReel::create(['doctor_id' => $doctor->id, 'reference_id' => 'TDR-ADMIN-CARD', 'consent' => true, 'content_type' => 'card']);
        AudioMessage::create(['doctor_reel_id' => $audioReel->id, 'original_audio' => 'original/audio/test.mp3']);
        GreetingCard::create(['doctor_reel_id' => $cardReel->id, 'teacher_name' => 'Teacher', 'message' => 'Thank you']);

        $this->actingAs($admin);
        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Total Doctors')
            ->assertSee('Total Videos')
            ->assertSee('Total Audios')
            ->assertSee('Total Cards')
            ->assertViewHas('stats', fn (array $stats) => $stats === ['doctors' => 1, 'videos' => 1, 'audios' => 1, 'cards' => 1])
            ->assertDontSee('Last 7 days')
            ->assertDontSee('Content mix')
            ->assertDontSee('Reports');
        $this->get(route('admin.videos.index'))->assertOk()->assertSee('Video recordings');
        $this->get(route('admin.audios.index'))->assertOk()->assertSee('Dr Admin Test');
        $this->get(route('admin.cards.index'))->assertOk()->assertSee('TDR-ADMIN-CARD')->assertDontSee('TDR-ADMIN-AUDIO');
        $this->get(route('admin.doctors.index'))
            ->assertOk()
            ->assertSee('Dr Admin Test')
            ->assertDontSee('<th>Mobile</th>', false)
            ->assertDontSee('<th>Hospital</th>', false);
        $this->get(route('admin.doctors.index', ['search' => 'Medicine']))
            ->assertOk()
            ->assertSee('Dr Admin Test');
        $this->get(route('admin.doctors.index', ['search' => 'Not A Real Doctor']))
            ->assertOk()
            ->assertSee('No records found');
        $this->get(route('admin.doctors.show', $reel))->assertOk()->assertSee('TDR-ADMIN-TEST')->assertSee('Back');
        $this->get(route('admin.doctors.show', [$audioReel, 'media_type' => 'audio']))
            ->assertOk()
            ->assertDontSee('Should Not Appear')
            ->assertDontSee('Card-only message');
        $this->get(route('admin.settings.edit'))->assertOk();
        $this->get('/admin/reports')->assertNotFound();
        $this->get(route('admin.profile.edit'))->assertOk()->assertSee('Change password');

        $this->get(route('admin.doctors.export'))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_admin_can_store_campaign_logo_on_the_public_disk(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'super_admin', 'status' => true]);

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'campaign_title' => 'Teachers Day',
            'campaign_subtitle' => 'Thank your teacher',
            'campaign_quote' => '',
            'recording_min_seconds' => 5,
            'recording_max_seconds' => 30,
            'upload_max_mb' => 25,
            'campaign_active' => '1',
            'campaign_logo' => UploadedFile::fake()->image('logo.png'),
        ])->assertRedirect();

        $logo = \App\Models\Setting::where('key', 'campaign_logo')->value('value');
        $this->assertStringStartsWith('storage/branding/campaign-logo-', $logo);
        Storage::disk('public')->assertExists(substr($logo, strlen('storage/')));
    }
}
