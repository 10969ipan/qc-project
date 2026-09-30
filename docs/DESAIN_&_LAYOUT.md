# Panduan Desain & Layout (UI/UX)
*Referensi utama: `resources/views/in_process/index.blade.php`*

Dokumen ini adalah panduan baku untuk Model AI atau Developer saat membuat *view* atau fitur baru di dalam sistem QC-Project agar UI/UX tetap seragam, modern, dan tidak berat saat di-render.

## 1. Grid & Struktur Halaman Dasar
- Aplikasi menggunakan framework **Bootstrap 4** yang telah dikustomisasi.
- **Card Wrapper**: Semua tabel atau form utama wajib dibungkus di dalam `<div class="card shadow mb-4 border-0">`.
- Hindari membuat UI yang memakan layar penuh tanpa padding. Gunakan container standar.

## 2. Standar Tabel (Data Table)
- **Sticky Header**: Header tabel harus selalu *sticky* saat di-scroll ke bawah.
  - Gunakan class: `table-responsive` dengan CSS kustom agar `thead > tr > th` memiliki `position: sticky; top: 0; z-index: 10;`.
- **Styling Baris & Sel**:
  - Ukuran teks tabel umumnya menggunakan `font-size: 0.60rem;` atau `0.65rem;` untuk memuat banyak data tanpa memakan terlalu banyak ruang vertikal (*compact design*).
  - Hilangkan garis vertikal yang terlalu tebal; gunakan border-bottom tipis warna `#f1f5f9`.
- **Warna Status (Judgment)**:
  - OK: Teks atau *badge* warna Hijau (`text-success` atau `#10b981`).
  - NG: Teks atau *badge* warna Merah (`text-danger` atau `#dc2626`).

## 3. Komponen Filter & Pencarian
- Taruh form filter di dalam *card header* atau *card body* bagian atas dengan background yang halus (`bg-white` atau `bg-light`).
- Gunakan `input-group-sm` untuk menghemat ruang.
- Jangan gunakan tombol *Submit* konvensional untuk filter ringan; gunakan fungsi `onchange` pada input agar memicu *reload* secara mulus, atau gunakan sinkronisasi ke URL Parameter.

## 4. Modal (Dialogs)
- **Radius Membulat**: Gunakan `border-radius: 12px;` pada kontainer modal (seperti di modal Admin Hidden Items) untuk kesan lebih modern.
- **Header Modal**: Harus jelas dan memisahkan warna sesuai konteks.
  - Biru (`bg-info`) atau Ungu (`#7c3aed`) untuk pengaturan/konfigurasi.
  - Merah (`bg-danger`) untuk peringatan/konfirmasi penolakan.
- **Tombol Aksi (Footer)**: Tombol primary menggunakan shadow halus (`shadow-sm`, `rounded-pill` jika cocok) dengan efek warna yang premium, misalnya *purple* (`#7c3aed`).

## 5. Tombol & Ikon
- Gunakan ikon dari **FontAwesome** (`<i class="fas fa-..."></i>`).
- Beri spasi antara ikon dan teks tombol dengan margin `mr-1` atau `mr-2`.
- Hindari tombol raksasa, gunakan ukuran proporsional seperti `btn-sm`.

## 6. Efek Feedback & Loading
- Jangan membuat user bingung! Selalu sertakan efek loading saat proses penyimpanan:
  - Ganti teks tombol menjadi `<i class="fas fa-spinner fa-spin"></i> Loading...` via JavaScript saat formulir di-submit.
- Untuk Alert dan Notifikasi sukses/gagal, gunakan **SweetAlert2** (`Swal.fire`). Hindari `alert()` bawaan browser.

## 7. Floating Action / Bulk Action
- Untuk aksi yang melibatkan banyak baris data (*Bulk Delete*, *Bulk Approve*), gunakan *Float Menu* yang muncul di bagian bawah layar (lihat `bulkActionMenu`).
- Animasi pemunculan harus halus menggunakan `.fadeIn()` atau CSS *transition*.
