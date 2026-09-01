Technical & Design Specifications Document (Design Doc)

Architecting E-Berita Acara Digital with Gemini AI Integration

Sistem Target: Sistem Management Berita Acara Digital (PT Tera Data Indonusa Tbk.)

Penulis: Lead Software Architect / IT Department

Tanggal: 24 Agustus 2026

Status: Final & Approved

1. System Architecture Overview

Sistem ini mengusung pola arsitektur Hybrid Single-File Web Application. Pendekatan ini menggabungkan Server-Side Logic (PHP Native + PDO SQLite) dan Client-Side Dynamic Controller (Vanilla JS + LocalStorage Fallback) dalam satu unit terintegrasi.

+---------------------------------------------------------------------------+
|                              CLIENT BROWSER                               |
|                                                                           |
|  +--------------------+   +---------------------+   +------------------+  |
|  | Tailwind UI Layout |   | Modal & Form Engine |   | Print Engine A4  |  |
|  +---------+----------+   +----------+----------+   +--------+---------+  |
|            |                         |                       |            |
|            +-------------------------+-----------------------+            |
|                                      |                                    |
|                   +------------------v------------------+                 |
|                   |   Client-Side Storage Controller   |                 |
|                   +------------------+------------------+                 |
|                                      |                                    |
+--------------------------------------|------------------------------------+
                                       |
                   +-------------------+-------------------+
                   |                                       |
                   v (HTTP / REST API)                     v (External REST)
+--------------------------------------+   +--------------------------------+
|          PHP BACKEND SERVER          |   |       GOOGLE GEMINI AI API     |
|  (index.php + SQLite Engine)         |   |  (gemini-3-flash-preview)      |
|                                      |   |                                |
|  - API Routing GET/POST              |   |  - System Prompt Processing    |
|  - SQLite Database (berita_acara.db) |   |  - JSON Schema Standardizing   |
+--------------------------------------+   +--------------------------------+


2. Technology Stack

Layer

Teknologi

Alasan Pemilihan

Frontend Framework

HTML5, Tailwind CSS (CDN), FontAwesome 6

Tanpa proses kompilasi (zero build step), cepat, dan sangat responsif.

Backend Runtime

PHP 8.x Native

Kompatibilitas tinggi dengan server internal korporat (Apache/Nginx/XAMPP).

Database

SQLite via PHP PDO

Database berbasis file yang ringan, tanpa memerlukan konfigurasi DB server terpisah.

Fallback Storage

HTML5 localStorage

Menjamin aplikasi tetap berfungsi 100% meskipun berada pada lingkungan peramban steril/sandbox.

AI Processing

Google Gemini API (gemini-3-flash-preview)

Model mutakhir berkecepatan tinggi dengan kemampuan Structured JSON Output.

3. Database Schema Design (SQLite)

Database disimpan secara lokal pada file berita_acara.db.

CREATE TABLE IF NOT EXISTS berita_acara (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nomor_ba TEXT NOT NULL,
    judul TEXT NOT NULL,
    tanggal TEXT NOT NULL,
    waktu TEXT,
    lokasi TEXT,
    departemen TEXT DEFAULT 'IT Department',
    pembuka TEXT,
    kronologi TEXT,       -- JSON Array String: ["08.00: Prep", "11.31: Dismount"]
    personel TEXT,        -- JSON Array String: [{"nama":"", "jabatan":"", "instansi":""}]
    status_akhir TEXT,
    supervisor TEXT,
    status TEXT DEFAULT 'Draft',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);


4. Gemini AI Integration Architecture

4.1 Request Workflow

User memasukkan deskripsi skenario pada aiModal.

Client membentuk payload HTTP POST ke endpoint https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash-preview:generateContent.

Parameter responseMimeType: "application/json" diaktifkan untuk memastikan output selalu sesuai dengan skema JSON yang dibutuhkan.

4.2 System Prompt Contract

Anda adalah asisten administrasi resmi PT. Tera Data Indonusa Tbk. (TDI). Tugas Anda adalah membuat draf Berita Acara formal berdasarkan input deskripsi pekerjaan singkat dari pengguna.
Kembalikan JSON terstruktur dengan format berikut tanpa teks tambahan di luar JSON:
{
    "nomor_ba": "Format seperti '085/BA-IT/TDI/VIII/2026'",
    "judul": "Judul singkat pekerjaan",
    "tanggal": "Format 'Hari, Tanggal Bulan Tahun'",
    "waktu": "Contoh '08.00 WIB s.d. 16.00 WIB'",
    "lokasi": "Nama tempat / data center",
    "departemen": "Nama Departemen (default: IT Department)",
    "pembuka": "Paragraf pembuka formal bahasa Indonesia",
    "personel": [
        { "nama": "Nama Personel", "jabatan": "Jabatan", "instansi": "PT. Tera Data Indonusa Tbk. atau Vendor" }
    ],
    "kronologi": [
        "Waktu: Deskripsi rinci kegiatan"
    ],
    "status_akhir": "Contoh: ONLINE / NORMAL OPERATION / SELESAI",
    "supervisor": "Nama Supervisor (default: Iqbal Taufik Akbar)"
}


5. UI/UX & CSS Layout Engine

5.1 Print CSS Engine (Isolation Strategy)

Untuk memastikan cetakan PDF A4 presisi tanpa elemen antarmuka web (seperti tombol, header, atau modal background), diterapkan aturan @media print sebagai berikut:

@media print {
    /* Sembunyikan seluruh elemen di luar konten modal cetak */
    body * { 
        visibility: hidden; 
    }
    #printModalContent, #printModalContent * { 
        visibility: visible; 
    }
    #printModalContent {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 0;
        box-shadow: none !important;
        border: none !important;
    }
    .no-print { 
        display: none !important; 
    }
    @page { 
        size: A4 portrait; 
        margin: 15mm; 
    }
}


6. Security & Exception Handling

XSS Prevention: Seluruh data teks yang dipasang ke tabel atau modal dirender melalui fungsi pengaman escapeHtml() untuk menetralkan karakter berbahaya (<, >, &, ").

API Key Security: Pada lingkungan produksi terdistribusi, request Gemini AI disarankan disalurkan melalui PHP backend proxy (api_proxy.php) untuk menjaga kerahasiaan API Key dari paparan client-side script.

Graceful Fallback: Sistem secara otomatis mendeteksi ketersediaan modul SQLite PHP (extension_loaded('pdo_sqlite')). Jika modul terlepas, aplikasi tidak crash melainkan beralih ke engine localStorage.

7. Future Roadmap & Enhancements

V2.0 - Multi-Tenant Authentication: Menambahkan sistem Login RBAC (Role-Based Access Control) terpisah untuk Pelaksana (User) dan Penanggung Jawab (Supervisor).

V2.1 - Digital Signature Pad: Mengintegrasikan tanda tangan digital berbasis canvas (e-Signature HTML5) langsung pada dokumen.

V2.2 - Multi-Image Attachment: Menambahkan modul upload gambar langsung ke database SQLite (base64) untuk foto bukti fisik pengerjaan.