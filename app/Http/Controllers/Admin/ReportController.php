<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChecklistForm;
use App\Models\ChecklistSubmission;
use App\Models\DailyActivity;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->reportData($request, true);

        return view('admin.reports.index', $data);
    }

    public function exportPdf(Request $request)
    {
        $data = $this->reportData($request);

        return Pdf::loadView('admin.reports.pdf', $data)
            ->setPaper('a4', 'landscape')
            ->download('laporan-daily-weekly-activity-'.now()->format('Ymd-His').'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $data = $this->reportData($request);

        return response()->view('admin.reports.excel', $data)
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="laporan-aktivitas-'.now()->format('Ymd-His').'.xls"');
    }

    public function data(Request $request): JsonResponse
    {
        $this->validateFilters($request);

        return response()->json($this->buildStreamPayload($request));
    }

    private function reportData(Request $request, bool $paginateSubmissions = false): array
    {
        $this->validateFilters($request);

        // Do not cache this payload in the database cache store. It contains hundreds
        // of Eloquent models and relations, which can exceed MySQL's packet limit.
        $forms = ChecklistForm::with('items')->withCount('items')->orderBy('title')->get();
        $users = User::where('role', 'user')->orderBy('name')->get();

        $query = ChecklistSubmission::with(['form.items', 'submitter', 'answers.formItem'])
            ->where('status', 'submitted')
            ->when($request->filled('form_id'), fn ($q) => $q->where('form_id', $request->integer('form_id')))
            ->when($request->filled('user_id'), fn ($q) => $q->where('submitted_by', $request->integer('user_id')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('submission_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('submission_date', '<=', $request->date_to))
            ->when($request->filled('search'), function ($q) use ($request) {
                $keyword = $request->string('search')->toString();

                $q->where(function ($searchQuery) use ($keyword) {
                    $searchQuery->whereHas('form', fn ($formQuery) => $formQuery->where('title', 'like', "%{$keyword}%"))
                        ->orWhereHas('submitter', fn ($userQuery) => $userQuery->where('name', 'like', "%{$keyword}%"))
                        ->orWhereHas('answers', fn ($answerQuery) => $answerQuery->where('answer_value', 'like', "%{$keyword}%"));
                });
            })
            ->limit(500); // Limit hasil untuk performance

        $orderedQuery = $query->orderByDesc('submission_date')->orderByDesc('submitted_at');
        $allSubmissions = (clone $orderedQuery)->get();
        $submissions = $paginateSubmissions
            ? $orderedQuery->paginate(15)->withQueryString()
            : $allSubmissions;
        $allDailyActivities = (clone $this->dailyActivitiesQuery($request))->get();
        $dailyActivities = $this->dailyActivitiesQuery($request)
            ->paginate(15, ['*'], 'activity_page')
            ->withQueryString();
        $selectedForm = $request->filled('form_id') ? $forms->firstWhere('id', $request->integer('form_id')) : null;
        $selectedUser = $request->filled('user_id') ? $users->firstWhere('id', $request->integer('user_id')) : null;

        $periodDate = $request->date_from ?: $request->date_to ?: now()->toDateString();
        $reportPeriod = Carbon::parse($periodDate)->locale('id')->translatedFormat('F, Y');
        // Dompdf cannot reliably resolve Vite/public URLs in every environment.
        // Embed the local logo so the PDF is self-contained.
        $logoPath = storage_path('app/public/checklist-photos/tdi-2.png');
        $logoDataUri = is_file($logoPath)
            ? 'data:'.(mime_content_type($logoPath) ?: 'image/png').';base64,'.base64_encode((string) file_get_contents($logoPath))
            : null;

        $flaggedSubmissions = $allSubmissions->filter(fn ($submission) => $submission->answers->contains('is_flagged', true));
        $answeredCount = $allSubmissions->sum(fn ($submission) => $submission->answers->filter(fn ($answer) => filled($answer->answer_value))->count());
        $expectedCount = $allSubmissions->sum(fn ($submission) => $submission->form?->items->count() ?? 0);

        $summaryStats = [
            'total' => $allSubmissions->count(),
            'flagged' => $flaggedSubmissions->count(),
            'clean' => $allSubmissions->count() - $flaggedSubmissions->count(),
            'flagged_rate' => $allSubmissions->count() ? round($flaggedSubmissions->count() / $allSubmissions->count() * 100, 1) : 0,
            'answers' => $answeredCount,
            'expected_answers' => $expectedCount,
            'completion_rate' => $expectedCount ? round($answeredCount / $expectedCount * 100, 1) : 0,
            'flagged_answers' => $submissions->sum(fn ($submission) => $submission->answers->where('is_flagged', true)->count()),
            ...$this->dailyActivitySummary($allDailyActivities),
        ];

        $formSummary = $allSubmissions->groupBy('form_id')->map(fn (Collection $items) => $this->summarizeGroup($items));
        $userSummary = $allSubmissions->groupBy('submitted_by')->map(fn (Collection $items) => $this->summarizeGroup($items));

        return compact('forms', 'users', 'submissions', 'dailyActivities', 'selectedForm', 'selectedUser', 'reportPeriod', 'logoDataUri', 'summaryStats', 'formSummary', 'userSummary');
    }

    private function buildStreamPayload(Request $request): array
    {
        $allDailyActivities = (clone $this->dailyActivitiesQuery($request))->get();
        $dailyActivities = $this->dailyActivitiesQuery($request)
            ->paginate(15, ['*'], 'activity_page');
        $summaryStats = $this->dailyActivitySummary($allDailyActivities);

        return [
            'summaryStats' => $summaryStats,
            'dailyActivities' => $dailyActivities->map(fn ($activity) => [
                'id' => $activity->id,
                'date' => $activity->activity_date?->format('d/m/Y'),
                'staff' => $activity->user?->name ?? '-',
                'activity' => $activity->activity,
                'type' => $activity->type === 'ticketing' ? 'Ticketing' : 'Daily Activity',
                'category' => $activity->category,
                'ticket_url' => $activity->ticket_url,
                'status' => $activity->status,
                'status_label' => [
                    'completed' => 'Selesai',
                    'in_progress' => 'Dalam Proses',
                    'blocked' => 'Terhambat',
                ][$activity->status] ?? $activity->status,
                'status_color' => [
                    'completed' => 'success',
                    'in_progress' => 'warning',
                    'blocked' => 'danger',
                ][$activity->status] ?? 'secondary',
                'notes' => $activity->notes ?: '-',
                'assignee' => $activity->assigner?->name ?? 'Mandiri',
                'updated_at' => $activity->updated_at?->format('d/m/Y H:i'),
            ])->values(),
            'generatedAt' => now()->toIso8601String(),
        ];
    }

    private function dailyActivitiesQuery(Request $request)
    {
        return DailyActivity::with(['user', 'assigner'])
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('activity_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('activity_date', '<=', $request->date_to))
            ->when($request->filled('type'), fn ($q) => $q->ofType($request->type))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->category))
            ->when($request->filled('search'), function ($q) use ($request) {
                $keyword = $request->string('search')->toString();

                $q->where(function ($searchQuery) use ($keyword) {
                    $searchQuery->where('activity', 'like', "%{$keyword}%")
                        ->orWhere('category', 'like', "%{$keyword}%")
                        ->orWhere('notes', 'like', "%{$keyword}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$keyword}%"));
                });
            })
            ->orderByDesc('activity_date')->orderByDesc('updated_at')
            ->limit(300);
    }

    private function dailyActivitySummary(Collection $dailyActivities): array
    {
        $total = $dailyActivities->count();

        return [
            'daily_total' => $total,
            'daily_completed' => $dailyActivities->where('status', 'completed')->count(),
            'daily_in_progress' => $dailyActivities->where('status', 'in_progress')->count(),
            'daily_blocked' => $dailyActivities->where('status', 'blocked')->count(),
            'daily_completion_rate' => $total ? round($dailyActivities->where('status', 'completed')->count() / $total * 100, 1) : 0,
        ];
    }

    private function validateFilters(Request $request): void
    {
        $request->validate([
            'form_id' => ['nullable', 'integer', 'exists:checklist_forms,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'search' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'in:daily_activity,ticketing'],
            'category' => ['nullable', 'in:'.implode(',', DailyActivity::CATEGORIES)],
        ]);
    }

    private function summarizeGroup(Collection $submissions): array
    {
        $flagged = $submissions->filter(fn ($submission) => $submission->answers->contains('is_flagged', true))->count();
        $expected = $submissions->sum(fn ($submission) => $submission->form?->items->count() ?? 0);
        $answered = $submissions->sum(fn ($submission) => $submission->answers->filter(fn ($answer) => filled($answer->answer_value))->count());

        return [
            'total' => $submissions->count(),
            'flagged' => $flagged,
            'answers' => $answered,
            'completion_rate' => $expected ? round($answered / $expected * 100, 1) : 0,
        ];
    }
}
