# IT Checklist App — Perbaikan sesuai Blueprint

## Fase 1: Critical Fixes
- [x] 1. Delete duplicate migration `2026_04_28_144429_create_form_items_table.php`
- [x] 2. Rewrite `resources/views/user/dashboard.blade.php` (extend layouts.user, stat cards, checklist list)
- [x] 3. Fix `app/Http/Controllers/Admin/DashboardController.php` (return admin.dashboard, compute stats)
- [x] 4. Rewrite `resources/views/admin/dashboard.blade.php` (stat cards, Chart.js bar+doughnut charts, recent submissions)

## Fase 2: Missing User Views
- [x] 5. Create `resources/views/user/checklist/index.blade.php` (today's checklists with status badges)
- [x] 6. Create `resources/views/user/checklist/fill.blade.php` (mobile-friendly form: text, number, textarea, checkbox, radio, dropdown, signal)
- [x] 7. Create `resources/views/user/checklist/history.blade.php` (submission history table)

## Fase 3: Missing Admin Views
- [x] 8. Create `resources/views/admin/users/index.blade.php`
- [x] 9. Create `resources/views/admin/users/create.blade.php`
- [x] 10. Create `resources/views/admin/users/edit.blade.php`
- [x] 11. Rewrite `resources/views/admin/forms/create.blade.php` (full form builder UI with dynamic items, schedule config, user assignment)
- [x] 12. Rewrite `resources/views/admin/forms/edit.blade.php` (same UI, pre-filled data)

## Fase 4: Service & Logic Fixes
- [x] 13. Fix `app/Services/SchedulerService.php` (dayName format `D` for weekly schedule matching per blueprint)
- [x] 14. Fix `app/Services/AnomalyDetectionService.php` (keyword-based flagging per blueprint)
- [x] 15. Delete duplicate `app/Http/Controllers/Services/` directory

## Fase 5: Controller Tweaks
- [x] 16. Enrich Admin DashboardController stats (totalForms, totalSubmissions, totalUsers, complianceRate, issuesToday, recentSubmissions, weeklyComplianceData)
- [x] 17. Enrich User DashboardController with status info (formsDue, submittedToday, totalThisMonth, streak)

## Extra Improvements (Beyond Plan)
- [x] 18. Rewrite `resources/views/admin/forms/index.blade.php` (action buttons: view, edit, toggle, duplicate, delete)
- [x] 19. Rewrite `resources/views/admin/forms/show.blade.php` (detail view with items, assignments, submissions)
- [x] 20. Rewrite `resources/views/admin/submissions/index.blade.php` (filters, flagged highlighting)
- [x] 21. Rewrite `resources/views/admin/submissions/show.blade.php` (answer detail with flagged rows)
- [x] 22. Rewrite `resources/views/admin/reports/index.blade.php` (summary stats, per-item columns, export button)
- [x] 23. Fix HTML structure in all user views (proper closing tags)

## Verification
- [x] All 30 routes registered successfully (`php artisan route:list`)
- [x] All Blade templates compile without errors (`php artisan view:cache`)
- [x] No PHP syntax errors detected
