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
            'category' => 'Network & Connectivity', 'user' => $user->name, 'departement' => 'IT',
            'contact' => '08123456789', 'email' => $user->email, 'detail' => 'Temperature alarm.',
            'location' => 'Server room',
        ];

        $this->actingAs($admin)->postJson(route('admin.ticketing.create', $submission), $payload)
            ->assertCreated()
            ->assertJsonPath('data.ticket_number', 'TERRA-123');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://terra.test/api/tickets'
                && $request['category'] === 'Network & Connectivity';
        });

        $this->assertDatabaseMissing('daily_activities', ['submission_id' => $submission->id]);
        $this->assertSame('Network & Connectivity', $submission->fresh()->ticketing_data['Server room temperature']['category']);

        $this->actingAs($admin)->postJson(route('admin.ticketing.status', $submission), [
            'item' => 'Server room temperature', 'status' => 'closed',
            'ticket_number' => 'TERRA-123',
        ])->assertOk();

        $this->assertDatabaseHas('daily_activities', [
            'user_id' => $user->id, 'type' => 'ticketing', 'category' => 'Network & Connectivity',
            'submission_id' => $submission->id, 'ticket_item' => 'Server room temperature',
            'ticket_number' => 'TERRA-123',
            'user_request' => $user->name,
        ]);
        $this->assertDatabaseHas('user_requests', [
            'submission_id' => $submission->id,
            'ticket_item' => 'Server room temperature',
            'requester' => $user->name,
            'detail' => 'Temperature alarm.',
        ]);

        $this->actingAs($admin)->postJson(route('admin.ticketing.create', $submission), $payload)->assertOk();
        $this->assertSame(0, DailyActivity::where('submission_id', $submission->id)->count());

        $this->withHeader('X-API-Key', 'secret')->postJson(route('api.ticketing.status', $submission), [
            'item' => 'Server room temperature', 'status' => 'closed', 'category' => 'Network & Connectivity',
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
            'category_name' => 'Network & Connectivity',
        ], 200)]);
        config(['services.terra_ticketing.base_url' => 'https://terra.test', 'services.terra_ticketing.api_key' => 'secret']);

        $this->actingAs($admin)->postJson(route('admin.ticketing.create', $submission), [
            'item' => 'Router utama', 'service_department' => 'IT', 'type' => 1,
            'category' => 'Hardware & Devices', 'user' => $user->name, 'departement' => 'IT',
            'contact' => '08123456789', 'email' => $user->email, 'detail' => 'Tidak terhubung.',
            'location' => 'Server room',
        ])->assertOk()->assertJsonPath('data.ticket_number', 'TERRA-456');

        $this->assertDatabaseMissing('daily_activities', ['submission_id' => $submission->id]);
        $this->assertSame('Network & Connectivity', $submission->fresh()->ticketing_data['Router utama']['category']);
    }

    public function test_ticket_response_category_object_is_saved_to_ticketing_data(): void
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
            'success' => true,
            'data' => [
                'ticket_number' => 'TERRA-457',
                'ticket_url' => 'https://terra.test/tickets/TERRA-457',
                'category' => ['name' => 'Network & Connectivity'],
            ],
        ], 201)]);
        config(['services.terra_ticketing.base_url' => 'https://terra.test', 'services.terra_ticketing.api_key' => 'secret']);

        $this->actingAs($admin)->postJson(route('admin.ticketing.create', $submission), [
            'item' => 'Keyboard rusak', 'service_department' => 'IT', 'type' => 1,
            'category' => 'Network & Connectivity', 'user' => $user->name, 'departement' => 'IT',
            'contact' => '08123456789', 'email' => $user->email, 'detail' => 'Keyboard tidak berfungsi.',
            'location' => 'Ruang server',
        ])->assertCreated();

        $this->assertSame('Network & Connectivity', $submission->fresh()->ticketing_data['Keyboard rusak']['category']);
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
                'category' => 'Hardware & Devices',
            ]],
        ]);
        config(['services.terra_ticketing.api_key' => 'secret']);

        $this->withHeader('X-API-Key', 'secret')->postJson(route('api.ticketing.status', $submission), [
            'item' => 'Printer error', 'status' => 'in_progress',
        ])->assertOk();
        $this->assertDatabaseMissing('daily_activities', ['submission_id' => $submission->id]);

        $this->withHeader('X-API-Key', 'secret')->postJson(route('api.ticketing.status', $submission), [
            'item' => 'Printer error', 'status' => 'closed', 'category' => 'Bug / Error',
        ])->assertOk()->assertJsonPath('closed', true);
        $this->assertDatabaseHas('daily_activities', [
            'submission_id' => $submission->id, 'category' => 'Hardware & Devices', 'ticket_number' => 'TERRA-999',
        ]);
    }
}