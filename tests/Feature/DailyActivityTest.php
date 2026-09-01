<?php

namespace Tests\Feature;

use App\Models\DailyActivity;
use App\Models\ChecklistForm;
use App\Models\ChecklistSubmission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_daily_activity(): void
    {
        Carbon::setTestNow('2026-07-23 09:00:00');
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->post(route('user.daily-activities.store'), [
                'activity_date' => '2026-07-23',
                'activity' => 'Monitoring server utama',
                'status' => 'completed',
                'notes' => 'Kondisi server normal.',
            ])
            ->assertRedirect(route('user.daily-activities.index', ['date' => '2026-07-23']));

        $this->assertDatabaseHas('daily_activities', [
            'user_id' => $user->id,
            'activity' => 'Monitoring server utama',
            'status' => 'completed',
        ]);
    }

    public function test_user_cannot_update_another_users_daily_activity(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $activity = DailyActivity::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->put(route('user.daily-activities.update', $activity), [
                'activity_date' => today()->toDateString(),
                'activity' => 'Tidak boleh diubah',
                'status' => 'completed',
            ])
            ->assertForbidden();
    }

    public function test_user_can_open_the_update_form_and_change_activity_status(): void
    {
        Carbon::setTestNow('2026-07-24 09:00:00');
        $user = User::factory()->create(['role' => 'user']);
        $activity = DailyActivity::factory()->for($user)->create([
            'activity_date' => Carbon::today(),
            'status' => 'in_progress',
        ]);

        $this->actingAs($user)
            ->get(route('user.daily-activities.index', ['date' => Carbon::today()->toDateString(), 'edit' => $activity->id]))
            ->assertOk()
            ->assertSee('Simpan Perubahan');

        $this->actingAs($user)
            ->put(route('user.daily-activities.update', $activity), [
                'status' => 'completed',
                'notes' => 'Pekerjaan selesai.',
            ])
            ->assertRedirect(route('user.daily-activities.index', ['date' => Carbon::today()->toDateString()]));

        $this->assertDatabaseHas('daily_activities', [
            'id' => $activity->id,
            'status' => 'completed',
            'notes' => 'Pekerjaan selesai.',
        ]);
    }

    public function test_admin_can_monitor_all_staff_daily_activities(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'user', 'name' => 'Staff Operasional']);
        DailyActivity::factory()->for($staff)->create([
            'activity' => 'Update inventaris perangkat',
            'activity_date' => '2026-07-23',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.daily-activities.index', ['date' => '2026-07-23']))
            ->assertOk()
            ->assertSee('Staff Operasional')
            ->assertSee('Update inventaris perangkat');
    }

    public function test_user_history_shows_only_their_daily_activities_and_submissions(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $form = ChecklistForm::factory()->create(['title' => 'Checklist Ruang Server']);

        $otherForm = ChecklistForm::factory()->create(['title' => 'Checklist Pengguna Lain']);

        DailyActivity::factory()->for($user)->create(['activity' => 'Backup database harian']);
        DailyActivity::factory()->for($otherUser)->create(['activity' => 'Aktivitas pengguna lain']);
        ChecklistSubmission::create([
            'form_id' => $form->id,
            'submitted_by' => $user->id,
            'submission_date' => today(),
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);
        ChecklistSubmission::create([
            'form_id' => $otherForm->id,
            'submitted_by' => $otherUser->id,
            'submission_date' => today(),
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);

        $this->actingAs($user)
            ->get(route('user.history'))
            ->assertOk()
            ->assertSee('Riwayat Daily Activity')
            ->assertSee('Backup database harian')
            ->assertDontSee('Aktivitas pengguna lain')
            ->assertSee('Checklist Ruang Server')
            ->assertDontSee('Checklist Pengguna Lain');
    }
}
