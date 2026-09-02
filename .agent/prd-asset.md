# Product Requirement Document (PRD)

## Modul Manajemen Asset IT

**Produk:** IT Checklist

**Organisasi:** PT Tera Data Indonusa Tbk. (TDI) - IT Department

**Pemilik Dokumen:** IT Operations & Engineering Team

**Tanggal Dokumen:** 2 September 2026

**Versi:** 1.0.0

**Status:** Proposed

## 1. Ringkasan

Modul Manajemen Asset IT menyediakan inventaris terpusat untuk perangkat dan perlengkapan IT. Admin dapat menambah, melihat, mengubah, mencari, memfilter, dan menghapus data aset. Superadmin juga dapat mengelola master kategori aset sehingga data inventaris tetap konsisten.

Modul ini menjadi bagian dari area admin yang sudah ada pada aplikasi IT Checklist dan menggunakan autentikasi serta role-based access control aplikasi saat ini.

## 2. Masalah dan Tujuan

### 2.1 Masalah

- Data inventaris aset IT belum dikelola dalam satu modul aplikasi yang terstruktur.
- Identitas aset, seperti kode barang, nomor inventaris, dan serial number, sulit dicari dan diverifikasi secara cepat.
- Kategori aset dapat ditulis tidak konsisten apabila tidak memakai master data.
- Lokasi dan jumlah aset tidak dapat dipantau secara efisien.

### 2.2 Tujuan

- Menyediakan sumber data tunggal untuk aset IT.
- Mempercepat pencarian aset berdasarkan identitas, kategori, merk, atau lokasi.
- Menjaga konsistensi kategori aset dengan master kategori.
- Mendukung pencatatan aset secara lengkap untuk kebutuhan operasional dan audit inventaris.

## 3. Pengguna dan Hak Akses

| Peran | Akses |
| --- | --- |
| `superadmin` | Melihat, menambah, mengubah, dan menghapus aset; mengelola kategori aset. |
| `admin` | Melihat, menambah, mengubah, dan menghapus aset; melihat daftar kategori untuk kebutuhan form aset. |
| `user` | Tidak memiliki akses ke modul Asset maupun API/route admin terkait. |

Semua halaman modul berada di bawah prefix `/admin` dan dilindungi autentikasi. Kebijakan ini mengikuti middleware `role:admin,superadmin` yang sudah digunakan aplikasi.

## 4. Ruang Lingkup

### 4.1 In Scope

- CRUD data aset.
- CRUD master kategori aset untuk `superadmin`.
- Kategori default yang tersedia setelah deployment:
  - Tools
  - Switch
  - Access Point
  - Router
  - Printer
  - Others
- Pencarian dan filter daftar aset.
- Pagination daftar aset.
- Validasi server-side dan pesan kesalahan pada form.
- Konfirmasi sebelum aset atau kategori dihapus.
- Pengamanan penghapusan kategori yang masih dipakai oleh aset.

### 4.2 Out of Scope

- Upload foto atau dokumen aset.
- QR code, barcode, scan perangkat, atau import/export Excel/PDF.
- Riwayat perpindahan lokasi, peminjaman, depresiasi, atau maintenance aset.
- Akses modul untuk role `user`.
- Integrasi dengan sistem procurement atau ticketing.

## 5. Kebutuhan Fungsional

### 5.1 Master Kategori Asset

Superadmin dapat membuka halaman kategori asset untuk melihat, menambah, mengubah nama, dan menghapus kategori.

Aturan kategori:

- Nama kategori wajib diisi, maksimum 100 karakter, dan unik tanpa membedakan kapitalisasi.
- Seeder menyediakan enam kategori default pada bagian 4.1.
- Nama kategori `Access Point` memakai ejaan tersebut sebagai normalisasi dari input kebutuhan "Acess Point".
- Kategori tidak dapat dihapus jika masih direlasikan dengan satu atau lebih aset. Sistem menampilkan pesan yang menjelaskan alasan penolakan.
- Pengubahan nama kategori langsung tercermin pada aset yang terhubung karena aset menyimpan relasi kategori, bukan salinan teks kategori.

### 5.2 Tambah Asset

Admin dan superadmin dapat membuat aset baru melalui halaman atau form tambah asset.

| Field | Nama Teknis | Tipe | Wajib | Aturan |
| --- | --- | --- | --- | --- |
| Kategori Aset | `asset_category_id` | Select | Ya | Harus memilih kategori aktif/tersedia. |
| Nama | `name` | Text | Ya | Maksimum 255 karakter. |
| Tahun Pembelian | `purchase_year` | Number | Ya | Tahun integer dari 1900 hingga tahun berjalan. |
| Merk | `brand` | Text | Ya | Maksimum 100 karakter. |
| Type | `type` | Text | Ya | Maksimum 100 karakter. |
| Kode Barang | `item_code` | Text | Ya | Maksimum 100 karakter dan unik. |
| No Invent | `inventory_number` | Text | Ya | Maksimum 100 karakter dan unik. |
| SN | `serial_number` | Text | Ya | Maksimum 150 karakter dan unik. |
| Jumlah | `quantity` | Number | Ya | Integer minimum 1. |
| Lokasi | `location` | Text | Ya | Maksimum 255 karakter. |

Setelah data valid disimpan, pengguna kembali ke daftar aset dengan notifikasi sukses.

### 5.3 Daftar dan Pencarian Asset

Halaman daftar aset menampilkan tabel paginasi dengan kolom:

- Kategori
- Nama
- Merk / Type
- Kode Barang
- No Invent
- SN
- Tahun Pembelian
- Jumlah
- Lokasi
- Aksi

Kemampuan pencarian dan filter:

- Pencarian kata kunci pada nama, merk, type, kode barang, nomor inventaris, serial number, dan lokasi.
- Filter berdasarkan kategori aset.
- Filter berdasarkan lokasi.
- Filter berdasarkan tahun pembelian.
- Filter dapat digunakan bersamaan.
- Nilai filter dan kata kunci dipertahankan saat berpindah halaman pagination.
- Urutan daftar default adalah aset terbaru dibuat, kemudian ID terbaru.

### 5.4 Detail dan Edit Asset

- Setiap baris aset menyediakan aksi untuk melihat atau mengubah data aset.
- Form edit memuat nilai tersimpan dan memakai validasi yang sama dengan form tambah.
- Aturan unik kode barang, nomor inventaris, dan serial number mengabaikan record aset yang sedang diedit.
- Pembaruan yang berhasil menampilkan notifikasi sukses dan menampilkan data terbaru pada daftar/detail.

### 5.5 Hapus Asset

- Setiap aset menyediakan aksi hapus.
- Pengguna harus mengonfirmasi penghapusan sebelum request dikirim.
- Setelah berhasil dihapus, data aset tidak lagi tampil dalam daftar dan sistem menampilkan notifikasi sukses.
- Penghapusan bersifat hard delete untuk rilis pertama. Audit trail/soft delete berada di luar ruang lingkup.

## 6. UX dan Navigasi

- Tambahkan menu **Asset** pada sidebar admin, dekat menu operasional seperti Daily Activity.
- Menu hanya terlihat untuk `admin` dan `superadmin` karena layout admin hanya dapat diakses kedua role tersebut.
- Halaman menggunakan layout `resources/views/layouts/admin.blade.php`, Bootstrap, Font Awesome, dan gaya aplikasi yang sudah ada.
- Tombol tambah asset menggunakan ikon tambah; aksi edit dan hapus menggunakan ikon yang familiar serta `title`/label aksesibel.
- Tabel harus responsif. Pada layar kecil, tabel dapat dibungkus area horizontal scroll tanpa menumpuk isi kolom.
- Empty state menjelaskan bahwa belum ada data aset dan menyediakan aksi tambah aset bagi pengguna yang berhak.

## 7. Kebutuhan Non-Fungsional

- **Keamanan:** Seluruh endpoint memakai autentikasi, middleware role, proteksi CSRF, validasi Laravel, dan escaping Blade standar.
- **Integritas data:** Foreign key memastikan aset selalu merujuk ke kategori yang valid. Unique index melindungi kode barang, nomor inventaris, dan serial number dari duplikasi.
- **Kinerja:** Query daftar memuat relasi kategori secara eager loading dan menggunakan index untuk foreign key serta field filter yang relevan.
- **Kompatibilitas:** Tidak menambah library baru. Implementasi memakai Laravel 12, PHP 8.2+, Blade, Bootstrap, Font Awesome, dan PHPUnit yang telah tersedia.
- **Aksesibilitas:** Semua input memiliki label, pesan validasi dapat dibaca, dan aksi destruktif membutuhkan konfirmasi.

## 8. Kriteria Penerimaan

1. Setelah migration dan seeder dijalankan, keenam kategori default tersedia tanpa duplikasi ketika seeder dijalankan ulang.
2. Superadmin dapat membuat, mengubah, dan menghapus kategori yang tidak dipakai aset.
3. Sistem menolak penghapusan kategori yang masih mempunyai aset dan data aset tetap tidak berubah.
4. Admin dan superadmin dapat membuat aset dengan semua field wajib yang valid.
5. Sistem menolak data aset dengan kategori tidak valid, tahun di luar rentang, jumlah kurang dari satu, atau nilai unik yang duplikat.
6. Admin dan superadmin dapat mengubah seluruh field aset dan nilai unik milik aset sendiri tetap dapat dipertahankan ketika edit.
7. Admin dan superadmin dapat menghapus aset setelah konfirmasi.
8. Daftar aset menampilkan kategori, seluruh identitas aset, jumlah, dan lokasi secara akurat.
9. Search dan seluruh filter dapat digunakan bersama serta pagination mempertahankan parameter query.
10. Role `user` menerima HTTP 403 ketika mengakses route asset admin.
11. Seluruh test feature modul Asset lulus bersama test suite aplikasi yang sudah ada.

## 9. Metrik Keberhasilan

| Indikator | Target |
| --- | --- |
| Kelengkapan data aset baru | 100% field wajib terisi dan tervalidasi. |
| Waktu pencarian aset | Hasil daftar terfilter tersedia dalam kurang dari 1 detik pada volume operasional normal. |
| Duplikasi identitas inventaris | 0 duplikasi kode barang, nomor inventaris, dan serial number. |
| Kepatuhan akses | 0 akses modul Asset oleh role `user`. |

## 10. Asumsi dan Keputusan Terbuka

- Satu record merepresentasikan satu jenis/kelompok aset pada satu lokasi; field `quantity` mencatat jumlah unit. Sistem belum memecah serial number per unit ketika jumlah lebih dari satu.
- Karena setiap asset membutuhkan serial number yang unik, rilis pertama mengharuskan `quantity = 1` untuk aset yang memiliki serial number individual. Jika kebutuhan bisnis memerlukan jumlah lebih dari satu dengan serial number per unit, model data perlu dikembangkan menjadi tabel unit aset terpisah.
- Untuk menjaga konsistensi dengan field SN wajib dan unik, implementasi awal akan memvalidasi `quantity` sebagai minimum 1, tetapi product owner perlu mengonfirmasi apakah satu record dapat mewakili beberapa unit berserial berbeda. Sampai dikonfirmasi, operator sebaiknya mencatat tiap unit berserial sebagai record terpisah.