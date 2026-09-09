# Dokumentasi: Komponen Header dengan Tanda Tangan (Signature Header)

> **Referensi implementasi resmi** untuk semua halaman di `qc-project` yang memerlukan header
> dengan No. Dokumen dan kolom tanda tangan (Dibuat / Diperiksa / Diketahui).

---

## Gambaran Struktur

```
[ Logo ] [ Judul Halaman ] [ No. Dokumen | Signature Table ]
```

Bagian kanan (`No. Dokumen + Signature`) menggunakan **nested table** dengan `height: 100%`
agar border atas dan bawah kedua tabel sejajar secara otomatis.

---

## Arsitektur Tabel

```
<table>  ← Tabel utama header (width: 100%)
  <tr>
    <td> Logo </td>
    <td> Judul </td>
    <td padding:0>  ← Wrapper kanan (NO padding!)
      <table>  ← Tabel baris wrapper (width:100%; height:100%)
        <tr>
          <td> No. Dokumen Table </td>   ← height:100%
          <td> Signature Table   </td>   ← height:100%
        </tr>
      </table>
    </td>
  </tr>
</table>
```

> **Kunci utama** agar border sejajar: wrapper `<td>` luar menggunakan `padding: 0 !important`
> dan `vertical-align: top`. Kedua tabel anak menggunakan `height: 100%`.

---

## Tabel Referensi: Perbedaan Index vs Print

| Property | Index (Web) | Print (PDF) |
|---|---|---|
| **Border color** | `#dee2e6` (abu-abu) | `#000` (hitam) |
| **Font size tabel dokumen** | `0.65rem` | `7.5pt` |
| **Font size nama** | `0.63rem` | `6.5pt` |
| **Font size jabatan** | `0.63rem` | `6.5pt` |
| **Lebar kolom signature** | `120px` | `100px` |
| **Lebar kolom Tgl.** | `28px` | `22px` |
| **Total lebar signature tabel** | `388px` | `322px` |
| **Signature max-height** | `68px` | `58px` |
| **Signature max-width** | `130px` | `110px` |
| **Signature transform** | `scale(1.35)` | `scale(1.35)` |
| **Tinggi sel signature** | `58px` | `48px` |
| **Padding sel signature** | `4px` | `3px` |
| **Judul alignment** | `<h1>` kiri | `<div>` rata tengah |

---

## Ketentuan Kolom Dibuat (QC vs QS)

| Area / Menu | Nama | Jabatan | Asset File |
|---|---|---|---|
| **Quality Control (QC / Checksheet)**<br>*(Incoming, Sub Parts, In-Process, Checksheet, dll)* | **Arief H** | Spv. QC | `signatures/arif.png` |
| **Quality System (QS)**<br>*(Kalibrasi, Verification, Schedule, Problem Logs, dll)* | **Mida H** | Spv. QS | `signatures/mida.png` |

---

## File Aset Signature

Lokasi file: `public/signatures/`

| File | Nama | Jabatan | Kolom | Area Menu |
|---|---|---|---|---|
| `arif.png` | Arief H | Spv. QC | Dibuat | Quality Control (Checksheet) |
| `mida.png` | Mida H | Spv. QS | Dibuat | Quality System |
| `iwan.png` | Iwan S | Asst. Mgr Quality | Diperiksa | Semua Area |
| `desti.png` | Desti K | Mgr. Quality | Diketahui | Semua Area |

> File signature harus berformat **PNG dengan background transparan**.
> `mix-blend-mode: multiply` mengandalkan alpha channel PNG untuk menyatu dengan background putih.
> `transform: scale(1.35); transform-origin: center;` membuat tanda tangan tampak sedikit melampaui kolom secara realistis tanpa merubah ukuran sel tabel.

---

## Aturan Penting — Jangan Dilanggar

| # | Property | Alasan |
|---|---|---|
| 1 | `mix-blend-mode: multiply` pada `<img>` | Membuat background PNG putih transparan, tanda tangan terlihat bersih |
| 2 | `transform: scale(1.35); transform-origin: center;` | Efek meluap keluar batas sel tanpa mengubah dimensi/lebar kolom tabel |
| 3 | `padding: 0 !important` pada `<td>` wrapper kanan | Padding diatur di dalam tabel anak; jika ada di sini akan merusak alignment |
| 4 | `table-layout: fixed` pada Signature Table | Memastikan kolom tetap pada lebar `<th width="...">` yang sudah ditentukan |
| 5 | `height: 100%` pada kedua tabel anak | Membuat border atas-bawah No. Dokumen dan Signature sejajar |
| 6 | `white-space: nowrap` pada baris jabatan | Mencegah "Asst. Mgr Quality" wrap ke baris kedua |

---

## Template Lengkap: Index / Web View

Gunakan template ini untuk file `index.blade.php`. Border color: `#dee2e6`.

```html
{{-- ================================================================ --}}
{{-- HEADER UTAMA — salin seluruh blok ini ke halaman baru            --}}
{{-- Sesuaikan: [JUDUL], [NO_DOK], [TGL_TERBIT], [REVISI], [HALAMAN] --}}
{{-- ================================================================ --}}
<table style="width:100%; border-collapse:collapse; border:1px solid #dee2e6;">
    <tr>

        {{-- ===== [1] KOLOM LOGO ===== --}}
        <td style="width:75px; border:1px solid #dee2e6; padding:5px;
                   text-align:center; vertical-align:middle;">
            <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo"
                 style="max-width:58px; max-height:44px; object-fit:contain;">
        </td>

        {{-- ===== [2] KOLOM JUDUL ===== --}}
        {{-- Sesuaikan teks judul --}}
        <td style="border:1px solid #dee2e6; padding:5px 8px;
                   text-align:center; vertical-align:middle;">
            <h1 class="mb-0 font-weight-bold text-uppercase text-gray-800"
                style="font-size:0.85rem; letter-spacing:0.3px;">
                [JUDUL HALAMAN]
            </h1>
        </td>

        {{-- ===== [3] KOLOM KANAN: No. Dokumen + Signatures ===== --}}
        {{-- PENTING: padding:0 !important; vertical-align:top     --}}
        <td style="width:1px; border:1px solid #dee2e6;
                   padding:0 !important; vertical-align:top; white-space:nowrap;">

            {{-- Wrapper baris — menyamakan tinggi kedua tabel anak --}}
            <table style="border-collapse:collapse; width:100%; height:100%; border:none; margin:0;">
                <tr style="height:100%;">

                    {{-- ===== [3A] Tabel No. Dokumen ===== --}}
                    {{-- padding: kiri 4px, kanan 6px                   --}}
                    <td style="border:none; padding:4px 6px 4px 4px;
                                vertical-align:top; height:100%; white-space:nowrap;">
                        <table style="border-collapse:collapse; border:1px solid #dee2e6;
                                      font-size:0.65rem; background:#fff; height:100%; width:100%;">
                            <tr>
                                <td style="border:1px solid #dee2e6; padding:2px 6px;
                                           font-weight:600; color:#495057; white-space:nowrap;">No. Dokumen</td>
                                <td style="border:1px solid #dee2e6; padding:2px 4px;
                                           text-align:center; color:#495057;">:</td>
                                <td style="border:1px solid #dee2e6; padding:2px 6px;
                                           font-weight:700; color:#212529; white-space:nowrap;">
                                    {{-- Sesuaikan nomor dokumen --}}
                                    {{ strtolower($plantCode) === 'jakarta' ? 'QC-JKT-F-052' : 'QC-KRW-F-052' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="border:1px solid #dee2e6; padding:2px 6px;
                                           font-weight:600; color:#495057; white-space:nowrap;">Tgl. Terbit</td>
                                <td style="border:1px solid #dee2e6; padding:2px 4px;
                                           text-align:center; color:#495057;">:</td>
                                <td style="border:1px solid #dee2e6; padding:2px 6px;
                                           font-weight:600; color:#212529; white-space:nowrap;">25/03/2015</td>
                            </tr>
                            <tr>
                                <td style="border:1px solid #dee2e6; padding:2px 6px;
                                           font-weight:600; color:#495057; white-space:nowrap;">Revisi / Tgl</td>
                                <td style="border:1px solid #dee2e6; padding:2px 4px;
                                           text-align:center; color:#495057;">:</td>
                                <td style="border:1px solid #dee2e6; padding:2px 6px;
                                           font-weight:600; color:#212529; white-space:nowrap;">1 / 21/03/2018</td>
                            </tr>
                            <tr>
                                <td style="border:1px solid #dee2e6; padding:2px 6px;
                                           font-weight:600; color:#495057; white-space:nowrap;">Halaman</td>
                                <td style="border:1px solid #dee2e6; padding:2px 4px;
                                           text-align:center; color:#495057;">:</td>
                                <td style="border:1px solid #dee2e6; padding:2px 6px;
                                           font-weight:600; color:#212529; white-space:nowrap;">1 / 1</td>
                            </tr>
                        </table>
                    </td>

                    {{-- ===== [3B] Tabel Signatures ===== --}}
                    {{-- padding: kiri 0, kanan 4px                      --}}
                    {{-- Konfigurasi lebar:                               --}}
                    {{--   total width  = 388px                           --}}
                    {{--   Tgl.         = 28px                            --}}
                    {{--   Dibuat/Diperiksa/Diketahui = 120px x3         --}}
                    {{--   120px cukup untuk "Asst. Mgr Quality" nowrap  --}}
                    <td style="border:none; padding:4px 4px 4px 0;
                                vertical-align:top; height:100%;">
                        <table style="border-collapse:collapse; border:1px solid #dee2e6;
                                      text-align:center; font-size:0.65rem; line-height:1.1;
                                      background:#fff; height:100%;
                                      table-layout:fixed; width:388px;">
                            <thead>
                                <tr>
                                    <th style="border:1px solid #dee2e6; padding:3px 2px;
                                               font-weight:600; color:#495057; background:#fff; width:28px;">Tgl.</th>
                                    <th style="border:1px solid #dee2e6; padding:3px 6px;
                                               font-weight:600; color:#495057; background:#fff; width:120px;">Dibuat</th>
                                    <th style="border:1px solid #dee2e6; padding:3px 6px;
                                               font-weight:600; color:#495057; background:#fff; width:120px;">Diperiksa</th>
                                    <th style="border:1px solid #dee2e6; padding:3px 6px;
                                               font-weight:600; color:#495057; background:#fff; width:120px;">Diketahui</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- BARIS 1: Gambar tanda tangan --}}
                                <tr>
                                    {{-- Tanggal pengesahan (teks vertikal, rowspan=3) --}}
                                    <td rowspan="3"
                                        style="border:1px solid #dee2e6; padding:2px;
                                               vertical-align:middle; text-align:center; width:28px;">
                                        <div style="writing-mode:vertical-rl;
                                                    transform:rotate(180deg);
                                                    -webkit-transform:rotate(180deg);
                                                    white-space:nowrap;
                                                    font-size:0.58rem; font-weight:400;
                                                    margin:0 auto; color:#6c757d;">
                                            06-Jan-26 {{-- ← Sesuaikan tanggal --}}
                                        </div>
                                    </td>

                                    {{-- Tanda tangan Dibuat --}}
                                    {{-- Gunakan arif.png (Arief H / Spv. QC) untuk Checksheet / QC --}}
                                    {{-- Gunakan mida.png (Mida H / Spv. QS) untuk Quality System --}}
                                    <td style="border:1px solid #dee2e6; padding:4px;
                                               vertical-align:middle; height:58px; background:#fff; text-align:center;">
                                        <img src="{{ asset('signatures/arif.png') }}" alt="Arief H"
                                             style="max-height:68px; max-width:130px;
                                                    object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                    </td>

                                    {{-- Tanda tangan Diperiksa --}}
                                    <td style="border:1px solid #dee2e6; padding:4px;
                                               vertical-align:middle; height:58px; background:#fff; text-align:center;">
                                        <img src="{{ asset('signatures/iwan.png') }}" alt="Iwan S"
                                             style="max-height:68px; max-width:130px;
                                                    object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                    </td>

                                    {{-- Tanda tangan Diketahui --}}
                                    <td style="border:1px solid #dee2e6; padding:4px;
                                               vertical-align:middle; height:58px; background:#fff; text-align:center;">
                                        <img src="{{ asset('signatures/desti.png') }}" alt="Desti K"
                                             style="max-height:68px; max-width:130px;
                                                    object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                    </td>
                                </tr>

                                {{-- BARIS 2: Nama (bold) --}}
                                <tr>
                                    <td style="border:1px solid #dee2e6; padding:2px 6px;
                                               font-weight:600; font-size:0.63rem;
                                               color:#212529; white-space:nowrap;">Arief H</td>
                                    <td style="border:1px solid #dee2e6; padding:2px 6px;
                                               font-weight:600; font-size:0.63rem;
                                               color:#212529; white-space:nowrap;">Iwan S</td>
                                    <td style="border:1px solid #dee2e6; padding:2px 6px;
                                               font-weight:600; font-size:0.63rem;
                                               color:#212529; white-space:nowrap;">Desti K</td>
                                </tr>

                                {{-- BARIS 3: Jabatan (white-space:nowrap wajib) --}}
                                <tr>
                                    <td style="border:1px solid #dee2e6; padding:2px 6px;
                                               font-size:0.63rem; color:#495057; white-space:nowrap;">Spv. QC</td>
                                    <td style="border:1px solid #dee2e6; padding:2px 6px;
                                               font-size:0.63rem; color:#495057; white-space:nowrap;">Asst. Mgr Quality</td>
                                    <td style="border:1px solid #dee2e6; padding:2px 6px;
                                               font-size:0.63rem; color:#495057; white-space:nowrap;">Mgr. Quality</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>

                </tr>
            </table>
        </td>
    </tr>
</table>
```

---

## Template Lengkap: Print View

Gunakan template ini untuk file `print.blade.php`. Border color: `#000`.

```html
{{-- ================================================================ --}}
{{-- HEADER PRINT — salin seluruh blok ini ke halaman print baru      --}}
{{-- Sesuaikan: [JUDUL], [NO_DOK], [TGL_TERBIT], [REVISI], [HALAMAN] --}}
{{-- ================================================================ --}}
<table class="table table-bordered mb-3"
       style="width:100%; border-collapse:collapse; border:1px solid #000 !important;">
    <tr>

        {{-- ===== [1] KOLOM LOGO ===== --}}
        <td width="80" class="text-center align-middle"
            style="border:1px solid #000 !important; padding:5px;">
            <img src="{{ asset('master item/ipp.jpg') }}" height="40">
        </td>

        {{-- ===== [2] KOLOM JUDUL (rata tengah) ===== --}}
        <td class="align-middle"
            style="border:1px solid #000 !important; padding:5px;
                   text-align:center; vertical-align:middle;">
            <div style="font-size:11pt; font-weight:700; color:#000;
                        text-align:center; margin-bottom:2px;">
                [JUDUL HALAMAN] - {{ $year }}
            </div>
            <div style="font-size:9pt; font-weight:600; color:#000; text-align:center;">
                PLANT {{ strtoupper($plantCode) }}
            </div>
        </td>

        {{-- ===== [3] KOLOM KANAN: No. Dokumen + Signatures ===== --}}
        <td width="420" class="small p-0 align-middle"
            style="border:1px solid #000 !important;
                   padding:0 !important; white-space:nowrap; vertical-align:top;">

            <table style="border-collapse:collapse; width:100%; height:100%; border:none; margin:0;">
                <tr style="height:100%;">

                    {{-- ===== [3A] Tabel No. Dokumen ===== --}}
                    <td style="border:none; padding:4px 6px 4px 4px;
                                vertical-align:top; height:100%; white-space:nowrap;">
                        <table style="border-collapse:collapse; border:1px solid #000;
                                      font-size:7.5pt; line-height:1.2; background:#fff;
                                      height:100%; width:100%;">
                            <tr>
                                <td style="border:1px solid #000; padding:2px 6px;
                                           font-weight:600; color:#495057; white-space:nowrap;">No. Dokumen</td>
                                <td style="border:1px solid #000; padding:2px 4px;
                                           text-align:center; color:#495057;">:</td>
                                <td style="border:1px solid #000; padding:2px 6px;
                                           font-weight:700; color:#212529; white-space:nowrap;">
                                    {{ strtolower($plantCode) === 'jakarta' ? 'QC-JKT-F-052' : 'QC-KRW-F-052' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="border:1px solid #000; padding:2px 6px;
                                           font-weight:600; color:#495057; white-space:nowrap;">Tgl. Terbit</td>
                                <td style="border:1px solid #000; padding:2px 4px;
                                           text-align:center; color:#495057;">:</td>
                                <td style="border:1px solid #000; padding:2px 6px;
                                           font-weight:600; color:#212529; white-space:nowrap;">25/03/2015</td>
                            </tr>
                            <tr>
                                <td style="border:1px solid #000; padding:2px 6px;
                                           font-weight:600; color:#495057; white-space:nowrap;">Revisi / Tgl</td>
                                <td style="border:1px solid #000; padding:2px 4px;
                                           text-align:center; color:#495057;">:</td>
                                <td style="border:1px solid #000; padding:2px 6px;
                                           font-weight:600; color:#212529; white-space:nowrap;">1 / 21/03/2018</td>
                            </tr>
                            <tr>
                                <td style="border:1px solid #000; padding:2px 6px;
                                           font-weight:600; color:#495057; white-space:nowrap;">Halaman</td>
                                <td style="border:1px solid #000; padding:2px 4px;
                                           text-align:center; color:#495057;">:</td>
                                <td style="border:1px solid #000; padding:2px 6px;
                                           font-weight:600; color:#212529; white-space:nowrap;">1 / 1</td>
                            </tr>
                        </table>
                    </td>

                    {{-- ===== [3B] Tabel Signatures ===== --}}
                    {{-- Konfigurasi lebar:                               --}}
                    {{--   total width  = 322px                           --}}
                    {{--   Tgl.         = 22px                            --}}
                    {{--   Dibuat/Diperiksa/Diketahui = 100px x3         --}}
                    <td style="border:none; padding:4px 4px 4px 0;
                                vertical-align:top; height:100%;">
                        <table style="border-collapse:collapse; border:1px solid #000;
                                      text-align:center; font-size:7pt; line-height:1.1;
                                      background:#fff; height:100%;
                                      table-layout:fixed; width:322px;">
                            <thead>
                                <tr>
                                    <th style="border:1px solid #000; padding:2px 2px;
                                               font-weight:600; color:#495057; background:#fff; width:22px;">Tgl.</th>
                                    <th style="border:1px solid #000; padding:2px 4px;
                                               font-weight:600; color:#495057; background:#fff; width:100px;">Dibuat</th>
                                    <th style="border:1px solid #000; padding:2px 4px;
                                               font-weight:600; color:#495057; background:#fff; width:100px;">Diperiksa</th>
                                    <th style="border:1px solid #000; padding:2px 4px;
                                               font-weight:600; color:#495057; background:#fff; width:100px;">Diketahui</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- BARIS 1: Gambar tanda tangan --}}
                                <tr>
                                    {{-- Tanggal vertikal (rowspan=3) --}}
                                    <td rowspan="3"
                                        style="border:1px solid #000; padding:2px;
                                               vertical-align:middle; text-align:center; width:22px;">
                                        <div style="writing-mode:vertical-rl;
                                                    transform:rotate(180deg);
                                                    -webkit-transform:rotate(180deg);
                                                    white-space:nowrap;
                                                    font-size:6.5pt; font-weight:400;
                                                    margin:0 auto; color:#6c757d;">
                                            06-Jan-26
                                        </div>
                                    </td>

                                    {{-- Tanda tangan Dibuat --}}
                                    {{-- Gunakan arif.png (Arief H / Spv. QC) untuk Checksheet / QC --}}
                                    {{-- Gunakan mida.png (Mida H / Spv. QS) untuk Quality System --}}
                                    <td style="border:1px solid #000; padding:3px;
                                               vertical-align:middle; height:48px; background:#fff; text-align:center;">
                                        <img src="{{ asset('signatures/arif.png') }}" alt="Arief H"
                                             style="max-height:58px; max-width:110px;
                                                    object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                    </td>
                                    <td style="border:1px solid #000; padding:3px;
                                               vertical-align:middle; height:48px; background:#fff; text-align:center;">
                                        <img src="{{ asset('signatures/iwan.png') }}" alt="Iwan S"
                                             style="max-height:58px; max-width:110px;
                                                    object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                    </td>
                                    <td style="border:1px solid #000; padding:3px;
                                               vertical-align:middle; height:48px; background:#fff; text-align:center;">
                                        <img src="{{ asset('signatures/desti.png') }}" alt="Desti K"
                                             style="max-height:58px; max-width:110px;
                                                    object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                    </td>
                                </tr>

                                {{-- BARIS 2: Nama --}}
                                <tr>
                                    <td style="border:1px solid #000; padding:1px 5px;
                                               font-weight:600; font-size:6.5pt;
                                               color:#212529; white-space:nowrap;">Arief H</td>
                                    <td style="border:1px solid #000; padding:1px 5px;
                                               font-weight:600; font-size:6.5pt;
                                               color:#212529; white-space:nowrap;">Iwan S</td>
                                    <td style="border:1px solid #000; padding:1px 5px;
                                               font-weight:600; font-size:6.5pt;
                                               color:#212529; white-space:nowrap;">Desti K</td>
                                </tr>

                                {{-- BARIS 3: Jabatan --}}
                                <tr>
                                    <td style="border:1px solid #000; padding:1px 5px;
                                               font-size:6.5pt; color:#495057; white-space:nowrap;">Spv. QC</td>
                                    <td style="border:1px solid #000; padding:1px 5px;
                                               font-size:6.5pt; color:#495057; white-space:nowrap;">Asst. Mgr Quality</td>
                                    <td style="border:1px solid #000; padding:1px 5px;
                                               font-size:6.5pt; color:#495057; white-space:nowrap;">Mgr. Quality</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>

                </tr>
            </table>
        </td>
    </tr>
</table>
```

---

## Checklist Implementasi di Halaman Baru

- [ ] Salin template index ke file `index.blade.php` baru
- [ ] Salin template print ke file `print.blade.php` baru
- [ ] Ganti `[JUDUL HALAMAN]` dengan nama dokumen yang sesuai
- [ ] Sesuaikan No. Dokumen, Tgl. Terbit, Revisi/Tgl, Halaman
- [ ] Sesuaikan tanggal di kolom vertikal (misal: `06-Jan-26`)
- [ ] Pastikan `public/signatures/mida.png`, `iwan.png`, `desti.png` tersedia
- [ ] **JANGAN ubah** struktur wrapper, `height:100%`, `padding:0 !important`
- [ ] **JANGAN hapus** `mix-blend-mode:multiply` pada tag `<img>` tanda tangan
- [ ] **JANGAN hapus** `table-layout:fixed` pada Signature Table
- [ ] Test tampilan di browser (index) dan print preview (print)

---

*Referensi implementasi aktif:*
- `resources/views/calibration/schedule/index.blade.php`
- `resources/views/calibration/schedule/print.blade.php`
