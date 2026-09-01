<?php

namespace Tests\Feature\Admin;

use App\Models\ChecklistForm;
use App\Models\FormAssignment;
use App\Models\User;
use App\Notifications\FormAssignedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class FormAssignmentNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_newly_assigned_user_receives_an_email_notification(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $assignedUser = User::factory()->create(['role' => 'user']);

        $this->actingAs($admin)
            ->post(route('admin.forms.store'), $this->formData([$assignedUser->id]))
            ->assertRedirect(route('admin.forms.index'));

        $this->assertDatabaseHas('form_assignments', [
            'user_id' => $assignedUser->id,
        ]);
        Notification::assertSentTo($assignedUser, FormAssignedNotification::class);
    }

    public function test_only_newly_assigned_users_are_notified_when_a_form_is_updated(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $existingUser = User::factory()->create(['role' => 'user']);
        $newUser = User::factory()->create(['role' => 'user']);
        $form = ChecklistForm::factory()->create(['created_by' => $admin->id]);
        FormAssignment::create(['form_id' => $form->id, 'user_id' => $existingUser->id]);

        $this->actingAs($admin)
            ->put(route('admin.forms.update', $form), $this->formData([$existingUser->id, $newUser->id]))
            ->assertRedirect(route('admin.forms.index'));

        Notification::assertNotSentTo($existingUser, FormAssignedNotification::class);
        Notification::assertSentTo($newUser, FormAssignedNotification::class);
    }

    private function formData(array $assignedUserIds): array
    {
        return [
            'title' => 'Checklist Infrastruktur',
            'description' => 'Pemeriksaan rutin infrastruktur.',
            'schedule_type' => 'daily',
            'assigned_users' => $assignedUserIds,
            'items' => [[
                'label' => 'Koneksi internet',
                'field_type' => 'checkbox',
                'order_index' => 0,
            ]],
        ];
    }
}
