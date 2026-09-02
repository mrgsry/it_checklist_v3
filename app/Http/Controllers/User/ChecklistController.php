<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ChecklistForm;
use App\Models\ChecklistSubmission;
use App\Models\DailyActivity;
use App\Models\SubmissionAnswer;
use App\Services\AnomalyDetectionService;
use App\Services\ChecklistPhotoCompressor;
use App\Services\ChecklistSubmissionService;
use App\Services\SchedulerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ChecklistController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $formsDue = app(SchedulerService::class)->getFormsDueToday($user);
        $submittedFormIds = ChecklistSubmission::query()
            ->where('submitted_by', $user->id)
            ->whereDate('submission_date', Carbon::today())
            ->where('status', 'submitted')
            ->pluck('form_id');

        return view('user.checklist.index', compact('formsDue', 'submittedFormIds'));
    }

    public function fill(int $formId)
    {
        $user = Auth::user();
        $form = ChecklistForm::with('items')->findOrFail($formId);

        // Pastikan user di-assign ke form ini
        $isAssigned = $form->assignedUsers->contains($user->id);
        if (! $isAssigned) {
            abort(403, 'Anda tidak ditugaskan pada form ini.');
        }

        // Cek apakah sudah diisi hari ini
        $alreadySubmitted = ChecklistSubmission::where('form_id', $formId)
            ->where('submitted_by', $user->id)
            ->whereDate('submission_date', Carbon::today())
            ->where('status', 'submitted')
            ->exists();

        if ($alreadySubmitted) {
            return redirect()->route('user.checklist.index')
                ->with('error', 'Kamu sudah mengisi checklist ini hari ini.');
        }

        return view('user.checklist.fill', compact('form'));
    }

    public function submit(Request $request, int $formId)
    {
        $user = Auth::user();
        $form = ChecklistForm::with('items')->findOrFail($formId);

        // Pastikan request POST tidak dapat melewati pengecekan akses dari halaman form.
        if (! $form->assignedUsers()->whereKey($user->id)->exists()) {
            abort(403, 'Anda tidak ditugaskan pada form ini.');
        }

        // Validasi required fields
        $rules = [];
        foreach ($form->items as $item) {
            if ($item->field_type === 'photo') {
                $rules["answers.{$item->id}"] = $item->is_required ? 'required|array|min:1|max:5' : 'nullable|array|max:5';
                $rules["answers.{$item->id}.*"] = 'image|mimes:jpg,jpeg,png|max:5120';
            } elseif ($item->field_type === 'checkbox') {
                $rules["answers.{$item->id}"] = $item->is_required ? 'required|array' : 'nullable|array';
                foreach ($item->options ?? [] as $index => $option) {
                    $rules["answers.{$item->id}.{$index}"] = ($item->is_required ? 'required' : 'nullable').'|in:normal,tidak_normal';
                }
            } elseif ($item->is_required) {
                $rules["answers.{$item->id}"] = 'required';
            }
        }

        if (! empty($rules)) {
            $request->validate(
                $rules,
                [],
                collect($form->items)->mapWithKeys(
                    fn ($item) => ["answers.{$item->id}" => $item->label]
                )->toArray()
            );
        }

        // Cek duplikasi
        $existing = ChecklistSubmission::where('form_id', $formId)
            ->where('submitted_by', $user->id)
            ->whereDate('submission_date', Carbon::today())
            ->where('status', 'submitted')
            ->exists();

        if ($existing) {
            return redirect()->route('user.checklist.index')
                ->with('error', 'Checklist ini sudah pernah disubmit hari ini.');
        }

        $storedPaths = [];
        $submission = null;

        try {
            // Buat submission
            $submission = ChecklistSubmission::create([
                'form_id' => $formId,
                'submitted_by' => $user->id,
                'submission_date' => Carbon::today(),
                'submitted_at' => now(),
                'notes' => $request->notes,
                'status' => 'submitted',
            ]);

            // Simpan jawaban
            $answers = $request->input('answers', []);
            foreach ($form->items as $item) {
                if ($item->field_type === 'photo') {
                    $paths = [];
                    foreach ($request->file("answers.{$item->id}", []) as $file) {
                        if ($file->isValid()) {
                            $path = app(ChecklistPhotoCompressor::class)->store($file);
                            $paths[] = $path;
                            $storedPaths[] = $path;
                        }
                    }
                    $value = $paths === [] ? null : json_encode($paths, JSON_THROW_ON_ERROR);
                } else {
                    $value = $answers[$item->id] ?? null;
                    if ($item->field_type === 'checkbox' && is_array($value)) {
                        $value = json_encode(
                            collect($item->options ?? [])->mapWithKeys(
                                fn (string $option, int $index) => [$option => $value[$index] ?? null]
                            )->filter()->all(),
                            JSON_THROW_ON_ERROR
                        );
                    } elseif (is_array($value)) {
                        $value = implode(', ', $value);
                    }
                }

                SubmissionAnswer::create([
                    'submission_id' => $submission->id,
                    'form_item_id' => $item->id,
                    'answer_value' => $value,
                    'is_flagged' => false,
                ]);
            }
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($storedPaths);
            $submission?->delete();
            throw $exception;
        }

        // Deteksi anomali
        app(AnomalyDetectionService::class)->detectForSubmission($submission);

        return redirect()->route('user.dashboard')
            ->with('success', "✅ Checklist \"{$form->title}\" berhasil disubmit!");
    }

    public function history()
    {
        $user = Auth::user();

        $dailyActivities = DailyActivity::query()
            ->where('user_id', $user->id)
            ->orderByDesc('activity_date')
            ->latest()
            ->paginate(15, ['*'], 'daily_page');

        $submissions = ChecklistSubmission::where('submitted_by', $user->id)
            ->with(['form', 'answers.formItem'])
            ->orderBy('submitted_at', 'desc')
            ->paginate(15, ['*'], 'submission_page');

        return view('user.checklist.history', compact('dailyActivities', 'submissions'));
    }

    public function edit(ChecklistSubmission $submission)
    {
        $this->ensureOwner($submission);
        $submission->load(['form.items', 'answers.formItem']);

        return view('user.checklist.edit', compact('submission'));
    }

    public function update(Request $request, ChecklistSubmission $submission)
    {
        $this->ensureOwner($submission);
        $this->updateSubmission($request, $submission);

        return redirect()->route('user.history')->with('success', 'Submission berhasil diperbarui.');
    }

    public function updateSubmission(Request $request, ChecklistSubmission $submission): void
    {
        $submission->load(['form.items', 'answers']);
        $form = $submission->form;
        $rules = $this->answerRules($form, true, $submission);
        $request->validate($rules, [], $this->answerAttributes($form));

        $storedPaths = [];
        $replacedPaths = [];
        try {
            foreach ($form->items as $item) {
                $answer = $submission->answers->firstWhere('form_item_id', $item->id);
                if ($item->field_type === 'photo') {
                    $files = $request->file("answers.{$item->id}", []);
                    if ($files === []) {
                        continue;
                    }

                    $paths = [];
                    foreach ($files as $file) {
                        if ($file->isValid()) {
                            $path = app(ChecklistPhotoCompressor::class)->store($file);
                            $paths[] = $path;
                            $storedPaths[] = $path;
                        }
                    }
                    $replacedPaths = [...$replacedPaths, ...($answer?->photoPaths() ?? [])];
                    $value = json_encode($paths, JSON_THROW_ON_ERROR);
                } else {
                    $value = $this->answerValue($request->input("answers.{$item->id}"), $item);
                }

                $submission->answers()->updateOrCreate(
                    ['form_item_id' => $item->id],
                    ['answer_value' => $value, 'is_flagged' => false]
                );
            }
            $submission->update(['notes' => $request->input('notes')]);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($storedPaths);
            throw $exception;
        }

        Storage::disk('public')->delete(array_values(array_unique($replacedPaths)));

        $submission->load('answers.formItem');
        app(AnomalyDetectionService::class)->detectForSubmission($submission);

    }

    public function destroy(ChecklistSubmission $submission, ChecklistSubmissionService $submissionService)
    {
        $this->ensureOwner($submission);
        $submissionService->delete($submission);

        return redirect()->route('user.history')->with('success', 'Submission dihapus. Checklist dapat diisi ulang.');
    }

    private function ensureOwner(ChecklistSubmission $submission): void
    {
        abort_unless($submission->submitted_by === Auth::id(), 403);
    }

    private function answerRules(ChecklistForm $form, bool $editing = false, ?ChecklistSubmission $submission = null): array
    {
        $rules = ['notes' => ['nullable', 'string']];
        foreach ($form->items as $item) {
            if ($item->field_type === 'photo') {
                $hasExistingPhoto = $editing && (($submission?->answers->firstWhere('form_item_id', $item->id)?->photoPaths()) ?? []) !== [];
                $rules["answers.{$item->id}"] = $item->is_required && ! $hasExistingPhoto ? 'required|array|min:1|max:5' : 'nullable|array|max:5';
                $rules["answers.{$item->id}.*"] = 'image|mimes:jpg,jpeg,png|max:5120';
            } elseif ($item->field_type === 'checkbox') {
                $rules["answers.{$item->id}"] = $item->is_required ? 'required|array' : 'nullable|array';
                foreach ($item->options ?? [] as $index => $option) {
                    $rules["answers.{$item->id}.{$index}"] = ($item->is_required ? 'required' : 'nullable').'|in:normal,tidak_normal';
                }
            } elseif ($item->is_required) {
                $rules["answers.{$item->id}"] = 'required';
            }
        }

        return $rules;
    }

    private function answerAttributes(ChecklistForm $form): array
    {
        return $form->items->mapWithKeys(fn ($item) => ["answers.{$item->id}" => $item->label])->all();
    }

    private function answerValue(mixed $value, $item): ?string
    {
        if ($item->field_type === 'checkbox' && is_array($value)) {
            return json_encode(collect($item->options ?? [])->mapWithKeys(
                fn (string $option, int $index) => [$option => $value[$index] ?? null]
            )->filter()->all(), JSON_THROW_ON_ERROR);
        }

        return is_array($value) ? implode(', ', $value) : $value;
    }
}
