<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChecklistForm;
use App\Models\ChecklistSubmission;
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
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek   = Carbon::now()->endOfWeek();

        // Statistik kartu
        $totalForms       = ChecklistForm::count();
        $totalSubmissions = ChecklistSubmission::where('status', 'submitted')->count();
        $totalUsers       = User::where('role', 'user')->count();

        // Compliance rate minggu ini
        $weekSubmissions = ChecklistSubmission::where('status', 'submitted')
            ->whereBetween('submission_date', [$startOfWeek, $endOfWeek])
            ->count();

        $weekAssignments = DB::table('form_assignments')
            ->where(function ($q) use ($today) {
                $q->whereDate('assigned_at', '<=', $today)
                    ->orWhereNull('assigned_at');
            })
            ->count();

        $complianceRate = $weekAssignments > 0
            ? round(($weekSubmissions / $weekAssignments) * 100, 1)
            : 0;

        // Issues today (flagged answers)
        $issuesToday = ChecklistSubmission::whereDate('submission_date', $today)
            ->whereHas('answers', fn($q) => $q->where('is_flagged', true))
            ->count();

        // Recent submissions terbaru
        $recentSubmissions = ChecklistSubmission::with(['form', 'submitter'])
            ->latest('submitted_at')
            ->limit(10)
            ->get();

        // Data chart compliance 4 minggu terakhir
        $weeklyComplianceData = [];
        for ($i = 3; $i >= 0; $i--) {
            $ws = Carbon::now()->subWeeks($i)->startOfWeek();
            $we = Carbon::now()->subWeeks($i)->endOfWeek();

            $weeklyComplianceData[] = ChecklistSubmission::where('status', 'submitted')
                ->whereBetween('submission_date', [$ws, $we])
                ->count();
        }

        // Data submissions 30 hari terakhir (untuk line chart)
        $dailySubmissionsData = [];
        $dailyLabels = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dailyLabels[] = $date->format('M d');
            $dailySubmissionsData[] = ChecklistSubmission::where('status', 'submitted')
                ->whereDate('submission_date', $date)
                ->count();
        }

        // Data form usage (top 5 forms)
        $formUsageData = ChecklistSubmission::select('form_id', DB::raw('count(*) as total'))
            ->with('form')
            ->where('status', 'submitted')
            ->groupBy('form_id')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $item->form->title ?? 'Unknown',
                    'value' => $item->total
                ];
            });

        // Data user activity (top 5 users)
        $userActivityData = ChecklistSubmission::select('submitted_by', DB::raw('count(*) as total'))
            ->with('submitter')
            ->where('status', 'submitted')
            ->groupBy('submitted_by')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $item->submitter->name ?? 'Unknown',
                    'value' => $item->total
                ];
            });

        // Data issues by form (horizontal bar chart)
        $issuesByFormData = ChecklistSubmission::select('form_id', DB::raw('count(*) as total'))
            ->with('form')
            ->whereHas('answers', fn($q) => $q->where('is_flagged', true))
            ->groupBy('form_id')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $item->form->title ?? 'Unknown',
                    'value' => $item->total
                ];
            });

        // Data status overview (pie chart)
        $statusData = [
            'ok' => $totalSubmissions - $issuesToday,
            'issues' => $issuesToday
        ];

        // Upcoming scheduled forms (next 7 days)
        $upcomingForms = $this->getUpcomingScheduledForms();

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
            'formUsageData',
            'userActivityData',
            'issuesByFormData',
            'statusData',
            'upcomingForms'
        ));
    }

    /**
     * Get upcoming scheduled forms for the next 7 days
     */
    private function getUpcomingScheduledForms()
    {
        $scheduler = new SchedulerService();
        $upcomingForms = [];

        // Get all users with role 'user'
        $users = User::where('role', 'user')->get();

        // Check next 7 days for each user
        for ($i = 0; $i < 7; $i++) {
            $date = Carbon::today()->addDays($i);

            foreach ($users as $user) {
                $formsDue = $scheduler->getFormsDueToday($user);

                foreach ($formsDue as $form) {
                    // Check if already submitted today
                    $alreadySubmitted = ChecklistSubmission::where('form_id', $form->id)
                        ->where('submitted_by', $user->id)
                        ->whereDate('submission_date', $date)
                        ->where('status', 'submitted')
                        ->exists();

                    if (!$alreadySubmitted) {
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

        // Limit to 20 items
        return array_slice($upcomingForms, 0, 20);
    }
}
