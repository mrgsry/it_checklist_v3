<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChecklistForm;
use App\Models\ChecklistSubmission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $forms = ChecklistForm::orderBy('title')->get();
        $users = User::where('role', 'user')->orderBy('name')->get();

        $submissions = collect();
        $selectedForm = null;
        $summaryStats = [];

        if ($request->filled('form_id')) {
            $selectedForm = ChecklistForm::with('items')->findOrFail($request->form_id);

            $query = ChecklistSubmission::with(['submitter', 'answers.formItem'])
                ->where('form_id', $request->form_id)
                ->where('status', 'submitted');

            if ($request->filled('date_from')) {
                $query->whereDate('submission_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('submission_date', '<=', $request->date_to);
            }
            if ($request->filled('user_id')) {
                $query->where('submitted_by', $request->user_id);
            }

            $submissions = $query->orderBy('submission_date', 'desc')->get();

            // Hitung summary
            $totalSubmissions = $submissions->count();
            $flaggedCount     = $submissions->filter(fn($s) => $s->answers->where('is_flagged', true)->count() > 0)->count();

            $summaryStats = [
                'total'         => $totalSubmissions,
                'flagged'       => $flaggedCount,
                'clean'         => $totalSubmissions - $flaggedCount,
                'flagged_rate'  => $totalSubmissions > 0
                    ? round(($flaggedCount / $totalSubmissions) * 100, 1) : 0,
            ];
        }

        return view('admin.reports.index', compact(
            'forms', 'users', 'submissions', 'selectedForm', 'summaryStats'
        ));
    }

    public function exportExcel(Request $request)
    {
        // Akan diimplementasi di Phase 2 dengan Maatwebsite Excel
        return back()->with('error', 'Fitur export akan segera tersedia.');
    }
}