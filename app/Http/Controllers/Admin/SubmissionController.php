<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\User\ChecklistController as UserChecklistController;
use App\Models\ChecklistSubmission;
use App\Models\ChecklistForm;
use App\Models\User;
use App\Services\ChecklistSubmissionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->submissionData($request);
        $submissions = $data['query']->paginate(15)->withQueryString();

        if ($request->boolean('format_json') || $request->wantsJson()) {
            return response()->json([
                'data' => $submissions->getCollection()->map(fn ($submission) => [
                    'id' => $submission->id,
                    'form_title' => $submission->form->title ?? '-',
                    'user_name' => $submission->submitter->name ?? '-',
                    'submission_date' => $submission->submission_date?->isoFormat('D MMM Y'),
                    'submitted_at' => $submission->submitted_at?->format('H:i'),
                    'flagged_count' => $submission->answers->where('is_flagged', true)->count(),
                    'show_url' => route('admin.submissions.show', $submission),
                ])->values(),
                'meta' => [
                    'current_page' => $submissions->currentPage(),
                    'last_page' => $submissions->lastPage(),
                    'total' => $submissions->total(),
                    'from' => $submissions->firstItem(),
                    'to' => $submissions->lastItem(),
                ],
            ]);
        }

        return view('admin.submissions.index', array_merge($data, compact('submissions')));
    }

    public function exportPdf(ChecklistSubmission $submission)
    {
        $submission->load(['form', 'submitter', 'answers.formItem']);

        foreach ($submission->answers as $answer) {
            if ($answer->formItem?->field_type === 'photo' && filled($answer->answer_value)) {
                $answer->photoDataUris = array_filter(array_map(
                    fn (string $path) => $this->photoDataUri($path),
                    $answer->photoPaths()
                ));
            }
        }

        $logoDataUri = $this->photoDataUri('checklist-photos/tdi-2.png');

        return Pdf::loadView('admin.submissions.pdf', compact('submission', 'logoDataUri'))
            ->setPaper('a4')
            ->download('submission-'.$submission->id.'-'.now()->format('Ymd-His').'.pdf');
    }

    public function show(ChecklistSubmission $submission)
    {
        $submission->load(['form.items', 'submitter', 'answers.formItem']);
        return view('admin.submissions.show', compact('submission'));
    }

    public function destroy(ChecklistSubmission $submission, ChecklistSubmissionService $submissionService)
    {
        $submissionService->delete($submission);

        return redirect()->route('admin.submissions.index')->with('success', 'Submission berhasil dihapus.');
    }

    public function edit(ChecklistSubmission $submission)
    {
        $submission->load(['form.items', 'answers.formItem']);

        return view('user.checklist.edit', [
            'submission' => $submission,
            'layout' => 'layouts.admin',
            'backRoute' => route('admin.submissions.show', $submission),
            'updateRoute' => route('admin.submissions.update', $submission),
        ]);
    }

    public function update(Request $request, ChecklistSubmission $submission, UserChecklistController $checklistController)
    {
        $checklistController->updateSubmission($request, $submission);

        return redirect()->route('admin.submissions.show', $submission)->with('success', 'Submission berhasil diperbarui.');
    }

    private function submissionData(Request $request): array
    {
        $request->validate([
            'form_id' => ['nullable', 'integer', 'exists:checklist_forms,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $query = ChecklistSubmission::with(['form', 'submitter', 'answers.formItem'])
            ->when($request->filled('form_id'), fn ($builder) => $builder->where('form_id', $request->integer('form_id')))
            ->when($request->filled('user_id'), fn ($builder) => $builder->where('submitted_by', $request->integer('user_id')))
            ->when($request->filled('date_from'), fn ($builder) => $builder->whereDate('submission_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($builder) => $builder->whereDate('submission_date', '<=', $request->date_to))
            ->latest('submitted_at');

        $forms = ChecklistForm::orderBy('title')->get();
        $users = User::where('role', 'user')->orderBy('name')->get();

        return compact('query', 'forms', 'users');
    }

    private function photoDataUri(string $path): ?string
    {
        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        $absolutePath = Storage::disk('public')->path($path);
        $mimeType = mime_content_type($absolutePath) ?: 'image/jpeg';

        return 'data:'.$mimeType.';base64,'.base64_encode((string) Storage::disk('public')->get($path));
    }
}