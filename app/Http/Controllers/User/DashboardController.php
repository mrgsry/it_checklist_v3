<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ChecklistSubmission;
use App\Models\DailyActivity;
use App\Models\FormAssignment;
use App\Services\SchedulerService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today();

        // Form yang harus diisi hari ini
        $scheduler = app(SchedulerService::class);
        $assignments = FormAssignment::where('user_id', $user->id)->with('form')->get();
        $formsDue = $scheduler->getFormsDueToday($user, $assignments);

        // Form yang sudah diisi hari ini
        $submittedToday = ChecklistSubmission::where('submitted_by', $user->id)
            ->whereDate('submission_date', $today)
            ->where('status', 'submitted')
            ->with('form')
            ->get();

        $dailyActivitiesToday = DailyActivity::where('user_id', $user->id)
            ->whereDate('activity_date', $today)
            ->latest()
            ->get();

        $dailyActivityTotal = $dailyActivitiesToday->count();
        $dailyActivityCompleted = $dailyActivitiesToday->where('status', 'completed')->count();
        $dailyActivityInProgress = $dailyActivitiesToday->where('status', 'in_progress')->count();
        $dailyActivityBlocked = $dailyActivitiesToday->where('status', 'blocked')->count();
        $formTotal = count($formsDue);
        $formCompleted = $submittedToday->count();

        // Ringkasan tujuh hari membantu user melihat ritme kerja, bukan hanya kondisi hari ini.
        $weekStart = $today->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
        $weekSubmissions = ChecklistSubmission::where('submitted_by', $user->id)
            ->whereBetween('submission_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->where('status', 'submitted')
            ->get(['form_id', 'submission_date']);
        $weekActivities = DailyActivity::where('user_id', $user->id)
            ->whereBetween('activity_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get(['activity_date', 'status', 'activity']);

        $weeklyTaskDays = collect();
        for ($date = $weekStart->copy(); $date->lte($weekEnd); $date->addDay()) {
            $dateKey = $date->toDateString();
            $forms = $scheduler->getFormsDueOnDate($user, $date, $assignments);
            $submittedFormIds = $weekSubmissions->where('submission_date', $dateKey)->pluck('form_id');
            $activities = $weekActivities->filter(fn ($activity) => $activity->activity_date?->toDateString() === $dateKey);
            $total = count($forms) + $activities->count();
            $completed = $forms ? collect($forms)->filter(fn ($form) => $submittedFormIds->contains($form->id))->count() : 0;
            $completed += $activities->where('status', 'completed')->count();

            $weeklyTaskDays->push([
                'date' => $date->copy(),
                'total' => $total,
                'completed' => $completed,
                'progress' => $this->percentage($completed, $total),
                'has_blocked' => $activities->where('status', 'blocked')->isNotEmpty(),
                'activities' => $activities,
            ]);
        }

        $weeklyTotal = $weeklyTaskDays->sum('total');
        $weeklyCompleted = $weeklyTaskDays->sum('completed');
        $weeklyProgress = $this->percentage($weeklyCompleted, $weeklyTotal);
        $attentionCount = $formTotal - $formCompleted + $dailyActivityBlocked;

        // Statistik
        $totalThisMonth = ChecklistSubmission::where('submitted_by', $user->id)
            ->whereMonth('submission_date', $today->month)
            ->where('status', 'submitted')
            ->count();

        $streak = $this->getSubmissionStreak($user->id, $today);

        $pendingCount = count($formsDue) - $submittedToday->count();
        if ($pendingCount < 0) {
            $pendingCount = 0;
        }

        return view('user.dashboard', compact(
            'formsDue', 'submittedToday', 'totalThisMonth', 'streak', 'today', 'pendingCount',
            'dailyActivitiesToday', 'dailyActivityTotal', 'dailyActivityCompleted',
            'dailyActivityInProgress', 'dailyActivityBlocked', 'formTotal', 'formCompleted',
            'weeklyTaskDays', 'weeklyTotal', 'weeklyCompleted', 'weeklyProgress', 'attentionCount'
        ));
    }

    private function percentage(int $completed, int $total): int
    {
        return $total > 0 ? (int) round(($completed / $total) * 100) : 0;
    }

    private function getSubmissionStreak(int $userId, Carbon $today): int
    {
        $submissionDates = ChecklistSubmission::query()
            ->where('submitted_by', $userId)
            ->where('status', 'submitted')
            ->whereBetween('submission_date', [$today->copy()->subDays(365), $today])
            ->select('submission_date')
            ->distinct()
            ->pluck('submission_date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->flip();

        $streak = 0;
        for ($date = $today->copy(); $submissionDates->has($date->toDateString()); $date->subDay()) {
            $streak++;
        }

        return $streak;
    }
}
