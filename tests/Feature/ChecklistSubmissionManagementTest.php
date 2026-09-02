<?php

namespace Tests\Feature;

use App\Models\ChecklistForm;
use App\Models\ChecklistSubmission;
use App\Models\FormAssignment;
use App\Models\FormItem;
use App\Models\SubmissionAnswer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChecklistSubmissionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_update_a_submission_and_anomaly_is_recalculated(): void
    {
        [$user, $submission, $item] = $this->submissionWithTextAnswer('normal');

        $this->actingAs($user)
            ->put(route('user.submissions.update', $submission), [
                'answers' => [$item->id => 'router rusak'],
                'notes' => 'Diperbarui',
            ])
            ->assertRedirect(route('user.history'));

        $answer = SubmissionAnswer::firstOrFail();
        $this->assertSame('router rusak', $answer->answer_value);
        $this->assertTrue($answer->is_flagged);
        $this->assertSame('Diperbarui', $submission->fresh()->notes);
    }

    public function test_user_cannot_manage_another_users_submission(): void
    {
        [, $submission, $item] = $this->submissionWithTextAnswer('normal');
        $otherUser = User::factory()->create(['role' => 'user']);

        $this->actingAs($otherUser)
            ->get(route('user.submissions.edit', $submission))
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->put(route('user.submissions.update', $submission), ['answers' => [$item->id => 'changed']])
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->delete(route('user.submissions.destroy', $submission))
            ->assertForbidden();
    }

    public function test_deleting_submission_removes_photos_and_allows_same_day_refill(): void
    {
        Storage::fake('public');
        Carbon::setTestNow('2026-09-02 09:00:00');
        $user = User::factory()->create(['role' => 'user']);
        $form = ChecklistForm::factory()->create(['start_date' => Carbon::today()->toDateString()]);
        FormAssignment::create(['form_id' => $form->id, 'user_id' => $user->id]);
        $item = FormItem::create([
            'form_id' => $form->id,
            'label' => 'Dokumentasi',
            'field_type' => 'photo',
            'is_required' => true,
            'order_index' => 1,
        ]);
        $submission = ChecklistSubmission::create([
            'form_id' => $form->id,
            'submitted_by' => $user->id,
            'submission_date' => Carbon::today(),
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);
        Storage::disk('public')->put('checklist-photos/delete-me.jpg', 'image');
        SubmissionAnswer::create([
            'submission_id' => $submission->id,
            'form_item_id' => $item->id,
            'answer_value' => json_encode(['checklist-photos/delete-me.jpg']),
        ]);

        $this->actingAs($user)
            ->delete(route('user.submissions.destroy', $submission))
            ->assertRedirect(route('user.history'));

        $this->assertDatabaseMissing('checklist_submissions', ['id' => $submission->id]);
        $this->assertDatabaseCount('submission_answers', 0);
        Storage::disk('public')->assertMissing('checklist-photos/delete-me.jpg');
        $this->actingAs($user)->get(route('user.checklist.fill', $form))->assertOk();
    }

    private function submissionWithTextAnswer(string $value): array
    {
        $user = User::factory()->create(['role' => 'user']);
        $form = ChecklistForm::factory()->create();
        $item = FormItem::create([
            'form_id' => $form->id,
            'label' => 'Status perangkat',
            'field_type' => 'text',
            'is_required' => true,
            'order_index' => 1,
        ]);
        $submission = ChecklistSubmission::create([
            'form_id' => $form->id,
            'submitted_by' => $user->id,
            'submission_date' => today(),
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);
        SubmissionAnswer::create([
            'submission_id' => $submission->id,
            'form_item_id' => $item->id,
            'answer_value' => $value,
        ]);

        return [$user, $submission, $item];
    }
}