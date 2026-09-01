<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyActivity;
use App\Models\User;
use App\Notifications\DailyActivityAssignedNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DailyActivityController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'activity_date' => 'required|date',
            'activity' => 'required|string|max:255',
            'category' => ['nullable', 'in:'.implode(',', DailyActivity::CATEGORIES)],
            'notes' => 'nullable|string|max:2000',
        ]);

        $user = User::where('role', 'user')->findOrFail($data['user_id']);
        $activity = DailyActivity::create([
            ...$data,
            'assigned_by' => $request->user()->id,
            'assigned_at' => now(),
            'status' => 'in_progress',
        ]);

        $user->notify(new DailyActivityAssignedNotification($activity));

        return redirect()->route('admin.daily-activities.index')
            ->with('success', 'Daily Activity berhasil ditugaskan dan notifikasi dikirim.');
    }

    public function index(Request $request): View
    {
        $activities = $this->filteredActivities($request)
            ->paginate(20)
            ->withQueryString();

        $users = User::where('role', 'user')->orderBy('name')->get();

        return view('admin.daily-activities.index', compact('activities', 'users'));
    }

    public function exportPdf(Request $request)
    {
        $activities = $this->filteredActivities($request)->get();

        return Pdf::loadView('admin.daily-activities.pdf', compact('activities'))
            ->setPaper('a4', 'landscape')
            ->download('daily-activity-'.now()->format('Ymd-His').'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $activities = $this->filteredActivities($request)->get();

        return response()->view('admin.daily-activities.excel', compact('activities'))
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="daily-activity-'.now()->format('Ymd-His').'.xls"');
    }

    private function filteredActivities(Request $request)
    {
        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['nullable', 'in:completed,in_progress,blocked'],
            'category' => ['nullable', 'in:'.implode(',', DailyActivity::CATEGORIES)],
            'type' => ['nullable', 'in:daily_activity,ticketing'],
        ]);

        return DailyActivity::with(['user', 'assigner'])
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('activity_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('activity_date', '<=', $request->date_to))
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->category))
            ->when($request->filled('type'), fn ($query) => $query->ofType($request->type))
            ->orderByDesc('activity_date')
            ->latest();
    }
}
