<?php

namespace Tests\Feature;

use App\Models\ChecklistForm;
use App\Models\ChecklistSubmission;
use App\Models\FormAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserChecklistStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_submitted_form_is_marked_complete_and_cannot_be_filled_again(): void
    {
        Carbon::setTestNow('2026-07-24 09:00:00');

        $user = User::factory()->create(['role' => 'user']);
        $form = ChecklistForm::factory()->create(['start_date' => Carbon::today()->toDateString()]);
        FormAssignment::create(['form_id' => $form->id, 'user_id' => $user->id]);
        ChecklistSubmission::create([
            'form_id' => $form->id,
            'submitted_by' => $user->id,
            'submission_date' => Carbon::today(),
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);

        $this->actingAs($user)
            ->get(route('user.checklist.index'))
            ->assertOk()
            ->assertSee('Form Complete')
            ->assertDontSee('Isi Sekarang');

        $this->actingAs($user)
            ->get(route('user.checklist.fill', $form))
            ->assertRedirect(route('user.checklist.index'));
    }
}