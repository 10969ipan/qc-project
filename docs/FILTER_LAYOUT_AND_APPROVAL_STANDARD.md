# Dokumentasi Standar Layout Filter, Approval Status & UI Compact

Dokumen ini berisi pedoman resmi dan template kode standar untuk penyeragaman **tata letak (layout) filter**, **streamlining approval status**, **desain UI compact (1920x1080)**, dan **fitur cetak langsung (silent print)** pada seluruh modul checksheet & verifikasi aplikasi QC.

---

## 1. Pedoman Layout Filter & Card Header

### A. Aturan Penataan & Penamaan Header
1. **Posisi Label/Header**: Wajib diletakkan di **atas input field** (`d-flex flex-column align-items-start`).
2. **Penamaan Label Standar**:
   - `Part Name` (bukan `Part:`)
   - `Customer` (bukan `Cust:`)
   - `Tanggal` (bukan `Tgl:`) dengan format rentang **"dari - sampai"** (`start_date` **s/d** `end_date`)
   - `Shift`
   - `Inisial`
   - `Line / Mesin` (atau `Line / Meja`)
   - `QR Code`

### B. Urutan Field Filter Standar
1. **Part Name** (`item_id`)
2. **Customer** (`customer`)
3. **Tanggal** (`start_date` & `end_date`)
4. **Shift** (`shift`)
5. **Inisial** (`operator_initials`)
6. **Line / Mesin / Meja** (`line`)
7. 🔍 **[ Filter ]** & 🔄 **[ Reset ]** &rarr; **Tepat di samping Line/Meja** dengan jarak ekstra 2x space (`margin-left: 20px`).
8. **QR Code** (`qr_raw`) &rarr; **Khusus Mode Verifikasi Saja** (`request('view_mode') === 'verifikasi'`).
9. 📋 **[ Hasil Verifikasi ]** / 📄 **[ PDF ]** / 🖨️ **[ Cetak ]** &rarr; **Diposisikan paling kanan (`ml-auto`)**.

### C. Pemeliharaan State (`view_mode` & `plant`)
- Selalu cantumkan `<input type="hidden" name="plant" value="{{ request('plant') }}">` dan `<input type="hidden" name="view_mode" value="{{ request('view_mode') }}">` (jika ada).
- Tombol **Reset Filter** harus mempertahankan parameter `plant` dan `view_mode` yang sedang aktif.

### D. Judul Card Header
- Judul Card Header (misal: `DATA MASUK SUB ASSY` dan `DATA HASIL VERIFIKASI SUB ASSY`) dibuat **berwarna hitam (`text-dark`)**, **UPPERCASE (`text-uppercase`)**, dan **tanpa icon** agar tampilan konsisten dan bersih.

### E. Kolom Tabel QR-Code
- Kolom **QR-Code** pada tabel data (`<th>` dan `<td>`) **HANYA ditampilkan pada Mode Verifikasi** (`@if(request('view_mode') === 'verifikasi')`).
- Untuk data manual, kolom QR-Code disembunyikan/dihapus karena berisi data kosong.

---

## 2. Pedoman Streamlining Approval Status & Menu Actions

1. **Header Tabel**:
   - Gunakan `colspan="2"` pada header utama `<th colspan="2" class="align-middle">Approval Status</th>`.
   - Sub-header terdiri dari 2 kolom dengan lebar minimal (`min-width: 120px`):
     1. `Kashift QC` / `Kepala Regu` (dinamis sesuai konteks plant: Jakarta = Kepala Regu, Karawang = Kashift QC)
     2. `Supervisor QC`
2. **Format Cell Approval (Pencegahan Tampilan Tertumpuk)**:
   - Gunakan `white-space: nowrap; min-width: 120px;` pada `<td>` agar nama pemberi approval (misal: "Administrator" / "Ahmad Jaeni") tidak terpotong atau terurai menjadi beberapa baris.
   - Gunakan badge ukuran ringkas (`font-size: 0.65rem; padding: 2px 6px;`) yang proporsional dengan font tabel.
   - Bungkus nama dan tanggal approval dalam `<div>` berukuran halus (`font-size: 0.62rem; line-height: 1.2;`) untuk tampilan rapi dan tidak terlalu tinggi.
3. **Body Tabel (`<tbody>`)**:
   - Nonaktifkan/hapus kolom `<td>` untuk **Asst. Manager QC** dan **Manager QC**.
4. **Kolom Actions (Pengelompokan ke Menu Dropdown 3-Titik `:`)**:
   - **Khusus Role Admin (`$user->role === 'admin'`)**:
     - Seluruh tombol persetujuan (`Approve` & `Reject` untuk Kashift/Kepala Regu & Supervisor QC), `Status Approval`, `Edit`, serta `Hapus` dimasukkan ke dalam **menu dropdown 3-titik (`:`)**.
     - Hal ini membuat kolom **Actions** berukuran ringkas (`width: 50px`), rapi, dan tidak memakan lebar tabel.
   - **Role Non-Admin (Kashift QC, Kepala Regu, Supervisor QC)**:
     - User non-admin tetap melihat tombol `Approve` & `Reject` secara inline untuk role mereka masing-masing demi kecepatan approval rutin.

---

## 3. Stylings CSS Ultra-Compact View (Optimasi Layar 1920x1080)

Gunakan style CSS ringkas berikut pada bagian `@section('content')` atau file CSS terpisah untuk memastikan tabel dan elemen dapat memuat 10+ baris data tanpa perlu scroll halaman pada layar 1080p:

```css
<style>
    .table-responsive {
        max-height: 68vh !important;
        overflow: auto !important;
        border: none !important;
        box-shadow: inset 0 0 5px rgba(0,0,0,0.02);
    }
    #checksheetTable {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        border: none !important;
        width: 100% !important;
        table-layout: auto !important;
    }
    
    #checksheetTable td, #checksheetTable th {
        border-left: none !important;
        border-right: 1px solid #f1f5f9 !important;
    }

    #checksheetTable tbody td {
        border-bottom: 1px solid #f1f5f9 !important;
        border-top: none !important;
        vertical-align: middle !important;
        color: #334155 !important;
        font-size: 0.60rem !important;
        padding: 2px 4px !important;
        line-height: 1.1 !important;
    }

    /* Global TH sticky setup */
    #checksheetTable > thead > tr > th {
        position: -webkit-sticky !important;
        position: sticky !important;
        background-color: #f8fafc !important;
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

    #checksheetTable tbody tr:hover {
        background-color: #f1f5f9 !important;
        transition: background-color 0.2s ease;
    }

    #checksheetTable td.no-export {
        min-width: 0 !important;
        white-space: nowrap !important;
    }
    #checksheetTable .btn {
        min-width: 0 !important;
        padding: 0.1rem 0.3rem !important;
        font-size: 0.58rem !important;
        margin: 0px !important;
    }
    #checksheetTable .badge {
        font-size: 0.58rem !important;
        padding: 0.1rem 0.3rem !important;
    }

    /* Exact sticky heights */
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
        height: 44px !important; /* 24 + 20 */
        z-index: 106 !important;
    }
</style>
```

---

## 4. Fitur Cetak Langsung (Direct Silent Print)

Untuk menghindari terbukanya tab/halaman baru saat klik **Cetak**, gunakan pola berikut:
1. Hapus atribut `target="_blank"` pada elemen `<a>` tombol Cetak.
2. Tambahkan class `.btn-print-direct`.
3. Tambahkan script JS pendukung yang memuat URL cetak ke dalam iframe tersembunyi (*silent iframe*).

### Script JS Direct Print:
```javascript
// Direct Print (Tanpa Buka Halaman Baru & Tanpa Double Dialog)
$(document).on('click', '.btn-print-direct', function(e) {
    e.preventDefault();
    var printUrl = $(this).attr('href');
    if (!printUrl || printUrl === '#') return;

    var oldIframe = document.getElementById('silentPrintIframe');
    if (oldIframe) {
        oldIframe.parentNode.removeChild(oldIframe);
    }

    var iframe = document.createElement('iframe');
    iframe.id = 'silentPrintIframe';
    iframe.style.position = 'fixed';
    iframe.style.right = '0';
    iframe.style.bottom = '0';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = '0';
    iframe.style.opacity = '0';
    iframe.src = printUrl;

    document.body.appendChild(iframe);
});
```

---

## 5. Template Kode Standar (Blade View Form Filter & Actions)

```blade
<!-- Header Card Data -->
<div class="card shadow mb-2">
    <div class="card-header py-2 px-3">
        @if(request('view_mode') === 'verifikasi')
            <h6 class="m-0 font-weight-bold text-dark text-uppercase" style="font-size: 0.80rem;">DATA HASIL VERIFIKASI SUB ASSY</h6>
        @else
            <h6 class="m-0 font-weight-bold text-dark text-uppercase" style="font-size: 0.80rem;">DATA MASUK SUB ASSY</h6>
        @endif
    </div>
    <div class="card-body p-2">
        <form action="{{ route('ADMIN_ROUTE_NAME') }}" method="GET"
            class="d-flex flex-wrap align-items-end bg-light p-2 rounded mb-2 shadow-sm"
            style="gap: 8px; overflow-x: auto;" id="filterFormStandard">

            {{-- Preserve Context Parameters --}}
            <input type="hidden" name="plant" value="{{ request('plant') }}">
            @if(request('view_mode'))
                <input type="hidden" name="view_mode" value="{{ request('view_mode') }}">
            @endif

            <!-- 1. Field: Part Name -->
            <div class="d-flex flex-column align-items-start">
                <label class="mb-1 small font-weight-bold text-gray-700" style="font-size: 0.68rem;">Part Name</label>
                <div style="width: 180px;" class="custom-filter-wrapper">
                    <select name="item_id" id="filterItem" class="form-control form-control-sm border-0 shadow-sm d-none">
                        <option value="">Semua Part Name</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" {{ request('item_id') == $item->id ? 'selected' : '' }}>
                                {{ $item->name }} {{ $item->part_number ? '- '.$item->part_number : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- 2. Field: Customer -->
            <div class="d-flex flex-column align-items-start">
                <label class="mb-1 small font-weight-bold text-gray-700" style="font-size: 0.68rem;">Customer</label>
                <div style="width: 140px;" class="custom-filter-wrapper">
                    <select name="customer" id="filterCustomer" class="form-control form-control-sm border-0 shadow-sm d-none">
                        <option value="">Semua Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer }}" {{ request('customer') == $customer ? 'selected' : '' }}>{{ $customer }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- 3. Field: Tanggal (dari - sampai) -->
            <div class="d-flex flex-column align-items-start">
                <label class="mb-1 small font-weight-bold text-gray-700" style="font-size: 0.68rem;">Tanggal</label>
                <div class="d-flex align-items-center shadow-sm rounded bg-white overflow-hidden" style="border: 1px solid #e2e8f0;">
                    <input type="date" name="start_date" id="start_date" class="form-control form-control-sm border-0"
                        style="width: 120px; font-size: 0.70rem; height: 26px;" value="{{ request('start_date') }}" title="Dari Tanggal">
                    <span class="px-2 text-gray-500 font-weight-bold small">s/d</span>
                    <input type="date" name="end_date" id="end_date" class="form-control form-control-sm border-0"
                        style="width: 120px; font-size: 0.70rem; height: 26px;" value="{{ request('end_date') }}" title="Sampai Tanggal">
                </div>
            </div>

            <!-- 4. Field: Shift -->
            <div class="d-flex flex-column align-items-start">
                <label class="mb-1 small font-weight-bold text-gray-700" style="font-size: 0.68rem;">Shift</label>
                <div style="width: 95px;" class="custom-filter-wrapper">
                    <select name="shift" id="filterShift" class="form-control form-control-sm border-0 shadow-sm" style="font-size: 0.70rem; height: 26px;">
                        <option value="">Semua</option>
                        <option value="1" {{ request('shift') == '1' ? 'selected' : '' }}>Shift 1</option>
                        <option value="2" {{ request('shift') == '2' ? 'selected' : '' }}>Shift 2</option>
                        <option value="3" {{ request('shift') == '3' ? 'selected' : '' }}>Shift 3</option>
                    </select>
                </div>
            </div>

            <!-- 5. Field: Inisial -->
            <div class="d-flex flex-column align-items-start">
                <label class="mb-1 small font-weight-bold text-gray-700" style="font-size: 0.68rem;">Inisial</label>
                <div style="width: 120px;" class="custom-filter-wrapper">
                    <select name="operator_initials" id="filterInisial" class="form-control form-control-sm border-0 shadow-sm d-none">
                        <option value="">Semua Inisial</option>
                        @foreach($initials as $initial)
                            <option value="{{ $initial }}" {{ request('operator_initials') == $initial ? 'selected' : '' }}>{{ $initial }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- 6. Field: Line / Mesin / Meja -->
            <div class="d-flex flex-column align-items-start">
                <label class="mb-1 small font-weight-bold text-gray-700" style="font-size: 0.68rem;">Line / Mesin</label>
                <div style="width: 115px;" class="custom-filter-wrapper">
                    <select name="line" id="filterLine" class="form-control form-control-sm border-0 shadow-sm" style="font-size: 0.70rem; height: 26px;">
                        <option value="">Semua Line</option>
                        @foreach($lines as $l)
                            <option value="{{ $l }}" {{ request('line') == $l ? 'selected' : '' }}>
                                LINE-{{ $l }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Tombol Filter & Reset (Tepat di Samping Line/Meja dengan 2x Space) -->
            <div class="d-flex align-items-center" style="gap: 4px; align-self: flex-end; margin-bottom: 8px !important; margin-left: 20px;">
                <button type="submit" class="btn btn-primary btn-sm shadow-sm rounded-pill px-2 py-1 d-flex align-items-center" style="font-size: 0.68rem; height: 26px;" title="Cari Data">
                    <i class="fas fa-search fa-sm mr-1"></i> Filter
                </button>
                <a href="{{ route('ADMIN_ROUTE_NAME', array_merge(['plant' => request('plant')], request('view_mode') ? ['view_mode' => request('view_mode')] : [])) }}"
                    class="btn btn-secondary btn-sm shadow-sm rounded-pill px-2 py-1 d-flex align-items-center" style="font-size: 0.68rem; height: 26px;" title="Reset Filter">
                    <i class="fas fa-undo fa-sm mr-1"></i> Reset
                </a>
            </div>

            <!-- 7. Field: QR Code (Tampilkan HANYA untuk Mode Verifikasi) -->
            @if(request('view_mode') === 'verifikasi')
            <div class="d-flex flex-column align-items-start">
                <label class="mb-1 small font-weight-bold text-gray-700" style="font-size: 0.68rem;">QR Code</label>
                <div class="input-group input-group-sm shadow-sm rounded" style="width: 180px;">
                    <input type="text" name="qr_raw" id="filterQrRaw" class="form-control border-0"
                        placeholder="Scan/Ketik QR..." value="{{ request('qr_raw') }}" style="font-size: 0.70rem; height: 26px;">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-primary border-0" id="btnScanQRIndex" title="Scan QR Code" style="height: 26px; padding: 0 8px;">
                            <i class="fas fa-qrcode"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endif

            <!-- Tombol Navigasi & Ekspor (Paling Kanan) -->
            <div class="d-flex align-items-center ml-auto" style="gap: 4px; align-self: flex-end; margin-bottom: 8px !important;">
                <a href="{{ route('ADMIN_ROUTE_NAME', ['plant' => request('plant'), 'view_mode' => 'verifikasi']) }}"
                    class="btn btn-sm shadow-sm rounded-pill px-2 py-1 d-flex align-items-center" title="Hasil Verifikasi"
                    style="background-color: #6f42c1; color: white; font-size: 0.68rem; height: 26px;">
                    <i class="fas fa-clipboard-check fa-sm mr-1"></i> Hasil Verifikasi
                </a>
                <a href="#" class="btn btn-danger btn-sm shadow-sm rounded-pill px-2 py-1 d-flex align-items-center" style="font-size: 0.68rem; height: 26px;" title="Export to PDF">
                    <i class="fas fa-file-pdf fa-sm mr-1"></i> PDF
                </a>
                <a href="#" class="btn btn-sm shadow-sm rounded-pill px-2 py-1 btn-print-direct d-flex align-items-center" title="Print"
                    style="background-color: #17a589; color: white; font-size: 0.68rem; height: 26px;">
                    <i class="fas fa-print fa-sm mr-1"></i> Cetak
                </a>
            </div>
        </form>
    </div>
</div>
```

---

## 6. Standar Spesifik Modul Double Tape Checksheet

Berikut adalah aturan standar penyeragaman khusus pada modul **Double Tape Checksheet**:

1. **Format Lot ID**:
   - Menampilkan **Lot ID (Tgl / Shift / Inisial)** yang bersumber dari data proses Injection (`injection_date`, `injection_shift`, `injection_initials`).
   - Jika data belum diisi saat pembuatan, data Lot ID dapat ditambahkan/diperbarui kapan saja melalui modal Edit.

2. **Format Checked Column**:
   - Menampilkan **Checked (Tgl / Shift / Inisial)** yang bersumber dari tanggal checksheet (`date`), shift (`shift`), dan inisial operator (`operator_initials`).

3. **Penyederhanaan Form Input (Create & Edit Modal)**:
   - Field input manual QC Plating disederhanakan/dihapus dari form create & edit modal untuk mengurangi redudansi data.

4. **Validasi & AJAX Respon Edit Modal**:
   - Proses pengeditan data dari modal edit diproses secara AJAX dengan pengembalian respon JSON terstruktur (`{ success: true, message: "..." }`).
   - Menyimpan dan memvalidasi daftar defect (`defect_types` & `defect_quantities`) serta user inspector pendukung.
   - Menggunakan penampil notifikasi sukses berbasis **SweetAlert (Swal)** sebelum merefresh tampilan tabel.

5. **Cache-Busting Script Assets**:
   - Pemanggilan file JavaScript (`double-tape.js`) di Blade template wajib menyertakan query parameter versioning `?v={{ time() }}` untuk mencegah browser menyimpan cache versi lama di local maupun server production.

