<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\DailyActivity;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DailyActivityController extends Controller
{
    public function index(Request $request): View
    {
        $selectedDate = Carbon::parse($request->input('date', today()->toDateString()))->toDateString();
        $activities = Auth::user()->dailyActivities()
            ->whereDate('activity_date', $selectedDate)
            ->latest()
            ->get();
        $editingActivityId = $request->integer('edit');

        return view('user.daily-activities.index', compact('activities', 'selectedDate', 'editingActivityId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        Auth::user()->dailyActivities()->create($data);

        return redirect()->route('user.daily-activities.index', ['date' => $data['activity_date']])
            ->with('success', 'Aktivitas harian berhasil ditambahkan.');
    }

    public function update(Request $request, DailyActivity $dailyActivity): RedirectResponse
    {
        $this->ensureOwnership($dailyActivity);
        $data = $request->validate([
            'status' => 'required|in:completed,in_progress,blocked',
            'notes' => 'nullable|string|max:2000',
        ]);
        $dailyActivity->update($data);

        return redirect()->route('user.daily-activities.index', ['date' => $dailyActivity->activity_date->toDateString()])
            ->with('success', 'Aktivitas harian berhasil diperbarui.');
    }

    public function destroy(DailyActivity $dailyActivity): RedirectResponse
    {
        $this->ensureOwnership($dailyActivity);
        abort_if($dailyActivity->isAssigned(), 403, 'Tugas dari admin tidak dapat dihapus.');
        $date = $dailyActivity->activity_date->toDateString();
        $dailyActivity->delete();

        return redirect()->route('user.daily-activities.index', ['date' => $date])
            ->with('success', 'Aktivitas harian berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'activity_date' => 'required|date|before_or_equal:today',
            'activity' => 'required|string|max:255',
            'status' => 'required|in:completed,in_progress,blocked',
            'notes' => 'nullable|string|max:2000',
        ]);
    }

    private function ensureOwnership(DailyActivity $dailyActivity): void
    {
        abort_unless($dailyActivity->user_id === Auth::id(), 403);
    }
}
