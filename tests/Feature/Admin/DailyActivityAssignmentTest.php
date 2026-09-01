<?php

namespace Tests\Feature\Admin;

use App\Models\DailyActivity;
use App\Models\ChecklistSubmission;
use App\Models\User;
use App\Notifications\DailyActivityAssignedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DailyActivityAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_assign_activity_and_user_receives_notification(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($admin)->post(route('admin.daily-activities.store'), [
            'user_id' => $user->id, 'activity_date' => '2026-07-24',
            'activity' => 'Periksa backup', 'category' => 'Network/System', 'notes' => 'Sebelum jam 10',
        ])->assertRedirect(route('admin.daily-activities.index'));

        $activity = DailyActivity::firstOrFail();
        $this->assertSame($admin->id, $activity->assigned_by);
        $this->assertNotNull($activity->assigned_at);
        $this->assertSame('Network/System', $activity->category);
        Notification::assertSentTo($user, DailyActivityAssignedNotification::class);
    }

    public function test_user_can_update_only_progress_on_assigned_activity(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $activity = DailyActivity::factory()->for($user)->create([
            'assigned_by' => $admin->id, 'assigned_at' => now(), 'activity' => 'Tugas tetap',
        ]);

        $this->actingAs($user)->put(route('user.daily-activities.update', $activity), [
            'activity_date' => '2030-01-01', 'activity' => 'Manipulasi',
            'status' => 'completed', 'notes' => 'Selesai',
        ])->assertRedirect();

        $this->assertDatabaseHas('daily_activities', [
            'id' => $activity->id, 'activity' => 'Tugas tetap', 'status' => 'completed', 'notes' => 'Selesai',
        ]);
    }

    public function test_non_admin_cannot_assign_activity_or_view_monitor(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->post(route('admin.daily-activities.store'), [])->assertForbidden();
        $this->actingAs($user)->get(route('admin.activity-monitor'))->assertForbidden();
    }

    public function test_admin_monitor_returns_daily_activity_and_submission(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        DailyActivity::factory()->for($user)->create(['activity' => 'Audit perangkat']);

        $response = $this->actingAs($admin)->getJson(route('admin.activity-monitor'));
        $response->assertOk()->assertJsonFragment(['type' => 'Daily Activity', 'user' => $user->name]);
    }

    public function test_admin_monitor_returns_the_latest_daily_activity_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $activity = DailyActivity::factory()->for($user)->create([
            'activity' => 'Update perangkat',
            'status' => 'in_progress',
        ]);

        $this->actingAs($admin)->getJson(route('admin.activity-monitor'))
            ->assertJsonFragment(['id' => 'daily-'.$activity->id, 'status' => 'in_progress']);

        $activity->update(['status' => 'completed']);

        $this->actingAs($admin)->getJson(route('admin.activity-monitor'))
            ->assertOk()
            ->assertJsonFragment(['id' => 'daily-'.$activity->id, 'status' => 'completed'])
            ->assertHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function test_admin_can_view_daily_activity_card_details(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        DailyActivity::factory()->for($user)->create([
            'activity' => 'Verifikasi inventaris',
            'activity_date' => today(),
            'status' => 'completed',
        ]);

        $this->actingAs($admin)->getJson(route('admin.dashboard.card-details', ['card' => 'daily']))
            ->assertOk()
            ->assertJsonPath('title', 'Detail Daily Activity Hari Ini')
            ->assertJsonFragment(['user' => $user->name, 'item' => 'Verifikasi inventaris', 'status' => 'completed'])
            ->assertHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function test_admin_can_export_filtered_daily_activities_to_excel(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        DailyActivity::factory()->for($user)->create(['activity' => 'Masuk filter', 'activity_date' => '2026-08-10', 'status' => 'completed']);
        DailyActivity::factory()->for($user)->create(['activity' => 'Di luar filter', 'activity_date' => '2026-08-20', 'status' => 'completed']);

        $this->actingAs($admin)
            ->get(route('admin.daily-activities.export-excel', ['date_from' => '2026-08-01', 'date_to' => '2026-08-15', 'status' => 'completed']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->assertHeader('Content-Disposition')
            ->assertSee('Masuk filter')
            ->assertDontSee('Di luar filter');
    }

    public function test_admin_can_export_filtered_daily_activities_to_pdf(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        DailyActivity::factory()->for($user)->create(['activity' => 'PDF filter', 'status' => 'completed']);

        $this->actingAs($admin)
            ->get(route('admin.daily-activities.export-pdf', ['status' => 'completed']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_non_admin_cannot_view_dashboard_card_details(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->getJson(route('admin.dashboard.card-details', ['card' => 'daily']))
            ->assertForbidden();
    }

    public function test_admin_dashboard_shows_daily_activity_trend_for_selected_period(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        DailyActivity::factory()->for($user)->create([
            'activity' => 'Periksa log server',
            'activity_date' => now()->startOfWeek()->addDay(),
            'status' => 'completed',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard', ['trend_period' => 'week']));

        $response->assertOk();
        $response->assertSee('Trend Daily Activity');
        $response->assertSee('1 Minggu');
        $response->assertSee('Daily Activity');
    }

    public function test_admin_dashboard_shows_daily_task_by_user_donut_chart_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        DailyActivity::factory()->for($user)->count(3)->create([
            'activity' => 'Periksa log server',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Daily Task per User');
        $response->assertSee($user->name);
    }
}
