<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;

class MemoMakerController extends Controller
{
    public function index()
    {
        return view('admin.memo-maker.index', ['adminSettings' => AdminSettingController::payload(AdminSetting::first())]);
    }
}