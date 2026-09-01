<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use App\Models\InstruksiKerjaDocument;
use Illuminate\Http\Request;

class InstruksiKerjaMakerController extends Controller
{
    public function index()
    {
        return view('admin.instruksi-kerja-maker.index', [
            'adminSettings' => AdminSettingController::payload(AdminSetting::first()),
            'documents' => InstruksiKerjaDocument::query()->latest('updated_at')->get()->pluck('payload')->values(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id' => ['required', 'string', 'max:100'],
            'document' => ['required', 'array'],
        ]);

        $document = InstruksiKerjaDocument::updateOrCreate(
            ['document_id' => $data['id']],
            ['payload' => array_merge($data['document'], ['id' => $data['id']])],
        );

        return response()->json($document->payload);
    }

    public function destroy(string $documentId)
    {
        InstruksiKerjaDocument::where('document_id', $documentId)->delete();

        return response()->json(['success' => true]);
    }
}