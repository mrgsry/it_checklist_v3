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
        $data = $this->reportData($request);

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

    private function reportData(Request $request): array
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
            ->limit(500); // Limit hasil untuk performance

        $submissions = $query->orderByDesc('submission_date')->orderByDesc('submitted_at')->get();
        $dailyActivities = $this->dailyActivitiesQuery($request)->get();
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

        $flaggedSubmissions = $submissions->filter(fn ($submission) => $submission->answers->contains('is_flagged', true));
        $answeredCount = $submissions->sum(fn ($submission) => $submission->answers->filter(fn ($answer) => filled($answer->answer_value))->count());
        $expectedCount = $submissions->sum(fn ($submission) => $submission->form?->items->count() ?? 0);

        $summaryStats = [
            'total' => $submissions->count(),
            'flagged' => $flaggedSubmissions->count(),
            'clean' => $submissions->count() - $flaggedSubmissions->count(),
            'flagged_rate' => $submissions->count() ? round($flaggedSubmissions->count() / $submissions->count() * 100, 1) : 0,
            'answers' => $answeredCount,
            'expected_answers' => $expectedCount,
            'completion_rate' => $expectedCount ? round($answeredCount / $expectedCount * 100, 1) : 0,
            'flagged_answers' => $submissions->sum(fn ($submission) => $submission->answers->where('is_flagged', true)->count()),
            ...$this->dailyActivitySummary($dailyActivities),
        ];

        $formSummary = $submissions->groupBy('form_id')->map(fn (Collection $items) => $this->summarizeGroup($items));
        $userSummary = $submissions->groupBy('submitted_by')->map(fn (Collection $items) => $this->summarizeGroup($items));

        return compact('forms', 'users', 'submissions', 'dailyActivities', 'selectedForm', 'selectedUser', 'reportPeriod', 'logoDataUri', 'summaryStats', 'formSummary', 'userSummary');
    }

    private function buildStreamPayload(Request $request): array
    {
        $dailyActivities = $this->dailyActivitiesQuery($request)->get();
        $summaryStats = $this->dailyActivitySummary($dailyActivities);

        return [
            'summaryStats' => $summaryStats,
            'dailyActivities' => $dailyActivities->map(fn ($activity) => [
                'id' => $activity->id,
                'date' => $activity->activity_date?->format('d/m/Y'),
                'staff' => $activity->user?->name ?? '-',
                'activity' => $activity->activity,
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
