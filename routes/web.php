<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\User;

// ─── AUTH ──────────────────────────────────────────────
Route::get('/',      [AuthController::class, 'showLogin']);
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
    Route::get('submissions/{submission}', [Admin\SubmissionController::class, 'show'])->name('submissions.show');

    // Reports
    Route::get('reports', [Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export-excel', [Admin\ReportController::class, 'exportExcel'])->name('reports.export-excel');

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
});