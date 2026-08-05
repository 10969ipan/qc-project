# Panduan Standardisasi UI/UX & Dokumentasi Kode Modul Incoming Material

Dokumen ini adalah acuan teknis terperinci mengenai standardisasi *User Interface* (UI), logika kalkulasi data otomatis, integrasi audio validasi, dan penanganan tindakan tabel (*Action Dropdown & Modal Edit*) yang diterapkan pada menu **Incoming Material**. Dokumen ini dirancang sebagai **cetak biru (*blueprint*)** yang dapat diaplikasikan secara langsung saat menstandardisasi modul pelaporan QC lainnya.

---

## 1. Standardisasi Formulir Modal (Tambah & Edit)

Setiap Modal (baik `create.blade.php` maupun `edit_form.blade.php`) menganut struktur visual yang rapi, minimalis, dan terkelompok (*sectioning*).

### A. Pengelompokan (Sectioning)
Gunakan judul *section* untuk memisahkan grup data (contoh: INFORMASI MATERIAL & WAKTU, DATA KEDATANGAN, KUANTITAS & HASIL, DETAIL DEFECT).

**Kode Template Section Header:**
```html
<div class="font-weight-bold text-primary mt-4 mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">
    INFORMASI MATERIAL & WAKTU
</div>
```

### B. Standardisasi Kelas Input (Input Fields & Labels)
Setiap elemen form menggunakan kombinasi kelas berikut untuk mencapai tampilan datar (*flat*), modern, dan *clean*:
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

### C. Elemen Informasi Readonly
Gunakan warna latar belakang ringan (`bg-light`) dan atribut `readonly` untuk kolom yang dihitung otomatis atau diambil dari *master database* (misal: Jam Before/After, Part No, Supplier).

```html
<div class="col-md-3 form-group">
    <label class="small font-weight-bold text-gray-700">Part No</label>
    <input type="text" class="form-control form-control-sm border-0 shadow-sm bg-light" value="{{ $item->part_number ?? '-' }}" readonly>
</div>
```

---

## 2. Kalkulasi Otomatis (Auto-Calculation Logic)

Sistem secara otomatis mendeduksi parameter kuantitas dan *Sampling Size* (AQL) untuk mempercepat proses entri dan menghindari kesalahan manual.

### Alur Kalkulasi `Qty (Kg)` $\rightarrow$ `Komper/Karung` $\rightarrow$ `Sampling Size`:
1. **Input `Qty (Kg)` (`#lotQtyInput` / `#quantityInput`)**:
   Saat nilai Qty (Kg) diisi atau discan (misal `2000` kg), event `input change` menghitung jumlah kemasan karung secara otomatis dengan pembagian 25 kg/karung:
   $$\text{Komper/Karung} = \lceil \frac{\text{Qty (Kg)}}{25} \rceil$$
   *(Contoh: $2000 / 25 = 80$ Karung)*.
2. **Kalkulasi Sampling Size (AQL)**:
   Perubahan pada `#komperKarungInput` secara otomatis memicu pencarian tabel AQL:
   $$\text{Sampling Size} = \text{AQL\_TABLE.getSampleSize}(\text{totalKarung})$$
3. **Pemicuan Independen**:
   Pengeditan manual pada `Sampling Size` (`#totalCheckInput`) hanya memperbarui kuantitas pemeriksaan dan judgment tanpa merusak/menimpa isi kolom `Komper/Karung`.

```javascript
// Hitung Otomatis Komper/Karung dari Qty (Kg)
$(document).on('input change', '#lotQtyInput, #quantityInput', function() {
    const qtyKg = parseFloat($(this).val()) || 0;
    if ($('#komperKarungInput').length > 0) {
        const totalKarung = qtyKg > 0 ? Math.ceil(qtyKg / 25) : 0;
        $('#komperKarungInput').val(totalKarung).trigger('input');
    }
});

// Hitung Ukuran Sampel AQL dari Komper/Karung
$('#komperKarungInput').on('input', function() {
    const totalKarung = parseFloat($(this).val()) || 0;
    const sampleSize = AQL_TABLE.getSampleSize(totalKarung);
    $('#totalCheckInput').val(sampleSize).trigger('input');
});
```

---

## 3. Sistem Audio Validasi & Notifikasi Scan

Seluruh pemicuan auditif pada pemindaian QR Code dikonsolidasikan melalui helper global `window.playAppAudio(type)` pada `layouts/admin.blade.php`:

| Parameter `type` | File Audio MP3 | Kejadian / Status Validasi |
| :--- | :--- | :--- |
| `'success'` | `QR CODE BERHASIL DI SCAN.mp3` | Pemindaian QR Code valid & item berhasil ditemukan |
| `'format_error'` | `FORMAT QR CODE SALAH, SCAN QR INTERNAL.mp3` | Format string QR Code tidak sesuai standar internal |
| `'duplicate_saved'` | `QR CODE DUPLICATE, SUDAH DI SIMPAN SEBELUM NYA.mp3` | Unique ID QR Code sudah pernah tersimpan di database |
| `'duplicate_list'` | `QR CODE SUDAH ADA DI LIST.mp3` | QR Code sudah ada dalam daftar antrean sementara |
| `'item_not_found'` | `ITEM PART INI TIDAK ADA DI CHECKSHEET INI.mp3` | Item hasil parsing QR tidak terdaftar di master item checksheet |

Setiap hasil sukses menampilkan SweetAlert2 modal feedback tanpa mengganggu alur scanning:
```javascript
Swal.fire({
    icon: "success",
    title: "QR Berhasil Discan",
    text: "Item otomatis terpilih.",
    timer: 1500,
    showConfirmButton: false,
});
```

---

## 4. Action Bar & Action Dropdown 3-Titik

### A. Penggunaan Partial `@include('partials.action_dropdown')`
Kolom Aksi pada tabel laporan `index.blade.php` diselaraskan menggunakan menu *3-dot dropdown* standar:

```html
<td class="align-middle text-center text-nowrap no-export" style="min-width: 160px;">
    @if($loop->first)
        @include('partials.bulk_approve_button')
    @endif

    {{-- Tombol Approve/Reject Berdasarkan Role --}}
    @if($canApproveKashift) ... @endif
    @if($canApproveSupervisor) ... @endif

    @include('partials.action_dropdown', [
        'canEdit'      => $canEdit,
        'canDelete'    => $canDelete,
        'editUrl'      => route('incoming.materials.edit', array_merge(['id' => $cs->id], request()->all())),
        'deleteRoute'  => route('incoming.materials.destroy', array_merge(request()->query(), ['id' => $cs->id])),
        'deleteParams' => [],
        'statusUrl'    => auth()->user()->role === 'admin' && Route::has('admin.incoming.materials.edit_approval') ? route('admin.incoming.materials.edit_approval', $cs->id) : null,
    ])
</td>
```

### B. Event Delegation Modal Edit (`.btn-edit-modal`)
Gunakan penanganan *delegated event* `$(document).on('click', '.btn-edit-modal', ...)` untuk memastikan klik pada dropdown aksi dinamis selalu memuat form `edit_form.blade.php` ke dalam `#editModal` (`.modal-xl`):

```javascript
$(document).on('click', '.btn-edit-modal', function(e) {
    e.preventDefault();
    var url = $(this).attr('href');
    $('#editModal').modal('show');
    $('#editModalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
    
    $.get(url, function(data) {
        $('#editModalBody').html(data);
    }).fail(function() {
        $('#editModalBody').html('<div class="alert alert-danger">Gagal memuat data. Silakan coba lagi.</div>');
    });
});
```

### C. Konfirmasi Hapus Data Global (`.btn-delete`)
Penghapusan data ditangani secara terpusat melalui `public/js/layouts/layouts-admin.js`. Saat pengguna memilih **Hapus** pada Action Dropdown:
1. Dialog SweetAlert2 konfirmasi warna merah (`#e74a3b`) ditampilkan.
2. Saat disetujui, form dikirimkan via metode HTTP `DELETE` (`@method('DELETE')`).

---

## 5. Form Validation Non-Native (`novalidate`)

Form entri dan edit menggunakan atribut `novalidate` untuk menonaktifkan balok pesan bawaan browser, dan digantikan oleh penanganan SweetAlert2 yang mengumpulkan seluruh *field* mandatory yang belum terisi.

```javascript
$('#editChecksheetForm').on('submit', function(e) {
    var isValid = true;
    var missingFields = [];

    $(this).find('input[required], select[required], textarea[required]').each(function() {
        if (!$(this).val()) {
            isValid = false;
            $(this).addClass('is-invalid');
            missingFields.push($(this).attr('name'));
        }
    });

    if (!isValid) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Data Belum Lengkap!',
            html: 'Mohon lengkapi seluruh kolom wajib yang bertanda merah.',
            confirmButtonColor: '#4e73df'
        });
    }
});
```

---

## 6. Verifikasi & QA Checklist Modul

- [x] Sintaks PHP & Blade valid (`php artisan view:cache`).
- [x] Javascript bebas dari kesalahan sintaks (`node -c public/js/checksheet/incoming-create.js`).
- [x] Kalkulasi `Qty (Kg) / 25` -> `Komper/Karung` -> `AQL Sampling Size` berfungsi presisi.
- [x] Audio validasi 5 jenis suara (`window.playAppAudio`) terintegrasi.
- [x] Action dropdown 3-titik dan modal edit (`.modal-xl`) beroperasi lancar.
- [x] Konfirmasi Hapus Data via SweetAlert2 berjalan aman dengan metode `DELETE`.
