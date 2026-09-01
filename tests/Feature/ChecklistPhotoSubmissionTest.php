<?php

namespace Tests\Feature;

use App\Models\ChecklistForm;
use App\Models\FormAssignment;
use App\Models\FormItem;
use App\Models\SubmissionAnswer;
use App\Models\User;
use App\Services\ChecklistPhotoCompressor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class ChecklistPhotoSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_submits_one_photo_and_preserves_legacy_single_photo_paths(): void
    {
        [$user, $form, $item] = $this->assignedPhotoForm();
        $this->mockCompressor(['checklist-photos/one.jpg']);

        $this->actingAs($user)
            ->post(route('user.checklist.submit', $form), [
                'answers' => [$item->id => [UploadedFile::fake()->image('one.jpg')]],
            ])
            ->assertRedirect(route('user.dashboard'));

        $answer = SubmissionAnswer::firstOrFail();
        $this->assertSame(['checklist-photos/one.jpg'], $answer->photoPaths());
        $this->assertSame('["checklist-photos\/one.jpg"]', $answer->answer_value);

        $answer->update(['answer_value' => 'checklist-photos/legacy.jpg']);
        $this->assertSame(['checklist-photos/legacy.jpg'], $answer->fresh()->photoPaths());
    }

    public function test_it_submits_up_to_five_photos_for_an_item(): void
    {
        [$user, $form, $item] = $this->assignedPhotoForm();
        $paths = collect(range(1, 5))->map(fn (int $number) => "checklist-photos/{$number}.jpg")->all();
        $this->mockCompressor($paths);

        $this->actingAs($user)
            ->post(route('user.checklist.submit', $form), [
                'answers' => [$item->id => array_map(
                    fn (int $number) => UploadedFile::fake()->image("{$number}.jpg"),
                    range(1, 5)
                )],
            ])
            ->assertRedirect(route('user.dashboard'));

        $this->assertSame($paths, SubmissionAnswer::firstOrFail()->photoPaths());
    }

    public function test_it_rejects_missing_or_more_than_five_required_photos(): void
    {
        [$user, $form, $item] = $this->assignedPhotoForm();

        $this->actingAs($user)
            ->from(route('user.checklist.fill', $form))
            ->post(route('user.checklist.submit', $form), ['answers' => []])
            ->assertRedirect(route('user.checklist.fill', $form))
            ->assertSessionHasErrors("answers.{$item->id}");

        $this->actingAs($user)
            ->from(route('user.checklist.fill', $form))
            ->post(route('user.checklist.submit', $form), [
                'answers' => [$item->id => array_map(
                    fn (int $number) => UploadedFile::fake()->image("{$number}.jpg"),
                    range(1, 6)
                )],
            ])
            ->assertRedirect(route('user.checklist.fill', $form))
            ->assertSessionHasErrors("answers.{$item->id}");

        $this->assertDatabaseCount('checklist_submissions', 0);
    }

    public function test_it_removes_stored_photos_and_partial_submission_when_compression_fails(): void
    {
        Storage::fake('public');
        [$user, $form, $item] = $this->assignedPhotoForm();
        $compressor = Mockery::mock(ChecklistPhotoCompressor::class);
        $calls = 0;
        $compressor->shouldReceive('store')->twice()->andReturnUsing(function () use (&$calls) {
            $calls++;
            if ($calls === 2) {
                throw new RuntimeException('Compression failed');
            }

            Storage::disk('public')->put('checklist-photos/first.jpg', 'image');

            return 'checklist-photos/first.jpg';
        });
        $this->app->instance(ChecklistPhotoCompressor::class, $compressor);

        $this->withoutExceptionHandling();

        try {
            $this->actingAs($user)->post(route('user.checklist.submit', $form), [
                'answers' => [$item->id => [
                    UploadedFile::fake()->image('first.jpg'),
                    UploadedFile::fake()->image('second.jpg'),
                ]],
            ]);
            $this->fail('Expected compression failure to be thrown.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Compression failed', $exception->getMessage());
        }

        Storage::disk('public')->assertMissing('checklist-photos/first.jpg');
        $this->assertDatabaseCount('checklist_submissions', 0);
    }

    private function assignedPhotoForm(): array
    {
        $user = User::factory()->create(['role' => 'user']);
        $form = ChecklistForm::factory()->create();
        $item = FormItem::create([
            'form_id' => $form->id,
            'label' => 'Dokumentasi',
            'field_type' => 'photo',
            'is_required' => true,
            'order_index' => 1,
        ]);
        FormAssignment::create(['form_id' => $form->id, 'user_id' => $user->id]);

        return [$user, $form, $item];
    }

    private function mockCompressor(array $paths): void
    {
        $compressor = Mockery::mock(ChecklistPhotoCompressor::class);
        $compressor->shouldReceive('store')->times(count($paths))->andReturn(...$paths);
        $this->app->instance(ChecklistPhotoCompressor::class, $compressor);
    }
}