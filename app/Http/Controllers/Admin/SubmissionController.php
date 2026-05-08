<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChecklistSubmission;
use App\Models\ChecklistForm;
use App\Models\User;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function index(Request $request)
    {
        $query = ChecklistSubmission::with(['form', 'submitter', 'answers'])
            ->latest('submitted_at');

        // Filter
        if ($request->filled('form_id')) {
            $query->where('form_id', $request->form_id);
        }
        if ($request->filled('user_id')) {
            $query->where('submitted_by', $request->user_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('submission_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('submission_date', '<=', $request->date_to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $submissions = $query->paginate(15)->withQueryString();
        $forms = ChecklistForm::orderBy('title')->get();
        $users = User::where('role', 'user')->orderBy('name')->get();

        return view('admin.submissions.index', compact('submissions', 'forms', 'users'));
    }

    public function show(ChecklistSubmission $submission)
    {
        $submission->load(['form.items', 'submitter', 'answers.formItem']);
        return view('admin.submissions.show', compact('submission'));
    }
}