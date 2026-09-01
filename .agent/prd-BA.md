Product Requirement Document (PRD)

Sistem Management Berita Acara Digital & AI Auto-Generator

Nama Produk: Sistem Management Berita Acara Digital (E-Berita Acara TDI)

Organisasi: PT Tera Data Indonusa Tbk. (TDI) — IT Department

Pemilik Dokumen: IT Operations & Engineering Team

Supervisor Penanggung Jawab: Iqbal Taufik Akbar (Supervisor IT)

Tanggal Dokumen: 24 Agustus 2026

Versi Document: 1.0.0

Status Produk: In Production / Live

1. Executive Summary & Vision

1.1 Ringkasan Eksekutif

Sistem Management Berita Acara Digital adalah aplikasi web-based yang dirancang untuk mengotomatisasi, mengelola, dan mendokumentasikan seluruh kegiatan operasional IT (seperti relokasi server, pemeliharaan jaringan, perbaikan infrastruktur, dan migrasi sistem) di PT Tera Data Indonusa Tbk.

Sistem ini mengintegrasikan Gemini AI (Google AI API) untuk mengubah draf/skenario pekerjaan mentah menjadi Berita Acara formal berstandar korporasi secara otomatis dalam waktu hitungan detik.

1.2 Visi Produk

Memodernisasi dokumentasi legal-operasional departemen IT dengan menghilangkan proses administratif manual, mempercepat pembuatan laporan hingga 80%, serta menjamin konsistensi format standar A4 untuk kebutuhan arsip dan verifikasi audit.

2. Problem Statement & Objectives

2.1 Masalah Utama (Problem Statement)

Pengerjaan Manual & Memakan Waktu: Staf IT Support membutuhkan waktu lama untuk menyusun kronologi detail dan format laporan formal pasca-kegiatan operasional/lembur.

Inkonsistensi Format: Dokumen Berita Acara antar personel sering kali memiliki format, tata bahasa, dan struktur yang berbeda-beda.

Penyimpanan Terfragmentasi: Berita Acara berbasis word processor lokal sering kali berserakan dan sulit dicari kembali saat kebutuhan verifikasi supervisor atau audit operasional.

2.2 Tujuan Utama (Product Objectives)

Efisiensi Waktu: Memangkas waktu penyusunan Berita Acara dari rata-rata 30-45 menit menjadi kurang dari 1 menit memanfaatkan Gemini AI.

Standardisasi Format: Menghasilkan dokumen A4 siap cetak dengan Kop Surat resmi PT Tera Data Indonusa Tbk. yang konsisten.

Sentralisasi Data: Menyediakan repositori digital terpusat dengan kemampuan pencarian cepat (instant search) dan penyaringan status (filter).

3. User Personas & Target Audience

User Persona

Peran Utama

Kebutuhan Utama

IT Support Staff



(Muhamad Habib Gusti / Master Fauza)

Pelaksana kegiatan teknis di lapangan

- Input kronologi cepat dari HP/Laptop.



- Generate otomatis via AI saat lelah pengerjaan lembur.



- Export/Print ke PDF instant.

IT Supervisor



(Iqbal Taufik Akbar)

Pengawas & Penanggung Jawab Operasional

- Dashboard ringkasan status pekerjaan.



- Kemampuan meninjau dokumen secara rinci (review mode).



- Menyetujui dan memvalidasi Berita Acara.

Vendor / External Partner



(IMV Technical Support)

Personel pendukung kegiatan teknis

- Tercantum dalam daftar personel formal.



- Memperoleh salinan dokumen PDF resmi yang tervalidasi.

4. Key Features & Functional Requirements

4.1 Features Matrix

+-----------------------------------------------------------------------+
|                 E-BERITA ACARA MANAGEMENT SYSTEM                      |
+-----------------------------------------------------------------------+
|  1. AI Auto-Generator  |  2. Full CRUD Engine  |  3. Print & PDF Engine|
|  - Gemini 3 Flash REST |  - Dynamic Personel   |  - A4 Portrait Layout |
|  - Prompt Structured   |  - Dynamic Timeline   |  - Kop Surat TDI      |
|  - JSON Parser Output  |  - Filter & Search    |  - Signatures Grid    |
+-----------------------------------------------------------------------+


4.2 Detail Spesifikasi Fungsional

Fungsionalitas 1: Gemini AI Auto-Generate

Input Skenario: User memasukkan paragraf singkat deskripsi pengerjaan (bebas/tidak terstruktur).

AI Processing: Mengirimkan system prompt ketat ke API Gemini 3 Flash untuk memparsing tanggal, lokasi, kronologi waktu, daftar tim, serta status akhir.

Auto-Populate: Mengisi form masukan (Form Modal) secara instan dengan data JSON berstruktur formal dari Gemini AI.

Fungsionalitas 2: Dashboard & Management (CRUD)

Create: Pembuatan BA secara manual maupun via AI.

Read / List: Tabel interaktif menampilkan Nomor BA, Judul, Tanggal, Waktu, Lokasi, Status (Selesai/Draft), dan badge penanda dokumen AI.

Update / Edit: Kemampuan memperbarui data personel, kronologi, status akhir, maupun perbaikan teks kapan saja.

Delete: Pengahapusan dokumen dengan dialog konfirmasi aman.

Search & Filter: Pencarian kata kunci waktu nyata (instant filter) berdasarkan Nomor BA, Judul, atau Lokasi.

Fungsionalitas 3: Document Preview & Print Engine (A4 Format)

Kop Surat Resmi: Menampilkan branding PT Tera Data Indonusa Tbk., unit kerja IT Department, dan detail kontak.

Tabel Terstruktur: Detail pelaksanaan, durasi operasional lembur, dan daftar personel terverifikasi.

Daftar Kronologi: Format poin-poin bertimeline jelas (Waktu - Detail Kegiatan).

Grid Tanda Tangan: Grid tanda tangan otomatis untuk Pelaksana Pekerjaan (IT TDI & Vendor) serta blok persetujuan Supervisor IT.

Export PDF: Fitur Native Print CSS yang mengisolasi tampilan cetak agar bersih dari UI web (No-Print CSS Isolation).

5. Non-Functional Requirements (NFR)

Performance: Halaman utama memuat data dalam waktu $< 1$ detik. Proses generate AI memerlukan waktu respons average $1.5 - 3$ detik.

Reliability & Fallback: Memiliki arsitektur Hybrid Storage. Jika server SQLite PHP tidak tersedia, sistem otomatis berpindah secara seamless ke LocalStorage browser agar aplikasi tetap dapat berjalan 100%.

Usability & UX: Interface responsif berbasis Tailwind CSS, ramah pengguna di perangkat desktop maupun mobile.

Compliance & Security: Data dokumen terlindungi di level backend SQLite. Input pengguna disanitasi menggunakan fungsi escape HTML untuk mencegah celah Cross-Site Scripting (XSS).

6. Release & Success Metrics

Indikator (KPI)

Target

Waktu Pembuatan BA

Memangkas durasi dari 30 menit menjadi $< 2$ menit

Adopsi Fitur AI

$> 80\%$ Berita Acara dibuat memanfaatkan Gemini AI Generator

Tingkat Kesalahan Format

$0\%$ eror format pada cetakan PDF A4

Ketersediaan Sistem (Uptime)

$99.9\%$ uptime dengan mekanisme hybrid fallback