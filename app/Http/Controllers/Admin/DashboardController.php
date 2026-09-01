<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChecklistForm;
use App\Models\ChecklistSubmission;
use App\Models\DailyActivity;
use App\Models\FormAssignment;
use App\Models\User;
use App\Services\SchedulerService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $trendPeriod = request('trend_period', 'week');
        if (! in_array($trendPeriod, ['week', 'month', 'year'], true)) {
            $trendPeriod = 'week';
        }

        $cacheKey = 'dashboard_cache_' . $today->toDateString() . '_' . $trendPeriod;
        // Keep dashboard payload cached longer so moving between admin menus does not
        // repeatedly execute expensive aggregate queries.
        $cacheTtl = 900;

        $cachedData = cache()->remember($cacheKey, $cacheTtl, function () use ($today, $trendPeriod) {
            $startOfWeek = Carbon::now()->startOfWeek();
            $endOfWeek = Carbon::now()->endOfWeek();

            // Statistik monitoring hari ini - gunakan select minimal
            $todayAssignments = FormAssignment::where(function ($query) use ($today) {
                $query->whereDate('assigned_at', '<=', $today)
                    ->orWhereNull('assigned_at');
            })->count();
            
            $todaySubmissions = ChecklistSubmission::where('status', 'submitted')
                ->whereDate('submission_date', $today)
                ->count();
            $pendingSubmissions = max($todayAssignments - $todaySubmissions, 0);

            $dailyActivityStats = DailyActivity::whereDate('activity_date', $today)
                ->selectRaw('status, count(*) as cnt')
                ->groupBy('status')
                ->pluck('cnt', 'status');

            $dailyActivityCompleted = (int) $dailyActivityStats->get('completed', 0);
            $dailyActivityInProgress = (int) $dailyActivityStats->get('in_progress', 0);
            $dailyActivityBlocked = (int) $dailyActivityStats->get('blocked', 0);
            $dailyActivityTotal = $dailyActivityCompleted + $dailyActivityInProgress + $dailyActivityBlocked;

            // Compliance rate minggu ini
            $weekSubmissions = ChecklistSubmission::where('status', 'submitted')
                ->whereBetween('submission_date', [$startOfWeek, $endOfWeek])
                ->count();

            $weekAssignments = FormAssignment::where(function ($q) use ($today) {
                $q->whereDate('assigned_at', '<=', $today)
                    ->orWhereNull('assigned_at');
            })->count();

            $complianceRate = $weekAssignments > 0
                ? round(($weekSubmissions / $weekAssignments) * 100, 1)
                : 0;

            // Issues today (flagged answers)
            $issuesToday = DB::table('checklist_submissions as cs')
                ->join('submission_answers as sa', 'cs.id', '=', 'sa.submission_id')
                ->whereDate('cs.submission_date', $today)
                ->where('sa.is_flagged', true)
                ->distinct('cs.id')
                ->count('cs.id');

            // Recent submissions terbaru
            $recentSubmissions = ChecklistSubmission::query()
                ->select(['id', 'form_id', 'submitted_by', 'status', 'submission_date', 'submitted_at'])
                ->with(['form:id,title', 'submitter:id,name'])
                ->withCount([
                    'answers as flagged_answers_count' => fn ($query) => $query->where('is_flagged', true),
                ])
                ->latest('submitted_at')
                ->limit(10)
                ->get();

            return compact(
                'today', 'trendPeriod', 'startOfWeek', 'endOfWeek',
                'todayAssignments', 'todaySubmissions', 'pendingSubmissions',
                'dailyActivityCompleted', 'dailyActivityInProgress', 'dailyActivityBlocked', 'dailyActivityTotal',
                'complianceRate', 'issuesToday', 'recentSubmissions', 'weekSubmissions'
            );
        });

        extract($cachedData);
        $today = $cachedData['today'];
        $trendPeriod = $cachedData['trendPeriod'];
        $startOfWeek = $cachedData['startOfWeek'];
        $endOfWeek = $cachedData['endOfWeek'];

        // Add missing variables for view. Cache these totals because they are shown
        // on every dashboard load and do not need second-by-second freshness.
        $summaryTotals = cache()->remember('dashboard_summary_totals', 900, fn () => [
            'totalForms' => ChecklistForm::count(),
            'totalSubmissions' => ChecklistSubmission::where('status', 'submitted')->count(),
            'totalUsers' => User::where('role', 'user')->count(),
        ]);

        $totalForms = $summaryTotals['totalForms'];
        $totalSubmissions = $summaryTotals['totalSubmissions'];
        $totalUsers = $summaryTotals['totalUsers'];

        $trendLabels = [];
        $dailyActivityTrendData = [];

        if ($trendPeriod === 'week') {
            $trendStart = $today->copy()->startOfWeek();
            $trendEnd = $today->copy()->endOfWeek();

            $dailyActivityCounts = DailyActivity::query()
                ->selectRaw('activity_date, count(*) as total')
                ->whereBetween('activity_date', [$trendStart, $trendEnd])
                ->groupBy('activity_date')
                ->pluck('total', 'activity_date');

            for ($i = 0; $i < 7; $i++) {
                $date = $trendStart->copy()->addDays($i);
                $trendLabels[] = $date->format('D, d M');
                $dailyActivityTrendData[] = (int) $dailyActivityCounts->get($date->toDateString(), 0);
            }
        } elseif ($trendPeriod === 'month') {
            $trendStart = $today->copy()->startOfMonth();
            $trendEnd = $today->copy()->endOfMonth();

            $dailyActivityCounts = DailyActivity::query()
                ->selectRaw('activity_date, count(*) as total')
                ->whereBetween('activity_date', [$trendStart, $trendEnd])
                ->groupBy('activity_date')
                ->pluck('total', 'activity_date');

            for ($date = $trendStart->copy(); $date->lte($trendEnd); $date->addDay()) {
                $trendLabels[] = $date->format('d M');
                $dailyActivityTrendData[] = (int) $dailyActivityCounts->get($date->toDateString(), 0);
            }
        } else {
            $trendStart = $today->copy()->subMonthsNoOverflow(11)->startOfMonth();
            $trendEnd = $today->copy()->endOfMonth();

            $monthlyActivityCounts = DailyActivity::query()
                ->selectRaw('DATE_FORMAT(activity_date, "%Y-%m") as period, count(*) as total')
                ->whereBetween('activity_date', [$trendStart, $trendEnd])
                ->groupBy('period')
                ->pluck('total', 'period');

            for ($date = $trendStart->copy()->startOfMonth(); $date->lte($trendEnd); $date->addMonth()) {
                $period = $date->format('Y-m');
                $trendLabels[] = $date->format('M Y');
                $dailyActivityTrendData[] = (int) $monthlyActivityCounts->get($period, 0);
            }
        }

        // Data submissions 30 hari terakhir (untuk line chart)
        $chartStart = $today->copy()->subDays(29);
        $dailySubmissionCounts = ChecklistSubmission::query()
            ->selectRaw('submission_date, count(*) as total')
            ->where('status', 'submitted')
            ->whereBetween('submission_date', [$chartStart, $today])
            ->groupBy('submission_date')
            ->pluck('total', 'submission_date');

        $weeklyComplianceData = [];
        for ($i = 3; $i >= 0; $i--) {
            $week = $startOfWeek->copy()->subWeeks($i);
            $weeklyComplianceData[] = $dailySubmissionCounts
                ->filter(fn ($total, $date) => Carbon::parse($date)->betweenIncluded($week, $week->copy()->endOfWeek()))
                ->sum();
        }

        $dailySubmissionsData = [];
        $dailyLabels = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $dailyLabels[] = $date->format('M d');
            $dailySubmissionsData[] = $dailySubmissionCounts->get($date->toDateString(), 0);
        }

        // Data form usage (top 5 forms)
        $chartSummaryData = cache()->remember('dashboard_chart_summary_data', 900, function () {
            $formUsageData = ChecklistSubmission::query()
                ->select('form_id', DB::raw('count(*) as total'))
                ->with('form:id,title')
                ->where('status', 'submitted')
                ->groupBy('form_id')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get()
                ->map(fn ($item) => [
                    'label' => $item->form->title ?? 'Unknown',
                    'value' => $item->total,
                ]);

            $userActivityData = ChecklistSubmission::query()
                ->select('submitted_by', DB::raw('count(*) as total'))
                ->with('submitter:id,name')
                ->where('status', 'submitted')
                ->groupBy('submitted_by')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get()
                ->map(fn ($item) => [
                    'label' => $item->submitter->name ?? 'Unknown',
                    'value' => $item->total,
                ]);

            $dailyTaskByUserData = DailyActivity::query()
                ->select('user_id', DB::raw('count(*) as total'))
                ->with('user:id,name')
                ->groupBy('user_id')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get()
                ->map(fn ($item) => [
                    'label' => $item->user->name ?? 'Unknown',
                    'value' => $item->total,
                ]);

            $issuesByFormData = ChecklistSubmission::query()
                ->select('form_id', DB::raw('count(distinct checklist_submissions.id) as total'))
                ->join('submission_answers as sa', 'checklist_submissions.id', '=', 'sa.submission_id')
                ->with('form:id,title')
                ->where('sa.is_flagged', true)
                ->groupBy('form_id')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get()
                ->map(fn ($item) => [
                    'label' => $item->form->title ?? 'Unknown',
                    'value' => $item->total,
                ]);

            return compact('formUsageData', 'userActivityData', 'dailyTaskByUserData', 'issuesByFormData');
        });

        $formUsageData = $chartSummaryData['formUsageData'];
        $userActivityData = $chartSummaryData['userActivityData'];
        $dailyTaskByUserData = $chartSummaryData['dailyTaskByUserData'];
        $issuesByFormData = $chartSummaryData['issuesByFormData'];

        // Data status overview (pie chart)
        $statusData = [
            'submit' => [
                'completed' => $todaySubmissions,
                'pending' => $pendingSubmissions,
            ],
            'daily' => [
                'completed' => $dailyActivityCompleted,
                'in_progress' => $dailyActivityInProgress,
                'blocked' => $dailyActivityBlocked,
            ],
        ];

        $dashboardMetrics = [
            'todaySubmissions' => $todaySubmissions,
            'todayAssignments' => $todayAssignments,
            'pendingSubmissions' => $pendingSubmissions,
            'submissionProgress' => $todayAssignments > 0 ? round(($todaySubmissions / $todayAssignments) * 100, 1) : 0,
            'dailyActivityTotal' => $dailyActivityTotal,
            'dailyActivityCompleted' => $dailyActivityCompleted,
            'dailyActivityInProgress' => $dailyActivityInProgress,
            'dailyActivityBlocked' => $dailyActivityBlocked,
            'dailyActivityProgress' => $dailyActivityTotal > 0 ? round(($dailyActivityCompleted / $dailyActivityTotal) * 100, 1) : 0,
            'attentionCount' => $pendingSubmissions + $dailyActivityBlocked + $issuesToday,
        ];

        // Upcoming scheduled forms (next 7 days)
        $upcomingForms = cache()->remember(
            'dashboard_upcoming_forms_' . $today->toDateString(),
            1800,
            fn () => $this->getUpcomingScheduledForms()
        );
        $activityFeed = cache()->remember(
            'dashboard_activity_feed_snapshot_' . $today->toDateString(),
            45,
            fn () => $this->activityFeed()
        );

        return view('admin.dashboard', compact(
            'totalForms',
            'totalSubmissions',
            'totalUsers',
            'complianceRate',
            'issuesToday',
            'recentSubmissions',
            'weeklyComplianceData',
            'dailySubmissionsData',
            'dailyLabels',
            'trendPeriod',
            'trendLabels',
            'dailyActivityTrendData',
            'formUsageData',
            'userActivityData',
            'dailyTaskByUserData',
            'issuesByFormData',
            'statusData',
            'dashboardMetrics',
            'upcomingForms',
            'activityFeed'
        ));
    }

    public function activityMonitor()
    {
        // This endpoint is polled by the dashboard and must reflect status changes
        // immediately instead of serving a stale dashboard snapshot.
        return response()->json([
            'data' => $this->activityFeed(),
            'generatedAt' => now()->toIso8601String(),
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function dashboardMetrics()
    {
        return response()->json((function () {
            $today = Carbon::today();
            $startOfWeek = Carbon::now()->startOfWeek();

            $todayAssignments = FormAssignment::where(function ($query) use ($today) {
                $query->whereDate('assigned_at', '<=', $today)
                    ->orWhereNull('assigned_at');
            })->count();

            $todaySubmissions = ChecklistSubmission::where('status', 'submitted')
                ->whereDate('submission_date', $today)
                ->count();

            $pendingSubmissions = max($todayAssignments - $todaySubmissions, 0);

            $dailyActivityStats = DailyActivity::whereDate('activity_date', $today)
                ->selectRaw('status, count(*) as cnt')
                ->groupBy('status')
                ->pluck('cnt', 'status');

            $dailyActivityCompleted = (int) $dailyActivityStats->get('completed', 0);
            $dailyActivityInProgress = (int) $dailyActivityStats->get('in_progress', 0);
            $dailyActivityBlocked = (int) $dailyActivityStats->get('blocked', 0);
            $dailyActivityTotal = $dailyActivityCompleted + $dailyActivityInProgress + $dailyActivityBlocked;

            $issuesToday = DB::table('checklist_submissions as cs')
                ->join('submission_answers as sa', 'cs.id', '=', 'sa.submission_id')
                ->whereDate('cs.submission_date', $today)
                ->where('sa.is_flagged', true)
                ->distinct('cs.id')
                ->count('cs.id');

            return [
                'todaySubmissions' => $todaySubmissions,
                'todayAssignments' => $todayAssignments,
                'pendingSubmissions' => $pendingSubmissions,
                'submissionProgress' => $todayAssignments > 0 ? round(($todaySubmissions / $todayAssignments) * 100, 1) : 0,
                'dailyActivityTotal' => $dailyActivityTotal,
                'dailyActivityCompleted' => $dailyActivityCompleted,
                'dailyActivityInProgress' => $dailyActivityInProgress,
                'dailyActivityBlocked' => $dailyActivityBlocked,
                'dailyActivityProgress' => $dailyActivityTotal > 0 ? round(($dailyActivityCompleted / $dailyActivityTotal) * 100, 1) : 0,
                'attentionCount' => $pendingSubmissions + $dailyActivityBlocked + $issuesToday,
                'generatedAt' => now()->toIso8601String(),
            ];
        })())->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function cardDetails(string $card)
    {
        $today = Carbon::today();

        return (match ($card) {
            'forms' => response()->json($this->formCardDetails($today)),
            'daily' => response()->json($this->dailyCardDetails($today)),
            'attention' => response()->json($this->attentionCardDetails($today)),
            'issues' => response()->json($this->issueCardDetails($today)),
            default => abort(404),
        })->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    private function formCardDetails(Carbon $today): array
    {
        $submissions = ChecklistSubmission::query()
            ->select(['id', 'form_id', 'submitted_by', 'status', 'submitted_at', 'submission_date'])
            ->with(['form:id,title', 'submitter:id,name'])
            ->where('status', 'submitted')
            ->whereDate('submission_date', $today)
            ->latest('submitted_at')
            ->limit(50)
            ->get()
            ->map(fn (ChecklistSubmission $submission) => [
                'user' => $submission->submitter?->name ?? '-',
                'item' => $submission->form?->title ?? 'Form Checklist',
                'status' => 'submitted',
                'updated_label' => $submission->submitted_at?->format('d M Y, H:i') ?? '-',
            ]);

        $assigned = FormAssignment::query()
            ->where(function ($query) use ($today) {
                $query->whereDate('assigned_at', '<=', $today)->orWhereNull('assigned_at');
            })->count();

        return [
            'title' => 'Detail Submit Form Hari Ini',
            'summary' => sprintf('%d form sudah disubmit dari %d assignment.', $submissions->count(), $assigned),
            'columns' => ['User', 'Form', 'Status', 'Waktu Submit'],
            'rows' => $submissions,
        ];
    }

    private function dailyCardDetails(Carbon $today): array
    {
        $activities = DailyActivity::query()
            ->select(['id', 'user_id', 'activity', 'status', 'updated_at'])
            ->with('user:id,name')
            ->whereDate('activity_date', $today)
            ->latest('updated_at')
            ->limit(50)
            ->get()
            ->map(fn (DailyActivity $activity) => [
                'user' => $activity->user?->name ?? '-',
                'item' => $activity->activity,
                'status' => $activity->status,
                'updated_label' => $activity->updated_at?->format('d M Y, H:i') ?? '-',
            ]);

        return [
            'title' => 'Detail Daily Activity Hari Ini',
            'summary' => sprintf('%d aktivitas tercatat hari ini.', $activities->count()),
            'columns' => ['User', 'Aktivitas', 'Status', 'Pembaruan Terakhir'],
            'rows' => $activities,
        ];
    }

    private function attentionCardDetails(Carbon $today): array
    {
        $blockedActivities = DailyActivity::query()
            ->select(['id', 'user_id', 'activity', 'status', 'updated_at'])
            ->with('user:id,name')
            ->whereDate('activity_date', $today)
            ->where('status', 'blocked')
            ->latest('updated_at')
            ->limit(50)
            ->get()
            ->map(fn (DailyActivity $activity) => [
                'user' => $activity->user?->name ?? '-',
                'item' => $activity->activity,
                'status' => 'blocked',
                'updated_label' => $activity->updated_at?->format('d M Y, H:i') ?? '-',
            ]);

        $flaggedSubmissions = $this->flaggedSubmissionRows($today);
        $rows = $blockedActivities->concat($flaggedSubmissions)->values();

        return [
            'title' => 'Detail Perlu Perhatian',
            'summary' => sprintf('%d item perlu tindak lanjut hari ini.', $rows->count()),
            'columns' => ['User', 'Aktivitas / Form', 'Status', 'Pembaruan Terakhir'],
            'rows' => $rows,
        ];
    }

    private function issueCardDetails(Carbon $today): array
    {
        $blockedActivities = DailyActivity::query()
            ->select(['id', 'user_id', 'activity', 'status', 'updated_at'])
            ->with('user:id,name')
            ->whereDate('activity_date', $today)
            ->where('status', 'blocked')
            ->latest('updated_at')
            ->limit(50)
            ->get()
            ->map(fn (DailyActivity $activity) => [
                'user' => $activity->user?->name ?? '-',
                'item' => $activity->activity,
                'status' => 'blocked',
                'updated_label' => $activity->updated_at?->format('d M Y, H:i') ?? '-',
            ]);

        $rows = $blockedActivities->concat($this->flaggedSubmissionRows($today))->values();

        return [
            'title' => 'Detail Daily Activity Terhambat',
            'summary' => sprintf('%d aktivitas atau submit form terhambat hari ini.', $rows->count()),
            'columns' => ['User', 'Aktivitas / Form', 'Status', 'Pembaruan Terakhir'],
            'rows' => $rows,
        ];
    }

    private function flaggedSubmissionRows(Carbon $today)
    {
        return ChecklistSubmission::query()
            ->select(['checklist_submissions.id', 'form_id', 'submitted_by', 'submitted_at', 'updated_at'])
            ->with(['form:id,title', 'submitter:id,name'])
            ->whereDate('submission_date', $today)
            ->whereHas('answers', fn ($query) => $query->where('is_flagged', true))
            ->withCount(['answers as flagged_answers_count' => fn ($query) => $query->where('is_flagged', true)])
            ->latest('updated_at')
            ->limit(50)
            ->get()
            ->map(fn (ChecklistSubmission $submission) => [
                'user' => $submission->submitter?->name ?? '-',
                'item' => sprintf('%s (%d issue)', $submission->form?->title ?? 'Form Checklist', $submission->flagged_answers_count),
                'status' => 'issue',
                'updated_label' => ($submission->submitted_at ?: $submission->updated_at)?->format('d M Y, H:i') ?? '-',
            ]);
    }

    private function activityFeed()
    {
        $activities = DailyActivity::query()
            ->select(['id', 'user_id', 'activity', 'status', 'updated_at'])
            ->with(['user:id,name'])
            ->latest('updated_at')
            ->limit(15)
            ->get()
            ->map(fn ($activity) => [
                'id' => 'daily-'.$activity->id,
                'user' => $activity->user?->name ?? '-',
                'activity' => $activity->activity,
                'type' => 'Daily Activity',
                'status' => $activity->status,
                'updated_at' => $activity->updated_at?->toIso8601String(),
                'updated_label' => $activity->updated_at?->format('d M Y, H:i'),
            ]);

        $submissions = ChecklistSubmission::query()
            ->select(['id', 'form_id', 'submitted_by', 'status', 'submitted_at', 'updated_at'])
            ->with(['form:id,title', 'submitter:id,name'])
            ->latest('updated_at')
            ->limit(15)
            ->get()
            ->map(fn ($submission) => [
                'id' => 'submission-'.$submission->id,
                'user' => $submission->submitter?->name ?? '-',
                'activity' => $submission->form?->title ?? 'Form Checklist',
                'type' => 'Submit Form',
                'status' => $submission->status,
                'updated_at' => ($submission->submitted_at ?: $submission->updated_at)?->toIso8601String(),
                'updated_label' => ($submission->submitted_at ?: $submission->updated_at)?->format('d M Y, H:i'),
            ]);

        return $activities->concat($submissions)->sortByDesc('updated_at')->values()->take(15);
    }

    /**
     * Get upcoming scheduled forms for the next 7 days
     */
    private function getUpcomingScheduledForms()
    {
        $scheduler = new SchedulerService;
        $upcomingForms = [];

        // Get all users with role 'user'
        $users = User::query()
            ->select(['id', 'name'])
            ->where('role', 'user')
            ->whereHas('assignments')
            ->orderBy('name')
            ->limit(100)
            ->get();
        $assignmentsByUser = FormAssignment::query()
            ->select(['id', 'user_id', 'form_id'])
            ->whereIn('user_id', $users->pluck('id'))
            ->with(['form:id,title,is_active,start_date,end_date,schedule_type,schedule_days,schedule_interval'])
            ->get()
            ->groupBy('user_id');
        $startDate = Carbon::today();
        $endDate = $startDate->copy()->addDays(6);
        $submittedFormKeys = ChecklistSubmission::query()
            ->select(['form_id', 'submitted_by', 'submission_date'])
            ->where('status', 'submitted')
            ->whereBetween('submission_date', [$startDate, $endDate])
            ->get()
            ->mapWithKeys(fn ($submission) => [
                $submission->form_id.'-'.$submission->submitted_by.'-'.$submission->submission_date->toDateString() => true,
            ]);

        // Check next 7 days for each user
        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i);

            foreach ($users as $user) {
                $formsDue = $scheduler->getFormsDueOnDate($user, $date, $assignmentsByUser->get($user->id, collect()));

                foreach ($formsDue as $form) {
                    // Check if already submitted today
                    $alreadySubmitted = $submittedFormKeys->has($form->id.'-'.$user->id.'-'.$date->toDateString());

                    if (! $alreadySubmitted) {
                        $upcomingForms[] = [
                            'form' => $form,
                            'user' => $user,
                            'date' => $date,
                            'is_today' => $i === 0,
                            'is_overdue' => $i === 0 && $date->isPast(),
                        ];
                    }
                }
            }
        }

        // Sort by date and user
        usort($upcomingForms, function ($a, $b) {
            if ($a['date']->equalTo($b['date'])) {
                return strcmp($a['user']->name, $b['user']->name);
            }

            return $a['date']->greaterThan($b['date']) ? 1 : -1;
        });

        // Limit to 10 items to reduce dashboard render cost
        return array_slice($upcomingForms, 0, 10);
    }
}
