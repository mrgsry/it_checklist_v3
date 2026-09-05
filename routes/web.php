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

// Ticketing callback: Terra calls this when a ticket changes status.
Route::post('/api/ticketing/tickets/{submission}/status', [Admin\TicketingController::class, 'status'])
    ->name('api.ticketing.status');

// ─── ADMIN ─────────────────────────────────────────────
Route::prefix('admin')
    ->middleware(['auth', 'role:admin,superadmin'])
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->middleware('module:dashboard,read')->name('dashboard');

        // Form Builder
        Route::middleware('module:checklist,read')->group(function () {
            Route::resource('forms', Admin\FormBuilderController::class)->middlewareFor(['store', 'update', 'destroy'], 'module:checklist,write');
            Route::post('forms/{form}/toggle', [Admin\FormBuilderController::class, 'toggle'])->middleware('module:checklist,write')->name('forms.toggle');
            Route::post('forms/{form}/duplicate', [Admin\FormBuilderController::class, 'duplicate'])->middleware('module:checklist,write')->name('forms.duplicate');
        });

        // Submissions
        Route::middleware('module:submissions,read')->group(function () {
            Route::get('submissions', [Admin\SubmissionController::class, 'index'])->name('submissions.index');
            Route::get('submissions/{submission}/edit', [Admin\SubmissionController::class, 'edit'])->name('submissions.edit');
            Route::put('submissions/{submission}', [Admin\SubmissionController::class, 'update'])->middleware('module:submissions,write')->name('submissions.update');
            Route::delete('submissions/{submission}', [Admin\SubmissionController::class, 'destroy'])->middleware('module:submissions,write')->name('submissions.destroy');
            Route::get('submissions/{submission}/export-pdf', [Admin\SubmissionController::class, 'exportPdf'])->name('submissions.export-pdf');
            Route::get('submissions/{submission}', [Admin\SubmissionController::class, 'show'])->name('submissions.show');
        });

        // Terra Support ticketing proxy
        Route::middleware('module:submissions,read')->group(function () {
            Route::get('ticketing/departments', [Admin\TicketingController::class, 'departments'])->name('ticketing.departments');
            Route::get('ticketing/types', [Admin\TicketingController::class, 'types'])->name('ticketing.types');
            Route::get('ticketing/categories', [Admin\TicketingController::class, 'categories'])->name('ticketing.categories');
            Route::post('ticketing/tickets/{submission}', [Admin\TicketingController::class, 'create'])->middleware('module:submissions,write')->name('ticketing.create');
            Route::post('ticketing/tickets/{submission}/status', [Admin\TicketingController::class, 'status'])->middleware('module:submissions,write')->name('ticketing.status');
        });

        // Reports
        Route::middleware('module:reports,read')->group(function () {
            Route::get('reports', [Admin\ReportController::class, 'index'])->name('reports.index');
            Route::get('reports/data', [Admin\ReportController::class, 'data'])->name('reports.data');
            Route::get('reports/export-pdf', [Admin\ReportController::class, 'exportPdf'])->name('reports.export-pdf');
            Route::get('reports/export-excel', [Admin\ReportController::class, 'exportExcel'])->name('reports.export-excel');
        });

        // Daily Activity Monitoring
        Route::middleware('module:daily-activity,read')->group(function () {
            Route::post('daily-activities', [Admin\DailyActivityController::class, 'store'])->middleware('module:daily-activity,write')->name('daily-activities.store');
            Route::put('daily-activities/{dailyActivity}', [Admin\DailyActivityController::class, 'update'])->middleware('module:daily-activity,write')->name('daily-activities.update');
            Route::delete('daily-activities/{dailyActivity}', [Admin\DailyActivityController::class, 'destroy'])->middleware('module:daily-activity,write')->name('daily-activities.destroy');
            Route::get('daily-activities/export-excel', [Admin\DailyActivityController::class, 'exportExcel'])->name('daily-activities.export-excel');
            Route::get('activity-monitor', [Admin\DashboardController::class, 'activityMonitorPage'])->name('activity-monitor');
        });
        Route::middleware('module:dashboard,read')->group(function () {
            Route::get('dashboard/metrics', [Admin\DashboardController::class, 'dashboardMetrics'])->name('dashboard.metrics');
            Route::get('dashboard/card-details/{card}', [Admin\DashboardController::class, 'cardDetails'])->name('dashboard.card-details');
        });

            // Asset Management
    Route::middleware('module:asset,read')->group(function () {
        Route::get('assets/import/template', [Admin\AssetController::class, 'downloadTemplate'])->middleware('module:asset,write')->name('assets.import.template');
        Route::get('assets/import', [Admin\AssetController::class, 'importForm'])->middleware('module:asset,write')->name('assets.import.form');
        Route::post('assets/import', [Admin\AssetController::class, 'import'])->middleware('module:asset,write')->name('assets.import');
        Route::get('assets/export-pdf', [Admin\AssetController::class, 'exportPdf'])->name('assets.export-pdf');
        Route::get('assets/export-excel', [Admin\AssetController::class, 'exportExcel'])->name('assets.export-excel');
        Route::resource('assets', Admin\AssetController::class)->middlewareFor(['store', 'update', 'destroy'], 'module:asset,write');

        Route::middleware(['role:superadmin'])->group(function () {
            // Cukup gunakan Route::resource ini saja!
            Route::resource('asset-categories', Admin\AssetCategoryController::class)->except('show')->middlewareFor(['store', 'update', 'destroy'], 'module:asset,write');
        });
    });

        // Memo Maker
        Route::middleware('module:document-maker,read')->group(function () {
            Route::get('memo-maker', [Admin\MemoMakerController::class, 'index'])->name('memo-maker.index');
            Route::get('berita-acara-maker', [Admin\BeritaAcaraMakerController::class, 'index'])->name('berita-acara-maker.index');
            Route::get('instruksi-kerja-maker', [Admin\InstruksiKerjaMakerController::class, 'index'])->name('instruksi-kerja-maker.index');
            Route::post('instruksi-kerja-maker/documents', [Admin\InstruksiKerjaMakerController::class, 'store'])->name('instruksi-kerja-maker.documents.store');
            Route::delete('instruksi-kerja-maker/documents/{documentId}', [Admin\InstruksiKerjaMakerController::class, 'destroy'])->name('instruksi-kerja-maker.documents.destroy');
            Route::get('settings', [Admin\AdminSettingController::class, 'show'])->name('settings.show');
            Route::put('settings', [Admin\AdminSettingController::class, 'update'])->name('settings.update');
            Route::get('ai/models', [Admin\AiProxyController::class, 'models'])->name('ai.models');
            Route::post('ai/chat', [Admin\AiProxyController::class, 'chat'])->name('ai.chat');
        });

        // User Management (superadmin only)
        Route::middleware(['role:superadmin', 'module:user-management,read'])
            ->resource('users', Admin\UserManagementController::class)
            ->middlewareFor(['store', 'update', 'destroy'], 'module:user-management,write');
    });

// ─── USER ───────────────────────────────────────────────
Route::prefix('user')
    ->middleware(['auth', 'role:user'])
    ->name('user.')
    ->group(function () {

        Route::get('/dashboard', [User\DashboardController::class, 'index'])->middleware('module:dashboard,read')->name('dashboard');
        Route::middleware('module:asset,read')->group(function () {
            Route::get('/assets', [User\AssetController::class, 'index'])->name('assets.index');
            Route::get('/assets/create', [User\AssetController::class, 'create'])->middleware('module:asset,write')->name('assets.create');
            Route::get('/assets/{asset}/edit', [User\AssetController::class, 'edit'])->middleware('module:asset,write')->name('assets.edit');
            Route::post('/assets', [User\AssetController::class, 'store'])->middleware('module:asset,write')->name('assets.store');
            Route::put('/assets/{asset}', [User\AssetController::class, 'update'])->middleware('module:asset,write')->name('assets.update');
            Route::delete('/assets/{asset}', [User\AssetController::class, 'destroy'])->middleware('module:asset,write')->name('assets.destroy');
        });
        Route::middleware('module:checklist,read')->group(function () {
            Route::get('/checklist', [User\ChecklistController::class, 'index'])->name('checklist.index');
            Route::get('/checklist/{formId}/fill', [User\ChecklistController::class, 'fill'])->middleware('module:checklist,write')->name('checklist.fill');
            Route::post('/checklist/{formId}/submit', [User\ChecklistController::class, 'submit'])->middleware('module:checklist,write')->name('checklist.submit');
            Route::get('/submissions/{submission}/edit', [User\ChecklistController::class, 'edit'])->middleware('module:checklist,write')->name('submissions.edit');
            Route::put('/submissions/{submission}', [User\ChecklistController::class, 'update'])->middleware('module:checklist,write')->name('submissions.update');
            Route::delete('/submissions/{submission}', [User\ChecklistController::class, 'destroy'])->middleware('module:checklist,write')->name('submissions.destroy');
        });
        Route::get('/history', [User\ChecklistController::class, 'history'])->middleware('module:history,read')->name('history');
        Route::middleware('module:daily-activity,read')->group(function () {
            Route::get('/daily-activities', [User\DailyActivityController::class, 'index'])->name('daily-activities.index');
            Route::post('/daily-activities', [User\DailyActivityController::class, 'store'])->middleware('module:daily-activity,write')->name('daily-activities.store');
            Route::put('/daily-activities/{dailyActivity}', [User\DailyActivityController::class, 'update'])->middleware('module:daily-activity,write')->name('daily-activities.update');
            Route::delete('/daily-activities/{dailyActivity}', [User\DailyActivityController::class, 'destroy'])->middleware('module:daily-activity,write')->name('daily-activities.destroy');
        });
    });