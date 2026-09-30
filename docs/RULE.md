# System Rules & AI Guidelines (RULE.md)

Dokumen ini berisi sekumpulan aturan keras (strict rules) dan panduan logika bagi AI Agent maupun Developer saat memodifikasi, menambah fitur, atau membuat menu baru pada sistem aplikasi ini. Tujuannya agar arsitektur tetap bersih, tidak saling tumpang tindih, dan memprioritaskan performa.

## 1. Arsitektur & Logika Backend (Separation of Concerns)
- **Controller** (C) hanya bertugas menangani input *Request*, memanggil data dari *Service*, menyiapkan perhitungan murni (*pre-computation*), dan me-*return* ke View atau JSON.
- **Service Layer** (`app/Services/`) bertugas melakukan eksekusi *query builder* berat, logika bisnis kompleks, validasi mendalam, dan *save/update* database. JANGAN membanjiri Controller dengan query Eloquent yang panjang.
- **Model** (M) hanya untuk definisi tabel, relasi, atribut, dan mutator/accessor ringan.

## 2. Kepatuhan Pada 7 Pilar Blueprint Optimalisasi
Semua pembuatan fitur tabel data yang masif **WAJIB** merujuk pada `docs/optimization_guide.md`:
1. **Database Indexing**: Gunakan B-Tree Composite Index untuk query filter.
2. **Permission Cache**: Gunakan static `$permissionsMemoryCache` di model `User` untuk menghindari query hak akses (`hasPermission`) berulang.
3. **Direct Master Query**: Ambil data opsi *dropdown* (Filter) langsung dari tabel *Master Item* yang di-cache, JANGAN ambil dari *query* tabel transaksi utama karena memicu lambatnya server.
4. **Eager Loading**: Cegah N+1 *Problem* dengan selalu menggunakan fungsi `with(['relasi'])`.
5. **Controller Pre-computation (O(N))**: Jangan melakukan loop berat atau memanggil *helper service* di dalam `@foreach` Blade. Hitung semuannya di Controller, simpan ke atribut properti objek, lalu berikan ke Blade (Contoh: hitung *cycle time gap*).
6. **Clean Blade View**: Jangan menanam fungsi-fungsi kompleks di Blade.
7. **Ekstraksi JS Eksternal**: Skrip JS *inline* yang berlebihan akan membuat *memory leak*. Ekstrak kode JS ke dalam `/public/js/...` dan suplai *routes/variables* via *Window Object* (`window.configName`).

## 3. Aturan Pembuatan Desain & UI (Frontend)
- **LARANGAN KERAS (STRICT RULE)**: **Pastikan tidak memasukkan kode logika JavaScript ke dalam file Blade (`.blade.php`)**. Seluruh pembuatan script `<script>` yang memuat proses logika, event, atau manipulasi DOM **wajib** diletakkan di file eksternal `.js` (di dalam folder `public/js/...`). File blade hanya diperbolehkan menyimpan *inject data variables* sederhana.
- Rujuk secara spesifik ke dokumen `docs/DESAIN_&_LAYOUT.md`.
- Wajib menggunakan komponen Bootstrap 4 kustom yang sudah disediakan (Card, Table Responsive).
- Segala elemen tombol (*pill*, tabel padat, dan *badge*) harus seirama dan rapi.

## 4. Keamanan & Manipulasi Data (Data Integrity)
- Jika merancang logika "Menyembunyikan Data" (*Hide Rows*), lakukan dari level query `Where` atau CSS Class di view (jika perlu direkayasa), JANGAN PERNAH menghapus record di database hanya untuk urusan tampilan (*UI filtering*).
- Lakukan validasi ketat menggunakan `FormRequest` class (`app/Http/Requests`).

## 5. Pesan Commit (Git)
- Gunakan bahasa yang mudah dipahami, bisa santai atau semi-formal, asalkan *intent* atau tujuannya tersampaikan dengan jelas.
- Sebutkan modul yang diubah (contoh: `optimasi(in-process): ...` atau `fix(plating): ...`).

> **PENTING BAGI AI MODEL**: Saat Anda diberikan *prompt* oleh User terkait penambahan menu baru, baca secara saksama dokumen ini dan `optimization_guide.md` terlebih dahulu agar kode hasil *generate* tidak merusak standar performa sistem.
