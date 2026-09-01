<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\User;
use Illuminate\Support\Facades\Route;

// ─── AUTH ──────────────────────────────────────────────
Route::get('/', [AuthController::class, 'showLogin']);
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ─── ADMIN ─────────────────────────────────────────────
Route::prefix('admin')
    ->middleware(['auth', 'role:admin,superadmin'])
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

        // Form Builder
        Route::resource('forms', Admin\FormBuilderController::class);
        Route::post('forms/{form}/toggle', [Admin\FormBuilderController::class, 'toggle'])->name('forms.toggle');
        Route::post('forms/{form}/duplicate', [Admin\FormBuilderController::class, 'duplicate'])->name('forms.duplicate');

        // Submissions
        Route::get('submissions', [Admin\SubmissionController::class, 'index'])->name('submissions.index');
        Route::get('submissions/{submission}/export-pdf', [Admin\SubmissionController::class, 'exportPdf'])->name('submissions.export-pdf');
        Route::get('submissions/{submission}', [Admin\SubmissionController::class, 'show'])->name('submissions.show');

        // Reports
        Route::get('reports', [Admin\ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/data', [Admin\ReportController::class, 'data'])->name('reports.data');
        Route::get('reports/export-pdf', [Admin\ReportController::class, 'exportPdf'])->name('reports.export-pdf');
        Route::get('reports/export-excel', [Admin\ReportController::class, 'exportExcel'])->name('reports.export-excel');

        // Daily Activity Monitoring
         Route::get('daily-activities', [Admin\DailyActivityController::class, 'index'])->name('daily-activities.index');
         Route::post('daily-activities', [Admin\DailyActivityController::class, 'store'])->name('daily-activities.store');
         Route::get('daily-activities/export-pdf', [Admin\DailyActivityController::class, 'exportPdf'])->name('daily-activities.export-pdf');
         Route::get('daily-activities/export-excel', [Admin\DailyActivityController::class, 'exportExcel'])->name('daily-activities.export-excel');
         Route::get('activity-monitor', [Admin\DashboardController::class, 'activityMonitor'])->name('activity-monitor');
         Route::get('dashboard/metrics', [Admin\DashboardController::class, 'dashboardMetrics'])->name('dashboard.metrics');
         Route::get('dashboard/card-details/{card}', [Admin\DashboardController::class, 'cardDetails'])->name('dashboard.card-details');

        // Memo Maker
        Route::get('memo-maker', [Admin\MemoMakerController::class, 'index'])->name('memo-maker.index');
        Route::get('berita-acara-maker', [Admin\BeritaAcaraMakerController::class, 'index'])->name('berita-acara-maker.index');
        Route::get('instruksi-kerja-maker', [Admin\InstruksiKerjaMakerController::class, 'index'])->name('instruksi-kerja-maker.index');
        Route::post('instruksi-kerja-maker/documents', [Admin\InstruksiKerjaMakerController::class, 'store'])->name('instruksi-kerja-maker.documents.store');
        Route::delete('instruksi-kerja-maker/documents/{documentId}', [Admin\InstruksiKerjaMakerController::class, 'destroy'])->name('instruksi-kerja-maker.documents.destroy');
        Route::get('settings', [Admin\AdminSettingController::class, 'show'])->name('settings.show');
        Route::put('settings', [Admin\AdminSettingController::class, 'update'])->name('settings.update');
        Route::get('ai/models', [Admin\AiProxyController::class, 'models'])->name('ai.models');
        Route::post('ai/chat', [Admin\AiProxyController::class, 'chat'])->name('ai.chat');

        // User Management (superadmin only)
        Route::resource('users', Admin\UserManagementController::class);
    });

// ─── USER ───────────────────────────────────────────────
Route::prefix('user')
    ->middleware(['auth', 'role:user'])
    ->name('user.')
    ->group(function () {

        Route::get('/dashboard', [User\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/checklist', [User\ChecklistController::class, 'index'])->name('checklist.index');
        Route::get('/checklist/{formId}/fill', [User\ChecklistController::class, 'fill'])->name('checklist.fill');
        Route::post('/checklist/{formId}/submit', [User\ChecklistController::class, 'submit'])->name('checklist.submit');
        Route::get('/history', [User\ChecklistController::class, 'history'])->name('history');
        Route::get('/daily-activities', [User\DailyActivityController::class, 'index'])->name('daily-activities.index');
        Route::post('/daily-activities', [User\DailyActivityController::class, 'store'])->name('daily-activities.store');
        Route::put('/daily-activities/{dailyActivity}', [User\DailyActivityController::class, 'update'])->name('daily-activities.update');
        Route::delete('/daily-activities/{dailyActivity}', [User\DailyActivityController::class, 'destroy'])->name('daily-activities.destroy');
    });
