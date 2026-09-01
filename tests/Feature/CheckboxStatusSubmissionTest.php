<?php

namespace Tests\Feature;

use App\Models\ChecklistForm;
use App\Models\FormAssignment;
use App\Models\FormItem;
use App\Models\SubmissionAnswer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckboxStatusSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_abnormal_checkbox_status_is_saved_and_flagged_as_an_issue(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $form = ChecklistForm::factory()->create();
        $item = FormItem::create([
            'form_id' => $form->id,
            'label' => 'Pengecekan CCTV Loby kanan',
            'field_type' => 'checkbox',
            'options' => ['Pengecekan Lensa Camera', 'Pengecekan Body Camera'],
            'is_required' => true,
            'order_index' => 1,
        ]);
        FormAssignment::create(['form_id' => $form->id, 'user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('user.checklist.submit', $form), [
                'answers' => [
                    $item->id => [
                        0 => 'normal',
                        1 => 'tidak_normal',
                    ],
                ],
            ])
            ->assertRedirect(route('user.dashboard'));

        $answer = SubmissionAnswer::firstOrFail();

        $this->assertSame([
            'Pengecekan Lensa Camera' => 'normal',
            'Pengecekan Body Camera' => 'tidak_normal',
        ], $answer->checkboxStatuses());
        $this->assertTrue($answer->is_flagged);
    }
}