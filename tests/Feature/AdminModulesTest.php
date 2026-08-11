<?php

namespace Tests\Feature;

use App\Models\DoctorReel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_modules_and_excel_export_are_accessible_to_an_admin(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin', 'status' => true]);
        $reel = DoctorReel::create(['reference_id' => 'TDR-ADMIN-TEST', 'doctor_name' => 'Dr Admin Test', 'speciality' => 'Medicine', 'city' => 'Mumbai', 'consent' => true, 'content_type' => 'video']);

        $this->actingAs($admin);
        $this->get(route('admin.dashboard'))->assertOk();
        $this->get(route('admin.videos.index'))->assertOk()->assertSee('Video recordings');
        $this->get(route('admin.audios.index'))->assertOk()->assertDontSee('Dr Admin Test');
        $this->get(route('admin.cards.index'))->assertOk()->assertDontSee('Dr Admin Test');
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
        $this->get(route('admin.doctors.show', $reel))->assertOk()->assertSee('TDR-ADMIN-TEST');
        $this->get(route('admin.settings.edit'))->assertOk();
        $this->get(route('admin.reports.index'))->assertOk();
        $this->get(route('admin.profile.edit'))->assertOk()->assertSee('Change password');

        $this->get(route('admin.doctors.export'))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}
