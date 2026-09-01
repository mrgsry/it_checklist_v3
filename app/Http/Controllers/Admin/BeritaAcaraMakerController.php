<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;

class BeritaAcaraMakerController extends Controller
{
    public function index()
    {
        return view('admin.berita-acara-maker.index', ['adminSettings' => AdminSettingController::payload(AdminSetting::first())]);
    }
}