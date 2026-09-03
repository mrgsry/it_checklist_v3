# Product Requirement Document (PRD)

## User Role Permission dan Akses Modul

**Produk:** IT Checklist  
**Organisasi:** PT Tera Data Indonusa Tbk. (TDI) - IT Department  
**Tanggal:** 2 September 2026  
**Versi:** 1.0.0  
**Status:** Proposed

## 1. Ringkasan

Sistem membutuhkan pengaturan hak akses berbasis modul agar superadmin dapat menentukan modul apa saja yang dapat diakses setiap user. Implementasi menggunakan `spatie/laravel-permission` dengan permission yang diberikan langsung kepada user melalui halaman Management User.

Kolom `role` legacy (`superadmin`, `admin`, `user`) tetap dipertahankan untuk kompatibilitas. `superadmin` selalu memiliki akses penuh dan dapat mengatur permission user lain.

## 2. Tujuan

- Menyediakan kontrol akses modul pada level user.
- Menampilkan hanya menu yang boleh digunakan user.
- Melindungi route di backend, bukan hanya menyembunyikan menu.
- Memberikan default permission untuk user baru.
- Mempertahankan akses penuh superadmin dan helper role lama.

## 3. Role dan Prinsip Akses

| Role Legacy | Aturan |
| --- | --- |
| `superadmin` | Akses seluruh modul dan dapat mengelola permission. |
| `admin` | Akses modul sesuai permission yang diberikan; tidak dapat mengelola Management User. |
| `user` | Default hanya Dashboard, Checklist Saya, Daily Activity, dan Riwayat. |

Permission diberikan langsung ke user. Role Spatie disiapkan oleh package, tetapi bukan sumber utama otorisasi pada versi ini.

## 4. Daftar Permission

| Modul/Menu | Permission | Label |
| --- | --- | --- |
| Dashboard | `module.dashboard` | Dashboard |
| Form Checklist | `module.checklist` | Form Checklist |
| Submission | `module.submissions` | Submission |
| Daily Activity | `module.daily-activity` | Daily Activity |
| Asset | `module.asset` | Asset |
| Document Maker | `module.document-maker` | Document Maker |
| Laporan | `module.reports` | Laporan |
| Management User | `module.user-management` | Management User |
| Riwayat | `module.history` | Riwayat |

`module.history` dipisahkan karena Riwayat adalah menu user tersendiri. Total permission yang dikelola adalah 9.

## 5. Default Permission User

User baru dengan role `user` menerima:

```text
module.dashboard
module.checklist
module.daily-activity
module.history
```

Permission Submission, Asset, Document Maker, Laporan, dan Management User tidak aktif secara default.

User `admin` mengikuti permission yang disimpan di user. Data admin existing dapat diberi permission modul admin yang sebelumnya tersedia, tetapi `module.user-management` tetap hanya untuk superadmin.

## 6. Kebutuhan Fungsional

### 6.1 Instalasi dan Model

- Gunakan `spatie/laravel-permission` yang kompatibel dengan Laravel 12 dan PHP 8.2+.
- Model `User` menggunakan trait `HasRoles`.
- Guard permission adalah `web`.
- Kolom `users.role`, `isAdmin()`, dan `isSuperAdmin()` tetap berjalan.
- Superadmin mendapat bypass permission secara implisit.

### 6.2 Management User

Superadmin dapat mencentang permission pada halaman edit user. Form menyediakan checkbox untuk seluruh 9 permission.

Saat disimpan, permission user disinkronkan menggunakan mekanisme setara `syncPermissions()`. Permission yang tidak dicentang dihapus.

Aturan:

- Hanya superadmin yang dapat membuka dan menyimpan Management User.
- Request permission harus divalidasi terhadap daftar permission guard `web`.
- Permission invalid ditolak.
- Non-superadmin tidak boleh memperoleh akses Management User hanya dengan mengirim permission tersebut.
- User baru role `user` mendapat empat permission default.
- Seeder tidak boleh menimpa permission custom ketika dijalankan ulang.

### 6.3 Proteksi Route

Setiap route modul admin dan user memakai middleware permission yang sesuai. Proteksi berlaku untuk halaman, export, endpoint AJAX, dan operasi POST/PUT/PATCH/DELETE.

User tanpa permission menerima HTTP 403. Superadmin selalu dilewatkan.

### 6.4 Sidebar

Sidebar hanya menampilkan menu jika user memiliki permission terkait. User default melihat Dashboard, Checklist Saya, Daily Activity, dan Riwayat. Menu lain muncul setelah permission diberikan.

Penyembunyian menu tidak menggantikan middleware backend.

### 6.5 Redirect Login

Setelah login, arahkan user ke halaman pertama yang dapat diakses: Dashboard, Checklist Saya, Daily Activity, atau Riwayat. Jangan membuat redirect loop atau mengarahkan user ke halaman 403.

## 7. Non-Functional Requirements

- **Security:** Otorisasi dilakukan di server menggunakan middleware/Gate.
- **Consistency:** Nama permission dan label berasal dari satu registry/konstanta.
- **Backward compatibility:** Role legacy dan superadmin tetap kompatibel.
- **Idempotency:** Seeder dapat dijalankan berulang tanpa duplikasi atau reset permission custom.
- **Performance:** Manfaatkan cache permission Spatie untuk menghindari query berulang.
- **Usability:** Checkbox memiliki label, status checked yang benar, dan pesan validasi.

## 8. Out of Scope

- Migrasi penuh dari role legacy ke role Spatie.
- Permission granular per aksi seperti `asset.create` atau `asset.delete`.
- Permission berbasis departemen/cabang.
- Approval workflow dan audit log detail.
- Pengelolaan permission melalui API publik.

## 9. Kriteria Penerimaan

1. Package dan migration Spatie berhasil dipasang.
2. Sembilan permission terdaftar tanpa duplikasi setelah seeding berulang.
3. User baru role `user` memiliki tepat empat default permission.
4. User tanpa permission Asset, Submission, Document Maker, atau Laporan menerima HTTP 403.
5. User dengan permission Asset dapat mengakses seluruh route Asset yang diizinkan oleh role legacy.
6. Sidebar mengikuti permission route dan tidak menampilkan menu yang tidak tersedia.
7. Superadmin dapat membuka semua modul tanpa assignment manual.
8. Hanya superadmin yang dapat mengelola user dan permission.
9. Checkbox edit user memuat state tersimpan dan sinkronisasi menghapus permission yang di-uncheck.
10. Export dan endpoint mutasi juga terlindungi permission.
11. Feature test authorization, default permission, seeder, dan sidebar lulus jika driver database tersedia.

## 10. Metrik Keberhasilan

| Indikator | Target |
| --- | --- |
| Akses tanpa izin | 0 route modul yang dapat dibuka tanpa permission. |
| Ketepatan default user | 100% sesuai empat permission default. |
| Konsistensi sidebar-route | 100% menu mengikuti permission route. |
| Duplikasi permission | 0 setelah seeder dijalankan ulang. |

## 11. Keputusan

- Permission diberikan langsung per user.
- Superadmin menjadi otorisasi tertinggi melalui role legacy dan bypass permission.
- Management User hanya untuk superadmin.
- Riwayat memiliki permission tersendiri meskipun aktif pada default user.