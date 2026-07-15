# Panduan Standardisasi UI/UX Modul Laporan QC

Dokumen ini adalah acuan teknis terperinci mengenai standardisasi *User Interface* (UI) dan logika data yang diterapkan pada menu **Incoming Material**. Dokumen ini dirancang sebagai **cetak biru (blueprint)** yang dapat Anda aplikasikan secara langsung (*copy-paste* lalu disesuaikan) saat menstandardisasi modul pelaporan lainnya.

---

## 1. Standardisasi Formulir Modal (Tambah & Edit)

Setiap Modal (baik `create.blade.php` maupun `edit_form.blade.php`) harus menganut struktur visual yang rapi, minimalis, dan terkelompok. Hindari tumpukan *field* input tanpa batas yang membingungkan.

### A. Pengelompokan (Sectioning)
Gunakan judul *section* untuk memisahkan grup data (contoh: Informasi Material, Kuantitas, dsb).

**Kode Template Section Header:**
```html
<div class="font-weight-bold text-primary mt-4 mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">
    INFORMASI MATERIAL & WAKTU
</div>
```

### B. Standardisasi Kelas Input (Input Fields & Labels)
Setiap elemen form harus menggunakan kombinasi kelas berikut untuk mencapai tampilan datar (*flat*), modern, dan *clean*:
- **Input text/number/select/date:** `form-control form-control-sm border-0 shadow-sm`
- **Label input:** `small font-weight-bold text-gray-700`

**Contoh Penerapan Input Standard:**
```html
<div class="row">
    <div class="col-md-4 form-group">
        <label class="small font-weight-bold text-gray-700">Tanggal Datang</label>
        <input type="date" class="form-control form-control-sm border-0 shadow-sm" name="tanggal_datang" required>
    </div>
</div>
```

### C. Elemen Informasi Readonly (Sebagai Referensi)
Beri warna latar belakang ringan (`bg-light`) dan parameter `readonly` untuk kolom yang dihitung secara otomatis (misal: Jam Before/After) atau diambil dari *database* (misal: Part No/Supplier). Ini sangat membantu integritas data saat edit.

**Contoh Penerapan Readonly:**
```html
<div class="col-md-3 form-group">
    <label class="small font-weight-bold text-gray-700">Part No</label>
    <input type="text" class="form-control form-control-sm border-0 shadow-sm bg-light" value="{{ $item->part_number ?? '-' }}" readonly>
</div>
```

### D. Layout Input Defect (List Dinamis)
Untuk list item dinamis seperti *Defect*, hilangkan *border*, *padding* besar, dan bayangan *box-shadow* pada *wrapper* barisnya. Baris harus terlihat menyatu dengan form utama.

**Kode Struktur Baris Defect (Tanpa Border/Bg):**
```html
<div class="row no-gutters mb-2 align-items-center">
    <div class="col-8 pr-1">
        <select class="form-control form-control-sm border-0 shadow-sm font-weight-bold" name="defect_types[]">
            <option value="">-- Pilih Defect --</option>
        </select>
    </div>
    <div class="col-3 pr-1">
        <input type="number" class="form-control form-control-sm border-0 shadow-sm text-center font-weight-bold" name="defect_quantities[]" placeholder="Qty">
    </div>
    <div class="col-1 text-center">
        <!-- Tombol Hapus Menggunakan Icon -->
        <button type="button" class="btn btn-link text-danger p-0"><i class="fas fa-times-circle"></i></button>
    </div>
</div>
```

---

## 2. Standardisasi Tampilan Laporan Tabel (`index.blade.php`)

Tabel data hasil laporan harus menggunakan tata letak *auto* untuk menghindari isi tabel saling tumpang tindih.

### A. Pengaturan CSS Tabel Utama
Tambahkan blok *style* ini ke bagian `<style>` laporan `index.blade.php`:

```css
#dataTableLaporan {
    border-collapse: separate !important;
    border-spacing: 0 !important;
    border: none !important;
    width: 100% !important;
    table-layout: auto !important; /* Mencegah kolom terjepit */
}

#dataTableLaporan tbody td {
    vertical-align: middle !important;
    color: #334155 !important;
    font-size: 0.68rem !important;
    padding: 4px 6px !important;
    white-space: nowrap !important; /* Teks memanjang, tabel menyesuaikan */
}
```

### B. Sticky Headers (Th)
*Header* tabel harus mengambang (*sticky*) saat di-*scroll* ke bawah dan selalu berada di rata tengah.

```css
#dataTableLaporan thead th {
    position: -webkit-sticky !important;
    position: sticky !important;
    top: 0 !important;
    z-index: 105 !important;
    background-color: #f8fafc !important;
    color: #475569 !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    font-size: 0.62rem !important;
    padding: 6px 12px !important;
    text-align: center !important; /* Rata Tengah */
    white-space: nowrap !important;
    border-top: 1px solid #e2e8f0 !important;
    border-bottom: 1px solid #e2e8f0 !important;
}
```

---

## 3. Logika & Integritas Data (Tingkat Controller / Blade)

### A. Penanganan Format Angka Desimal
Sistem berat (*Weight*) atau angka ukur (Qty, Komper, Sampling Size) sering tersimpan dengan *trailing zeroes* dari *database* (misal `20.00`). Hindari format desimal statis yang kaku, gunakan konversi murni *(float)* agar layar tampak bersih, kecuali memang ada nilai pecahannya.

**Contoh Rendering Blade (Tabel/Edit Form):**
```php
{{ (float) $item->quantity_kg }}
// Jika $item->quantity_kg bernilai 20.00 => Tampil '20'
// Jika $item->quantity_kg bernilai 20.50 => Tampil '20.5'
```

### B. Validasi Penghapusan Data Interaktif (JS)
Tinggalkan `confirm()` Javascript kuno. Saat menghapus data dari tabel, pastikan semua menggunakan format standar *SweetAlert2* dengan tombol API DELETE.

**Contoh Template JS SweetAlert2:**
```javascript
function deleteRow(url, csrf_token) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: 'Data yang dihapus tidak dapat dikembalikan!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74a3b', // Merah Ponytail standard
        cancelButtonColor: '#858796',  // Abu-abu Ponytail standard
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({ title: 'Menghapus...', allowOutsideClick: false });
            Swal.showLoading();
            // Lakukan eksekusi AJAX DELETE ke `url` di sini
        }
    });
}
```

---

## 4. Format Tombol (Action Bar & Tabel)
Semua tombol *action* dalam tabel sebaiknya berukuran sangat mini agar selaras dengan kerapatan tabel `font-size: 0.68rem`.

**Format Tombol Mini di TD:**
```html
<button class="btn btn-outline-primary btn-sm shadow-sm rounded" style="padding: 2px 6px; font-size: 0.65rem;">
    <i class="fas fa-edit"></i>
</button>
```
