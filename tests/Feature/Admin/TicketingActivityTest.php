<?php

namespace Tests\Feature\Admin;

use App\Models\ChecklistForm;
use App\Models\ChecklistSubmission;
use App\Models\DailyActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TicketingActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_ticket_creation_creates_one_ticketing_activity_for_the_submission_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $submission = ChecklistSubmission::create([
            'form_id' => ChecklistForm::factory()->create()->id,
            'submitted_by' => $user->id,
            'submission_date' => today(),
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);

        Http::fake([
            '*' => Http::response([
                'success' => true,
                'data' => ['ticket_number' => 'TERRA-123', 'ticket_url' => 'https://terra.test/tickets/TERRA-123'],
            ], 201),
        ]);
        config(['services.terra_ticketing.base_url' => 'https://terra.test', 'services.terra_ticketing.api_key' => 'secret']);

        $payload = [
            'item' => 'Server room temperature', 'service_department' => 'IT', 'type' => 1,
            'category' => 'Network/System', 'user' => $user->name, 'departement' => 'IT',
            'contact' => '08123456789', 'email' => $user->email, 'detail' => 'Temperature alarm.',
            'location' => 'Server room',
        ];

        $this->actingAs($admin)->postJson(route('admin.ticketing.create', $submission), $payload)
            ->assertCreated()
            ->assertJsonPath('data.ticket_number', 'TERRA-123');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://terra.test/api/tickets'
                && $request['category'] === 'Network/System';
        });

        $this->assertDatabaseMissing('daily_activities', ['submission_id' => $submission->id]);

        $this->actingAs($admin)->postJson(route('admin.ticketing.status', $submission), [
            'item' => 'Server room temperature', 'status' => 'closed',
            'ticket_number' => 'TERRA-123', 'category' => 'Network/System',
        ])->assertOk();

        $this->assertDatabaseHas('daily_activities', [
            'user_id' => $user->id, 'type' => 'ticketing', 'category' => 'Network/System',
            'submission_id' => $submission->id, 'ticket_item' => 'Server room temperature',
            'ticket_number' => 'TERRA-123',
        ]);

        $this->actingAs($admin)->postJson(route('admin.ticketing.create', $submission), $payload)->assertOk();
        $this->assertSame(0, DailyActivity::where('submission_id', $submission->id)->count());

        $this->withHeader('X-API-Key', 'secret')->postJson(route('api.ticketing.status', $submission), [
            'item' => 'Server room temperature', 'status' => 'closed', 'category' => 'Network/System',
        ])->assertOk();
        $this->assertSame(1, DailyActivity::where('submission_id', $submission->id)->count());

        $this->withHeader('X-API-Key', 'secret')->postJson(route('api.ticketing.status', $submission), [
            'item' => 'Server room temperature', 'status' => 'closed',
        ])->assertOk();
        $this->assertSame(1, DailyActivity::where('submission_id', $submission->id)->count());
    }

    public function test_ticket_response_can_use_a_direct_payload_and_category_from_terra(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $submission = ChecklistSubmission::create([
            'form_id' => ChecklistForm::factory()->create()->id,
            'submitted_by' => $user->id,
            'submission_date' => today(),
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);

        Http::fake(['*' => Http::response([
            'ticket_number' => 'TERRA-456',
            'url' => 'https://terra.test/tickets/TERRA-456',
            'category_name' => 'Network/System',
        ], 200)]);
        config(['services.terra_ticketing.base_url' => 'https://terra.test', 'services.terra_ticketing.api_key' => 'secret']);

        $this->actingAs($admin)->postJson(route('admin.ticketing.create', $submission), [
            'item' => 'Router utama', 'service_department' => 'IT', 'type' => 1,
            'category' => 'Peripherals', 'user' => $user->name, 'departement' => 'IT',
            'contact' => '08123456789', 'email' => $user->email, 'detail' => 'Tidak terhubung.',
            'location' => 'Server room',
        ])->assertOk()->assertJsonPath('data.ticket_number', 'TERRA-456');

        $this->assertDatabaseMissing('daily_activities', ['submission_id' => $submission->id]);
        $this->assertSame('Network/System', $submission->fresh()->ticketing_data['Router utama']['category']);
    }

    public function test_legacy_ticket_activity_is_classified_as_ticketing_when_loaded(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $activity = DailyActivity::factory()->for($user)->create([
            'type' => 'daily_activity',
            'activity' => 'Selesaikan Ticket #TERRA-789: Gangguan jaringan.',
        ]);

        $this->assertSame('ticketing', $activity->type);
        $this->assertSame(1, DailyActivity::ofType('ticketing')->whereKey($activity->id)->count());
        $this->assertSame(0, DailyActivity::ofType('daily_activity')->whereKey($activity->id)->count());
    }

    public function test_ticketing_callback_creates_activity_only_when_ticket_is_closed(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $submission = ChecklistSubmission::create([
            'form_id' => ChecklistForm::factory()->create()->id,
            'submitted_by' => $user->id,
            'submission_date' => today(), 'submitted_at' => now(), 'status' => 'submitted',
            'ticketing_data' => ['Printer error' => [
                'ticket_number' => 'TERRA-999', 'ticket_url' => 'https://terra.test/tickets/TERRA-999',
                'category' => 'Peripherals',
            ]],
        ]);
        config(['services.terra_ticketing.api_key' => 'secret']);

        $this->withHeader('X-API-Key', 'secret')->postJson(route('api.ticketing.status', $submission), [
            'item' => 'Printer error', 'status' => 'in_progress',
        ])->assertOk();
        $this->assertDatabaseMissing('daily_activities', ['submission_id' => $submission->id]);

        $this->withHeader('X-API-Key', 'secret')->postJson(route('api.ticketing.status', $submission), [
            'item' => 'Printer error', 'status' => 'closed', 'category' => 'Peripherals',
        ])->assertOk()->assertJsonPath('closed', true);
        $this->assertDatabaseHas('daily_activities', [
            'submission_id' => $submission->id, 'category' => 'Peripherals', 'ticket_number' => 'TERRA-999',
        ]);
    }
}