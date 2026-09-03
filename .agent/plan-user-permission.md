# Implementation Plan - User Role Permission dan Akses Modul

**Target Repository:** `/home/unifi-controller/it_checklist_v3`  
**Tanggal:** 2 September 2026  
**Status:** Ready for Implementation

## 1. Konteks

Repository menggunakan Laravel 12, PHP 8.2+, Blade, Bootstrap 5, Font Awesome 6, Vite, PHPUnit, autentikasi Laravel, kolom `users.role`, dan `RoleMiddleware` legacy.

Permission akan memakai `spatie/laravel-permission` dan diberikan langsung ke user. Kolom role serta helper existing tetap dipertahankan. Superadmin memiliki akses penuh.

## 2. Cakupan File

| Area | File yang dibuat/diubah |
| --- | --- |
| Dependency | `composer.json`, `composer.lock` |
| Konfigurasi | `config/permission.php` hasil publish Spatie |
| Database | Migration bawaan Spatie |
| Model | `app/Models/User.php` |
| Registry | Class/config daftar permission terpusat |
| Middleware | `bootstrap/app.php`, `RoleMiddleware` bila diperlukan |
| Routes | `routes/web.php` |
| Seeder | `database/seeders/PermissionSeeder.php`, `DatabaseSeeder.php`, `UserSeeder.php` bila perlu |
| Controller | `app/Http/Controllers/Admin/UserManagementController.php` |
| UI | View user create/edit/index, layout admin, layout user bila tersedia |
| Test | Feature test authorization, seeder, default permission, dan sidebar |

## 3. Registry Permission

Buat satu sumber daftar permission dengan metadata `name`, `label`, `group`, dan `default_user`.

```php
module.dashboard
module.checklist
module.submissions
module.daily-activity
module.asset
module.document-maker
module.reports
module.user-management
module.history
```

Default user:

```php
[
    'module.dashboard',
    'module.checklist',
    'module.daily-activity',
    'module.history',
]
```

## 4. Tahapan Implementasi

### 4.1 Dependency dan Migration

```bash
cd /home/unifi-controller/it_checklist_v3
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\\Permission\\PermissionServiceProvider"
php artisan migrate
```

Pastikan guard `web` digunakan dan migration tidak mengubah kolom `users.role`.

### 4.2 Model dan Superadmin Bypass

Tambahkan `Spatie\Permission\Traits\HasRoles` ke model `User`. Pertahankan `$fillable`, `isAdmin()`, dan `isSuperAdmin()`.

Tambahkan bypass resmi melalui `Gate::before()` atau mekanisme provider yang kompatibel dengan versi package:

```php
Gate::before(function (User $user, string $ability): ?bool {
    return $user->isSuperAdmin() ? true : null;
});
```

Validasi agar bypass berlaku untuk `can()`, middleware permission, dan directive Blade.

### 4.3 Permission Seeder

Buat `PermissionSeeder` yang:

1. Menggunakan `firstOrCreate()` untuk seluruh permission guard `web`.
2. Memberi semua permission ke superadmin atau mengandalkan bypass secara konsisten.
3. Memberi empat default permission kepada user role `user` yang belum memiliki permission.
4. Memberi permission kompatibilitas kepada admin existing tanpa `module.user-management`.
5. Tidak menghapus permission custom saat dijalankan ulang.
6. Membersihkan cache permission setelah seeding.

Panggil seeder setelah `UserSeeder` di `DatabaseSeeder`.

### 4.4 Route Authorization

Pertahankan `RoleMiddleware` sebagai pembatas area admin/user dan tambahkan middleware permission pada setiap kelompok modul.

| Area | Permission |
| --- | --- |
| Admin Dashboard dan metrics | `module.dashboard` |
| Form Builder | `module.checklist` |
| Submissions dan monitoring submission | `module.submissions` |
| Admin Daily Activity | `module.daily-activity` |
| Asset dan export Asset | `module.asset` |
| Memo, Berita Acara, Instruksi Kerja, endpoint terkait | `module.document-maker` |
| Reports dan export laporan | `module.reports` |
| User Management | `module.user-management` + role `superadmin` |
| User Dashboard | `module.dashboard` |
| User Checklist dan submission miliknya | `module.checklist` |
| User History | `module.history` |
| User Daily Activity | `module.daily-activity` |

Pecah route group agar export dan endpoint mutasi memakai permission yang sama dengan halaman modul.

### 4.5 User Management Controller

Perbarui controller untuk:

- Mengirim registry permission ke form edit.
- Membaca permission user untuk state checkbox.
- Memvalidasi `permissions` sebagai array nullable.
- Membatasi setiap permission ke daftar valid guard `web`.
- Memanggil `syncPermissions()` setelah data user valid.
- Memberi default permission saat membuat user role `user`.
- Menolak atau mengabaikan `module.user-management` untuk non-superadmin.
- Mempertahankan validasi superadmin pada seluruh action Management User.

Jangan mengubah permission user custom hanya karena seeder dijalankan ulang.

### 4.6 Blade dan Sidebar

Pada `resources/views/admin/users/edit.blade.php`, tambahkan bagian **Hak Akses Modul** berisi 9 checkbox:

- `name="permissions[]"`
- value permission
- label Bahasa Indonesia
- state dari `old()` lalu permission tersimpan

Gunakan `@can('module.xxx')` pada sidebar admin dan user. Grup Document Maker hanya tampil jika `module.document-maker` tersedia. Backend tetap menjadi pengaman utama.

### 4.7 Redirect Login

Periksa controller login. Resolver tujuan login memilih urutan:

1. Dashboard.
2. Checklist Saya.
3. Daily Activity.
4. Riwayat.

Hanya pilih route yang boleh diakses user dan hindari redirect loop bila tidak ada permission.

## 5. Test Feature

Buat test mengikuti struktur repository, misalnya `tests/Feature/Auth/UserPermissionTest.php`.

Scenario minimum:

1. Seeder menghasilkan 9 permission tanpa duplikasi.
2. User baru role `user` mendapat empat default permission.
3. User default dapat membuka Dashboard, Checklist, Daily Activity, dan Riwayat.
4. User default mendapat 403 untuk Asset, Submission, Document Maker, dan Laporan.
5. User dengan permission Asset dapat membuka index, CRUD, PDF, dan Excel Asset.
6. Permission melindungi URL langsung, endpoint mutasi, export, dan AJAX.
7. Sidebar tidak menampilkan menu tanpa permission dan menampilkan menu setelah permission diberikan.
8. Superadmin dapat membuka semua modul tanpa assignment manual.
9. Non-superadmin tidak dapat membuka Management User walaupun mengirim permission terkait.
10. Superadmin dapat menyimpan permission dan `syncPermissions()` menghapus permission yang di-uncheck.
11. Permission invalid ditolak.

## 6. Urutan Eksekusi

1. Install package dan publish konfigurasi/migration.
2. Tambahkan trait User dan bypass superadmin.
3. Buat registry permission.
4. Buat dan panggil seeder permission.
5. Migrasikan database dan reset cache permission.
6. Tambahkan middleware permission ke route.
7. Tambahkan checkbox pada Management User.
8. Perbarui sidebar dan redirect login.
9. Tambahkan feature test.
10. Jalankan formatter, lint, route check, dan test.

## 7. Verifikasi

```bash
composer validate
php artisan migrate
php artisan db:seed
php artisan permission:cache-reset
php artisan route:list
./vendor/bin/pint --dirty
git diff --check
php artisan test --filter=UserPermissionTest
php artisan test
```

Verifikasi manual:

1. Login sebagai user baru dan pastikan hanya empat menu default tampil.
2. Coba akses Asset, Submission, Document Maker, dan Laporan langsung; hasil harus 403.
3. Login sebagai superadmin dan edit permission user.
4. Centang Asset dan Laporan, simpan, lalu verifikasi menu dan route.
5. Uncheck keduanya, simpan, lalu verifikasi akses kembali 403.
6. Pastikan superadmin selalu dapat mengakses semua modul.
7. Pastikan admin biasa tidak dapat mengelola Management User.

## 8. Risiko dan Mitigasi

| Risiko | Mitigasi |
| --- | --- |
| Role legacy dan permission konflik | Role membatasi area; permission membatasi modul. |
| Superadmin kehilangan akses | Gate bypass dan test khusus superadmin. |
| Menu tersembunyi tetapi route terbuka | Middleware permission pada semua route, export, dan mutasi. |
| Seeder mereset custom permission | Hanya isi default user baru/tanpa permission; jangan reset custom. |
| Admin mendapat Management User | Pertahankan pembatasan `isSuperAdmin()` selain middleware permission. |
| Cache permission stale | Jalankan cache reset setelah seeding/deployment. |
| PHPUnit gagal karena `pdo_sqlite` | Dokumentasikan kendala dan tetap jalankan lint, migration, route check, dan test pada environment yang mendukung driver. |

## 9. Definition of Done

- Dependency, konfigurasi, dan migration Spatie tersedia.
- Sembilan permission dan default user tersedia melalui seeder idempotent.
- Route backend dan sidebar mengikuti permission.
- Management User dapat menyimpan checkbox permission.
- Superadmin memiliki akses penuh.
- Role legacy tetap berfungsi.
- Test, Pint, PHP lint, route check, dan `git diff --check` bersih sejauh environment memungkinkan.