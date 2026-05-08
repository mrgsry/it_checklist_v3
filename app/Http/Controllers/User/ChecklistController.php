<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\SchedulerService;
use App\Services\AnomalyDetectionService;
use App\Models\ChecklistForm;
use App\Models\ChecklistSubmission;
use App\Models\SubmissionAnswer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChecklistController extends Controller
{
    public function index()
    {
        $user     = Auth::user();
        $formsDue = app(SchedulerService::class)->getFormsDueToday($user);

        return view('user.checklist.index', compact('formsDue'));
    }

    public function fill(int $formId)
    {
        $user = Auth::user();
        $form = ChecklistForm::with('items')->findOrFail($formId);

        // Pastikan user di-assign ke form ini
        $isAssigned = $form->assignedUsers->contains($user->id);
        if (!$isAssigned) {
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

        // Validasi required fields
        $rules = [];
        foreach ($form->items as $item) {
            if ($item->field_type === 'photo') {
                $rules["answers.{$item->id}"] = $item->is_required ? 'required|image|mimes:jpg,jpeg,png|max:5120' : 'nullable|image|mimes:jpg,jpeg,png|max:5120';
            } elseif ($item->is_required) {
                $rules["answers.{$item->id}"] = 'required';
            }
        }

        if (!empty($rules)) {
            $request->validate(
                $rules,
                [],
                collect($form->items)->mapWithKeys(
                    fn($item) =>
                    ["answers.{$item->id}" => $item->label]
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

        // Buat submission
        $submission = ChecklistSubmission::create([
            'form_id'         => $formId,
            'submitted_by'    => $user->id,
            'submission_date' => Carbon::today(),
            'submitted_at'    => now(),
            'notes'           => $request->notes,
            'status'          => 'submitted',
        ]);

        // Simpan jawaban
        $answers = $request->input('answers', []);
        foreach ($form->items as $item) {
            if ($item->field_type === 'photo') {
                $file = $request->file("answers.{$item->id}");
                $value = null;
                if ($file && $file->isValid()) {
                    $path = $file->store('checklist-photos', 'public');
                    $value = $path;
                }
            } else {
                $value = $answers[$item->id] ?? null;
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
            }

            SubmissionAnswer::create([
                'submission_id' => $submission->id,
                'form_item_id'  => $item->id,
                'answer_value'  => $value,
                'is_flagged'    => false,
            ]);
        }

        // Deteksi anomali
        app(AnomalyDetectionService::class)->detectForSubmission($submission);

        return redirect()->route('user.dashboard')
            ->with('success', "✅ Checklist \"{$form->title}\" berhasil disubmit!");
    }

    public function history()
    {
        $user = Auth::user();

        $submissions = ChecklistSubmission::where('submitted_by', $user->id)
            ->with(['form', 'answers.formItem'])
            ->orderBy('submitted_at', 'desc')
            ->paginate(15);

        return view('user.checklist.history', compact('submissions'));
    }
}
