# Panduan Desain & Layout (UI/UX)
*Referensi utama: `resources/views/in_process/index.blade.php`*

Dokumen ini adalah panduan baku untuk Model AI atau Developer saat membuat *view* atau fitur baru di dalam sistem QC-Project agar UI/UX tetap seragam, modern, dan tidak berat saat di-render.

## 1. Grid & Struktur Halaman Dasar
- Aplikasi menggunakan framework **Bootstrap 4** yang telah dikustomisasi.
- **Card Wrapper**: Semua tabel atau form utama wajib dibungkus di dalam `<div class="card shadow mb-4 border-0">`.
- Hindari membuat UI yang memakan layar penuh tanpa padding. Gunakan container standar.

## 2. Standar Tabel (Data Table)
*Acuan Baku Mutlak: `resources/views/in_process/index.blade.php`*

Semua tabel data (DataTables maupun HTML matrix table reguler) **WAJIB** mengadopsi standar CSS dan markup yang sama persis dengan `in_process/index.blade.php`. Dilarang menggunakan styling tabel konvensional Bootstrap (`.table-bordered`) yang tebal, kaku, dan lambat di-render.

### A. Struktur HTML Wrapper & Table
```html
<div class="table-responsive">
    <table class="table table-hover" id="checksheetTable" width="100%" cellspacing="0">
        <thead>...</thead>
        <tbody>...</tbody>
    </table>
</div>
```

### B. Spesifikasi CSS Standar Tabel
```css
/* 1. Wrapper Bebas Border & Box-Shadow */
.table-responsive {
    max-height: none !important;
    overflow-x: auto !important;
    overflow-y: visible !important;
    border: none !important;
    box-shadow: none !important;
}

/* 2. Format Dasar Tabel (Borderless Structure) */
#checksheetTable {
    border-collapse: separate !important;
    border-spacing: 0 !important;
    border: none !important;
    width: 100% !important;
    table-layout: auto !important;
}

/* 3. Garis Pemisah Kolom Tipis Halus (#f1f5f9) */
#checksheetTable td, 
#checksheetTable th {
    border-left: none !important;
    border-right: 1px solid #f1f5f9 !important;
}

/* 4. Body Cell (TD) - Rapat, Ringan, & Modern */
#checksheetTable tbody td {
    border-bottom: 1px solid #f1f5f9 !important;
    border-top: none !important;
    vertical-align: middle !important;
    color: #334155 !important;
    font-size: 0.60rem !important;
    padding: 2px 4px !important;
    line-height: 1.1 !important;
}

/* 5. Header (TH) Sticky Baku (#f8fafc + Border #e2e8f0) - BEBAS BACKGROUND WARNA */
/* PENTING: layouts/admin.blade.php memiliki background default #4e73df (biru). */
/* Semua thead th tabel checksheet WAJIB meng-override ke #f8fafc secara mutlak (!important) */
#checksheetTable > thead > tr > th {
    position: -webkit-sticky !important;
    position: sticky !important;
    background-color: #f8fafc !important; /* DILARANG memakai background warna biru/hijau/dsb */
    background-clip: padding-box !important;
    color: #475569 !important;
    font-weight: 700 !important;
    text-transform: uppercase;
    font-size: 0.58rem !important;
    letter-spacing: 0.1px;
    padding: 3px 5px !important;
    border-left: none !important;
    border-right: 1px solid #e2e8f0 !important;
    border-bottom: 1px solid #e2e8f0 !important;
    vertical-align: middle !important;
    line-height: 1.1 !important;
    white-space: nowrap !important;
    box-shadow: inset 0 -1px 0 #cbd5e1;
}

/* 6. Hover Baris Halus */
#checksheetTable tbody tr:hover {
    background-color: #f1f5f9 !important;
    transition: background-color 0.2s ease;
}

/* 7. Tombol & Badge Kompak di Dalam Tabel */
#checksheetTable .btn {
    min-width: 0 !important;
    padding: 0.1rem 0.35rem !important;
    font-size: 0.58rem !important;
    margin: 0px !important;
}
#checksheetTable .badge {
    font-size: 0.58rem !important;
    padding: 0.1rem 0.3rem !important;
}

/* 8. Aturan Sticky untuk Header 2-Tingkat (Jika Memakai Grouping/Rowspan) */
#checksheetTable > thead > tr:nth-child(1) > th {
    top: 0 !important;
    z-index: 105 !important;
    height: 24px !important; 
}
#checksheetTable > thead > tr:nth-child(2) > th {
    top: 24px !important; 
    z-index: 104 !important;
    height: 20px !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important;
}
#checksheetTable > thead > tr:nth-child(1) > th[rowspan="2"] {
    top: 0 !important;
    height: 44px !important; /* Total = height baris 1 + baris 2 */
    z-index: 106 !important;
}
```

### C. Warna Status Penilaian (Judgment)
- **OK**: Teks atau badge Hijau (`text-success` atau `#198754` / `#10b981`).
- **NG**: Teks atau badge Merah (`text-danger` atau `#dc3545` / `#ef4444`). Latar belakang sel bernilai NG menggunakan `#fef2f2`.
- **Item Tidak Ada (NA)**: Abu-abu (`text-muted` atau `#64748b`). Background `#f1f5f9`.

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
