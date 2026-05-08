<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChecklistForm;
use App\Models\FormItem;
use App\Models\FormAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FormBuilderController extends Controller
{
    public function index()
    {
        $forms = ChecklistForm::with(['creator', 'assignments', 'submissions'])
            ->latest()->paginate(10);
        return view('admin.forms.index', compact('forms'));
    }

    public function create()
    {
        $users = User::where('role', 'user')->orderBy('name')->get();
        return view('admin.forms.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'schedule_type' => 'required|in:daily,weekly,custom',
            'items'         => 'required|array|min:1',
            'items.*.label' => 'required|string|max:255',
            'items.*.field_type' => 'required|in:checkbox,radio,dropdown,text,number,textarea,signal,photo',
        ], [
            'title.required'          => 'Nama form wajib diisi.',
            'items.required'          => 'Tambahkan minimal 1 item checklist.',
            'items.*.label.required'  => 'Label item wajib diisi.',
        ]);

        // Buat form
        $form = ChecklistForm::create([
            'title'             => $request->title,
            'description'       => $request->description,
            'schedule_type'     => $request->schedule_type,
            'schedule_days'     => $request->schedule_days ?? [],
            'schedule_interval' => $request->schedule_interval,
            'start_date'        => $request->start_date,
            'end_date'          => $request->end_date,
            'is_active'         => true,
            'created_by'        => Auth::id(),
        ]);

        // Simpan items
        $items = collect($request->items)->sortBy('order_index')->values();
        foreach ($items as $index => $item) {
            $options = null;
            if (!empty($item['options_raw'])) {
                $options = array_map('trim', explode(',', $item['options_raw']));
                $options = array_filter($options);
                $options = array_values($options);
            }

            FormItem::create([
                'form_id'     => $form->id,
                'label'       => $item['label'],
                'field_type'  => $item['field_type'],
                'options'     => $options,
                'is_required' => isset($item['is_required']),
                'placeholder' => $item['placeholder'] ?? null,
                'helper_text' => $item['helper_text'] ?? null,
                'order_index' => $index,
            ]);
        }

        // Assign users
        if ($request->has('assigned_users')) {
            foreach ($request->assigned_users as $userId) {
                FormAssignment::firstOrCreate([
                    'form_id' => $form->id,
                    'user_id' => $userId,
                ]);
            }
        }

        return redirect()->route('admin.forms.index')
            ->with('success', "Form \"{$form->title}\" berhasil dibuat!");
    }

    public function show(ChecklistForm $form)
    {
        $form->load(['items', 'assignedUsers', 'submissions.submitter']);
        return view('admin.forms.show', compact('form'));
    }

    public function edit(ChecklistForm $form)
    {
        $form->load('items', 'assignedUsers');
        $users = User::where('role', 'user')->orderBy('name')->get();
        $assignedUserIds = $form->assignedUsers->pluck('id')->toArray();
        return view('admin.forms.edit', compact('form', 'users', 'assignedUserIds'));
    }

    public function update(Request $request, ChecklistForm $form)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'schedule_type' => 'required|in:daily,weekly,custom',
        ]);

        $form->update([
            'title'             => $request->title,
            'description'       => $request->description,
            'schedule_type'     => $request->schedule_type,
            'schedule_days'     => $request->schedule_days ?? [],
            'schedule_interval' => $request->schedule_interval,
            'start_date'        => $request->start_date,
            'end_date'          => $request->end_date,
        ]);

        // Update items jika ada
        if ($request->has('items')) {
            $form->items()->delete();
            $items = collect($request->items)->sortBy('order_index')->values();
            foreach ($items as $index => $item) {
                $options = null;
                if (!empty($item['options_raw'])) {
                    $options = array_map('trim', explode(',', $item['options_raw']));
                    $options = array_filter($options);
                    $options = array_values($options);
                }
                FormItem::create([
                    'form_id'     => $form->id,
                    'label'       => $item['label'],
                    'field_type'  => $item['field_type'],
                    'options'     => $options,
                    'is_required' => isset($item['is_required']),
                    'placeholder' => $item['placeholder'] ?? null,
                    'helper_text' => $item['helper_text'] ?? null,
                    'order_index' => $index,
                ]);
            }
        }

        // Update assignments
        if ($request->has('assigned_users')) {
            $form->assignments()->delete();
            foreach ($request->assigned_users as $userId) {
                FormAssignment::create([
                    'form_id' => $form->id,
                    'user_id' => $userId,
                ]);
            }
        }

        return redirect()->route('admin.forms.index')
            ->with('success', "Form \"{$form->title}\" berhasil diupdate!");
    }

    public function destroy(ChecklistForm $form)
    {
        $title = $form->title;
        $form->delete();
        return redirect()->route('admin.forms.index')
            ->with('success', "Form \"{$title}\" berhasil dihapus.");
    }

    public function toggle(ChecklistForm $form)
    {
        $form->update(['is_active' => !$form->is_active]);
        $status = $form->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Form berhasil {$status}.");
    }

    public function duplicate(ChecklistForm $form)
    {
        $newForm = $form->replicate();
        $newForm->title      = $form->title . ' (Copy)';
        $newForm->is_active  = false;
        $newForm->created_by = Auth::id();
        $newForm->save();

        foreach ($form->items as $item) {
            $newItem = $item->replicate();
            $newItem->form_id = $newForm->id;
            $newItem->save();
        }

        return redirect()->route('admin.forms.edit', $newForm)
            ->with('success', 'Form berhasil diduplikasi. Silakan edit sesuai kebutuhan.');
    }
}
