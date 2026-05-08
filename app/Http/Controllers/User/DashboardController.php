<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\SchedulerService;
use App\Models\ChecklistSubmission;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user  = auth()->user();
        $today = Carbon::today();

        // Form yang harus diisi hari ini
        $formsDue = app(SchedulerService::class)->getFormsDueToday($user);

        // Form yang sudah diisi hari ini
        $submittedToday = ChecklistSubmission::where('submitted_by', $user->id)
            ->whereDate('submission_date', $today)
            ->where('status', 'submitted')
            ->with('form')
            ->get();

        // Statistik
        $totalThisMonth = ChecklistSubmission::where('submitted_by', $user->id)
            ->whereMonth('submission_date', $today->month)
            ->where('status', 'submitted')
            ->count();

        $streak = $this->getSubmissionStreak($user->id);

        $pendingCount = count($formsDue) - $submittedToday->count();
        if ($pendingCount < 0) $pendingCount = 0;

        return view('user.dashboard', compact(
            'formsDue', 'submittedToday', 'totalThisMonth', 'streak', 'today', 'pendingCount'
        ));
    }

    private function getSubmissionStreak(int $userId): int
    {
        $streak = 0;
        $date   = Carbon::today();

        while (true) {
            $hasSubmission = ChecklistSubmission::where('submitted_by', $userId)
                ->whereDate('submission_date', $date)
                ->where('status', 'submitted')
                ->exists();

            if (!$hasSubmission) break;

            $streak++;
            $date->subDay();

            if ($streak > 365) break;
        }

        return $streak;
    }
}