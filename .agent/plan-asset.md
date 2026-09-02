# Implementation Plan - Modul Manajemen Asset IT

**Target Repository:** `/home/unifi-controller/it_checklist_v3`

**Tanggal:** 2 September 2026

**Status:** Ready for Implementation

## 1. Konteks Implementasi

Repository merupakan aplikasi Laravel 12 dengan PHP 8.2+, Eloquent ORM, Blade templates, Bootstrap 5, Font Awesome 6, Vite, dan PHPUnit. Area admin menggunakan prefix route `/admin`, middleware `auth` dan `role:admin,superadmin`, serta layout `resources/views/layouts/admin.blade.php`.

Modul Asset akan mengikuti pola yang sudah ada pada Daily Activity dan User Management, tanpa menambah dependency baru atau mengubah behavior modul yang tidak terkait.

## 2. Cakupan File

| Area | File yang dibuat/diubah |
| --- | --- |
| Database | Migration kategori asset dan asset; seeder kategori default. |
| Domain | `app/Models/AssetCategory.php`, `app/Models/Asset.php`. |
| HTTP | `app/Http/Controllers/Admin/AssetController.php`, `app/Http/Controllers/Admin/AssetCategoryController.php`. |
| Routing | `routes/web.php`. |
| UI | Blade asset dan kategori di `resources/views/admin/assets/` dan `resources/views/admin/asset-categories/`. |
| Navigasi | `resources/views/layouts/admin.blade.php`. |
| Test | Feature test CRUD, otorisasi, validasi, filter, dan penghapusan kategori. |

## 3. Tahap Implementasi

### 3.1 Database

Buat migration baru dengan timestamp setelah migration terbaru agar deployment berjalan berurutan.

#### Tabel `asset_categories`

Kolom:

```php
$table->id();
$table->string('name', 100)->unique();
$table->timestamps();
```

Catatan:

- Unique constraint berada di database dan validasi aplikasi.
- Migration `down()` menghapus tabel dengan aman melalui `Schema::dropIfExists`.

#### Tabel `assets`

Kolom:

```php
$table->id();
$table->foreignId('asset_category_id')
    ->constrained('asset_categories')
    ->restrictOnDelete();
$table->string('name');
$table->unsignedSmallInteger('purchase_year')->index();
$table->string('brand', 100);
$table->string('type', 100);
$table->string('item_code', 100)->unique();
$table->string('inventory_number', 100)->unique();
$table->string('serial_number', 150)->unique();
$table->unsignedInteger('quantity')->default(1);
$table->string('location')->index();
$table->timestamps();

$table->index(['asset_category_id', 'purchase_year']);
```

Keputusan relasi:

- Gunakan `restrictOnDelete()` agar database mencegah orphan asset apabila kategori masih dipakai.
- Controller kategori juga mengecek relasi sebelum delete untuk memberi flash message yang jelas, bukan database exception.
- Unique index item code, nomor inventaris, dan serial number menjaga identitas inventaris.

### 3.2 Seeder Kategori Default

Buat `database/seeders/AssetCategorySeeder.php` dan panggil dari `DatabaseSeeder`.

Data seed:

```php
['Tools', 'Switch', 'Access Point', 'Router', 'Printer', 'Others']
```

Gunakan `firstOrCreate(['name' => $name])` agar seed dapat dijalankan ulang tanpa membuat data duplikat. Ejaan `Access Point` dipakai sebagai koreksi terhadap typo "Acess Point" pada requirement awal.

### 3.3 Model dan Relasi

Tambahkan model Eloquent berikut.

#### `App\Models\AssetCategory`

- Gunakan `HasFactory`.
- `$fillable = ['name']`.
- Relasi `assets(): HasMany` ke `Asset`.
- Default ordering kategori dilakukan oleh query controller `orderBy('name')`, bukan global scope.

#### `App\Models\Asset`

- Gunakan `HasFactory`.
- `$fillable`: `asset_category_id`, `name`, `purchase_year`, `brand`, `type`, `item_code`, `inventory_number`, `serial_number`, `quantity`, `location`.
- Relasi `category(): BelongsTo` dengan foreign key `asset_category_id`.
- Tambahkan cast `purchase_year` dan `quantity` sebagai `integer`.

Factory asset dapat ditambahkan jika diperlukan test agar pembuatan data fixture konsisten. Factory kategori/asset tidak boleh bergantung pada seed data test secara implisit.

### 3.4 Controller Asset

Buat `App\Http\Controllers\Admin\AssetController` dengan action:

| Action | Method | Tanggung Jawab |
| --- | --- | --- |
| `index` | GET | Validasi filter, query berelasi kategori, search, pagination, dan kirim kategori filter ke view. |
| `create` | GET | Menampilkan form tambah dengan kategori terurut. |
| `store` | POST | Validasi dan membuat asset; redirect dengan flash success. |
| `show` | GET | Menampilkan detail asset dan kategori. |
| `edit` | GET | Menampilkan form edit dan kategori. |
| `update` | PUT/PATCH | Validasi dengan pengecualian unique rule untuk asset aktif lalu update. |
| `destroy` | DELETE | Hapus asset dan redirect dengan flash success. |

Gunakan route model binding `Asset $asset` dan eager loading `with('category')` di daftar untuk menghindari N+1 query.

Aturan validasi store/update:

```php
'asset_category_id' => ['required', 'exists:asset_categories,id'],
'name' => ['required', 'string', 'max:255'],
'purchase_year' => ['required', 'integer', 'between:1900,'.now()->year],
'brand' => ['required', 'string', 'max:100'],
'type' => ['required', 'string', 'max:100'],
'item_code' => ['required', 'string', 'max:100', Rule::unique('assets', 'item_code')->ignore($asset?->id)],
'inventory_number' => ['required', 'string', 'max:100', Rule::unique('assets', 'inventory_number')->ignore($asset?->id)],
'serial_number' => ['required', 'string', 'max:150', Rule::unique('assets', 'serial_number')->ignore($asset?->id)],
'quantity' => ['required', 'integer', 'min:1'],
'location' => ['required', 'string', 'max:255'],
```

Query daftar:

- Validasi `search` maksimum 255 karakter, `asset_category_id` harus ada di tabel kategori ketika diisi, `location` maksimum 255 karakter, serta `purchase_year` pada range yang valid.
- Terapkan `where` kategori, lokasi, tahun hanya apabila parameter terisi.
- Untuk search, kelompokkan `orWhere` pada kolom `name`, `brand`, `type`, `item_code`, `inventory_number`, `serial_number`, dan `location`.
- Urutkan `latest('id')`, `paginate(20)`, lalu `withQueryString()`.

### 3.5 Controller Kategori Asset

Buat `App\Http\Controllers\Admin\AssetCategoryController` dengan `index`, `store`, `update`, dan `destroy`.

Otorisasi kategori:

- Pemeriksaan eksplisit `abort_unless($request->user()->isSuperAdmin(), 403)` pada tiap action mutasi dan halaman kategori.
- Alternatif yang disarankan saat implementasi adalah route group tambahan `->middleware('role:superadmin')`, agar keputusan akses berada di routing. Pilih satu pola dan terapkan konsisten; route middleware lebih ringkas dan sesuai `RoleMiddleware` yang ada.

Proses delete kategori:

1. Muat kategori melalui route model binding.
2. Periksa `$assetCategory->assets()->exists()`.
3. Jika true, redirect kembali dengan flash error tanpa delete.
4. Jika false, hapus kategori dan tampilkan flash success.

Validasi nama kategori:

```php
'name' => [
    'required',
    'string',
    'max:100',
    Rule::unique('asset_categories', 'name')->ignore($assetCategory?->id),
],
```

Normalisasi yang perlu diterapkan sebelum validasi/penyimpanan: `trim` whitespace. Case-insensitive uniqueness sepenuhnya bergantung pada collation database; bila database menggunakan collation case-sensitive, tambahkan validasi tambahan dengan `whereRaw('LOWER(name) = ?', [strtolower($name)])` atau tetapkan collation yang sesuai melalui migration.

### 3.6 Route

Tambahkan route dalam group admin yang sudah ada di `routes/web.php`:

```php
Route::resource('assets', Admin\AssetController::class);

Route::middleware('role:superadmin')->group(function () {
    Route::get('asset-categories', [Admin\AssetCategoryController::class, 'index'])
        ->name('asset-categories.index');
    Route::post('asset-categories', [Admin\AssetCategoryController::class, 'store'])
        ->name('asset-categories.store');
    Route::put('asset-categories/{assetCategory}', [Admin\AssetCategoryController::class, 'update'])
        ->name('asset-categories.update');
    Route::delete('asset-categories/{assetCategory}', [Admin\AssetCategoryController::class, 'destroy'])
        ->name('asset-categories.destroy');
});
```

Resource Asset otomatis menghasilkan named routes `admin.assets.index`, `create`, `store`, `show`, `edit`, `update`, dan `destroy` dengan prefix `/admin/assets`.

### 3.7 Blade Views

Buat struktur view:

```text
resources/views/admin/assets/
  index.blade.php
  create.blade.php
  edit.blade.php
  show.blade.php
  _form.blade.php
resources/views/admin/asset-categories/
  index.blade.php
```

Ketentuan UI:

- Semua view memakai `@extends('layouts.admin')` serta section title yang konsisten.
- Partial `_form.blade.php` dipakai bersama create dan edit untuk menghindari duplikasi field/validasi output.
- Gunakan `old()` sebagai prioritas lalu nilai model untuk mempertahankan input saat validasi gagal.
- Tampilkan error via `@error('field')` di bawah input dengan kelas Bootstrap error.
- Select kategori menggunakan value ID dan label nama kategori.
- Form list filter memakai `GET`; tombol filter dan reset tersedia.
- Table dibungkus `.table-responsive`; output data tetap escaped oleh `{{ }}`.
- Aksi edit/hapus memakai Font Awesome dan tombol hapus berupa form `DELETE` dengan `@csrf` serta `@method('DELETE')`.
- Konfirmasi hapus dapat menggunakan `onsubmit="return confirm('...')"` agar tidak perlu library JavaScript baru.
- Halaman kategori menyediakan form tambah dan tabel kategori. Edit dapat menggunakan modal Bootstrap atau form inline; pilih pola yang paling sederhana dan konsisten dengan view existing.

Tambahkan menu Asset ke sidebar pada `resources/views/layouts/admin.blade.php`, setelah Daily Activity:

```blade
<li class="nav-item">
    <a href="{{ route('admin.assets.index') }}"
        class="nav-link {{ request()->routeIs('admin.assets.*') ? 'active' : '' }}"
        data-sidebar-label="Asset">
        <i class="fas fa-boxes-stacked"></i><span class="sidebar-link-text">Asset</span>
    </a>
</li>
```

Tambahkan link kategori hanya untuk superadmin, misalnya di halaman Asset sebagai tombol sekunder, agar sidebar utama tidak terlalu padat.

### 3.8 Test Feature

Buat `tests/Feature/Admin/AssetManagementTest.php` menggunakan `RefreshDatabase`.

Minimal scenario yang diuji:

1. Admin dapat melihat daftar asset.
2. Admin dapat membuat asset valid dan database memuat seluruh nilai penting serta foreign key kategori.
3. Validasi menolak setiap field wajib yang kosong, tahun tidak valid, dan quantity nol.
4. Validasi menolak duplicate `item_code`, `inventory_number`, dan `serial_number`.
5. Admin dapat update asset tanpa false-positive unique validation untuk nilai miliknya sendiri.
6. Admin dapat menghapus asset.
7. Search menemukan asset melalui beberapa identifier; filter kategori, lokasi, dan tahun membatasi hasil yang benar.
8. Query filter tetap ada dalam link pagination.
9. User role `user` menerima forbidden pada index/store/update/destroy asset.
10. Superadmin dapat mengelola kategori.
11. Admin tidak dapat mengakses atau memodifikasi kategori.
12. Kategori yang masih dipakai asset tidak dapat dihapus, sedangkan kategori tanpa asset dapat dihapus.
13. Seeder menghasilkan tepat enam kategori default tanpa duplikasi setelah dijalankan lebih dari sekali.

Gunakan user factory dengan role eksplisit. Buat kategori secara eksplisit di tiap test atau factory agar test tidak tergantung urutan seeder.

## 4. Urutan Eksekusi

1. Buat migration dan model.
2. Buat seeder lalu registrasikan pada `DatabaseSeeder`.
3. Implement controller dan seluruh validasi.
4. Daftarkan route dan middleware kategori superadmin.
5. Implement Blade view serta navigasi sidebar.
6. Buat dan jalankan feature test.
7. Jalankan formatter dan verifikasi aplikasi.

## 5. Verifikasi Akhir

Jalankan perintah berikut setelah implementasi:

```bash
cd /home/unifi-controller/it_checklist_v3
php artisan migrate
php artisan db:seed --class=AssetCategorySeeder
php artisan route:list --name=admin.assets
php artisan route:list --name=admin.asset-categories
php artisan test --filter=AssetManagementTest
php artisan test
./vendor/bin/pint --dirty
```

Verifikasi manual di browser:

1. Login sebagai admin dan buka `/admin/assets`.
2. Tambah, cari, filter, edit, dan hapus aset.
3. Pastikan admin tidak dapat mengakses `/admin/asset-categories`.
4. Login sebagai superadmin dan kelola kategori.
5. Pastikan kategori yang sedang dipakai menolak penghapusan dengan pesan yang jelas.
6. Login sebagai user dan pastikan seluruh route `/admin/assets` menghasilkan HTTP 403.

## 6. Risiko dan Keputusan

| Risiko/Keputusan | Mitigasi |
| --- | --- |
| Requirement `Jumlah` lebih dari satu bertentangan dengan SN yang wajib dan unik per unit. | Rilis awal menyimpan satu record per unit berserial; pengembangan berikutnya dapat menambah tabel `asset_units`. |
| Penghapusan kategori menyebabkan orphan data. | Foreign key `restrictOnDelete()` dan pengecekan relasi sebelum delete. |
| Duplikasi identitas melalui request paralel. | Unique index database selain validasi Laravel. |
| Akses role tidak konsisten. | Semua route berada di admin group; kategori memakai middleware `role:superadmin`; test mencakup forbidden response. |
| Query daftar menjadi lambat saat data bertambah. | Eager loading kategori, pagination, dan index pada kategori/tahun/lokasi. |