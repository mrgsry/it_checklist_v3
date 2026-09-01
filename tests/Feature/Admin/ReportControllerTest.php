<?php

namespace Tests\Feature\Admin;

use App\Models\DailyActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_report_without_writing_the_large_report_payload_to_cache(): void
    {
        config(['cache.default' => 'array']);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Laporan Checklist');

        $this->assertSame([], Cache::getStore()->all());
    }

    public function test_report_polling_returns_only_daily_activity_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        DailyActivity::factory()->for($user)->create([
            'activity' => 'Periksa backup',
            'status' => 'completed',
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.reports.data'))
            ->assertOk()
            ->assertJsonPath('summaryStats.daily_total', 1)
            ->assertJsonPath('summaryStats.daily_completed', 1)
            ->assertJsonPath('dailyActivities.0.activity', 'Periksa backup')
            ->assertJsonMissingPath('summaryStats.total');
    }

    public function test_admin_can_export_filtered_report_to_excel(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        DailyActivity::factory()->for($user)->create([
            'activity' => 'Aktivitas terpilih',
            'activity_date' => '2026-08-01',
        ]);
        DailyActivity::factory()->for($user)->create([
            'activity' => 'Aktivitas lain',
            'activity_date' => '2026-08-20',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.reports.export-excel', [
                'date_from' => '2026-08-01',
                'date_to' => '2026-08-01',
            ]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->assertSee('Aktivitas terpilih')
            ->assertDontSee('Aktivitas lain');
    }
}