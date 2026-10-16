# List Perbaikan Kode Per Tanggal (8 - 16 Oktober 2026)

Berikut adalah daftar perbaikan dan pembaruan kode per tanggal secara ringkas:

---

### 📅 8 Oktober 2026
- **Kepatuhan Operator**: Implementasi input multi-plan shift, perataan layout cetak, dan sinkronisasi periode jadwal bulanan.
- **Kalibrasi**: Standardisasi layout filter bar dan penyesuaian scroll tabel jadwal kalibrasi.

---

### 📅 9 Oktober 2026
- **Durability Test**: Standardisasi header dokumen format ISO, penyesuaian layout filter, dan update tanda tangan Mida.
- **Filter Checksheet**: Filter dropdown Part Name & Customer disesuaikan dinamis agar hanya memunculkan data yang ada pada modul terkait.
- **Kepatuhan Operator**: Pengubahan dropdown pemilihan operator menjadi input pencarian teks interaktif.

---

### 📅 12 Oktober 2026
- **Approval Durability**: Penyesuaian aturan sampling (min. 1 per customer), pelepasan fallback tanggal script, dan penghapusan label trial di header.
- **Approval Helper**: Perbaikan bug `undefined variable $query` pada fungsi `getSamplingQuotaStatus`.
- **Filter Durability**: Filter Customer & Kategori disaring agar hanya menampilkan data riil dari laporan durability.
- **Optimasi In-Process Checksheet**:
  - Penambahan covering index database untuk percepatan filter dan list index.
  - Implementasi *precomputed dimension flags* (status dimensi OK/NG) dan background cache warming (interval 15 menit).
  - Eager loading relasi `sap_code`, `dimension_standards`, dan `initials` (eliminasi query N+1).
  - Sinkronisasi wadah error notifikasi pada reject modal.
- **Optimasi Filter Global**: Optimalisasi query filter dengan `whereExists`, perpanjangan durasi cache, dan pembatasan batas data (*max results*).
- **Performa Approval**: Optimasi kalkulasi kuota sampling dan antrean prefetch di browser.

---

### 📅 13 Oktober 2026
- **Approval Sampling**: Penghapusan cache kuota sampling agar tombol bulk approve terbuka secara instan/realtime saat kuota terpenuhi.
- **Durability Bulk Approve**: Perubahan perhitungan syarat kuota per kategori serta perbaikan issue *ambiguous column id*.
- **Optimasi Navigasi**: Eager loading menu navigasi dan optimalisasi query kuota approval di backend.

---

### 📅 14 Oktober 2026
- **Filter Bulk Approval**: Perbaikan sinkronisasi filter dan validasi rentang tanggal pada modal bulk approval.
- **Performa Backend**: Peningkatan kecepatan respons endpoint kalkulasi kuota sampling approval.

---

### 📅 15 Oktober 2026
- **Bulk Approve Admin**: Penambahan fitur kuota sampling otomatis dan opsi pemilihan akun approver.
- **Routing & Production Fix**: Penambahan fallback route aman `approvals.users_by_role` dan pembersihan cache routing.
- **Approver Lintas Plant & Manager**: Dukungan pemilihan approver lintas plant untuk role Manager dan penyediaan modal seleksi user.
- **Preload Approver**: Preload approver aktif dan sanitasi URL agar tidak salah default ke user Admin di production.
- **Approval Sortir Checksheet**: Pemetaan approval untuk Kepala Regu Jakarta dan penyesuaian approval bertingkat.
- **Alat Ukur Checksheet**: Penambahan integrasi data & pilihan alat ukur (*Caliper, Thickness Gauge, Feeler Gauge, Push-Pull, dll.*) pada form In-Process dan First Piece Approval.

---

### 📅 16 Oktober 2026
- **Laporan CASS Test (Durability)**:
  - Perbaikan bug preview gambar/foto evidence yang tidak muncul saat diedit di form Edit Laporan CASS Test.
  - Perbaikan penyimpanan variabel Data 2 pada field Aktual RN di form Edit agar tersimpan konsisten seperti Data 1.
- **Master Data Item (Cascading Downwards Alat Ukur)**:
  - Penambahan kolom pilihan alat ukur per point dimensi di Master Item.
  - Implementasi fitur *Cascading Downwards* pada dropdown alat ukur: mengubah alat ukur di suatu point otomatis mengubah point-point di bawahnya.
  - Pewarisan nilai alat ukur baris terakhir secara otomatis ketika tombol tambah baris dimensi diklik.
- **Checksheet In-Process & First Piece Approval (FPA)**:
  - Pelepasan fungsi `.hide()` pada tombol manipulasi tabel: tombol **+ Cavity**, **- Cavity**, **+ Point**, dan **- Point** tetap aktif dan dapat digunakan meskipun dimensi otomatis ditarik dari Master Data.
  - Penambahan auto-populate alat ukur saat tombol **+ Point** manual ditekan jika point tersebut terdefinisi di Master Data.
- **Dynamic Fallback Data Lama (Zero N+1 Query)**:
  - Implementasi fallback otomatis pada accessor `alat_ukur_array` di Model `InProcessChecksheet` dan `FirstPieceApproval`.
  - Data checksheet lama yang kolom `alat_ukur`-nya masih `NULL` otomatis menampilkan alat ukur di halaman index secara instan begitu Master Item diperbarui, tanpa memerlukan query tambahan ke database.
- **General Settings**:
  - Penambahan antarmuka pengaturan *"Daftar Alat Ukur (In Process & FPA)"* untuk mengelola opsi dropdown alat ukur secara dinamis tanpa hardcode.
- **Standardisasi UI & Kepatuhan Arsitektur ([`docs/RULE.md`](file:///D:/laragon/www/qc-project/docs/RULE.md))**:
  - Ekstraksi logika JavaScript inline modal detail alat ukur dan direct print pada FPA index blade ke file eksternal [`public/js/checksheet/fpa.js`](file:///D:/laragon/www/qc-project/public/js/checksheet/fpa.js).
- **Dokumentasi Kode**:
  - Pembuatan dokumentasi komprehensif Master Data ([`docs/documentation_master_data.md`](file:///D:/laragon/www/qc-project/docs/documentation_master_data.md)).
  - Pembaruan dokumentasi In-Process Checksheet ([`docs/documentation_in_process_checksheet.md`](file:///D:/laragon/www/qc-project/docs/documentation_in_process_checksheet.md)).
