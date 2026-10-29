# Aturan Global Proyek QC (Project-Scoped Rules)

Setiap kali Anda menerima tugas untuk melakukan modifikasi kode, memperbaiki bug, merancang UI, atau membuat fitur/menu baru di dalam proyek ini, **KAMU WAJIB mematuhi aturan berikut sebelum merancang solusi atau menulis baris kode apa pun:**

1. **Baca Aturan Baku (Wajib)**: Wajib memanggil tool `view_file` untuk membaca isi dari file `docs/RULE.md`. Patuhi seluruh aturan arsitektur yang ada di dalamnya secara mutlak tanpa terkecuali. 
2. **Pedoman UI/UX (Frontend)**: Jika tugas berkaitan dengan antarmuka pengguna, wajib membaca dan menggunakan pedoman dari `docs/DESAIN_&_LAYOUT.md` serta aturan di `.agents/rules/ui-standardization.md`.
3. **Optimasi Performa (Database & Controller)**: Proyek ini menggunakan standar optimalisasi yang sangat ketat (7 Pilar Blueprint). Jika membuat relasi database, filter form, query controller, atau perulangan tabel, dilarang menggunakan cara konvensional yang memicu lambatnya server. Wajib ikuti standar pada `docs/optimization_guide.md`.

**LARANGAN KERAS (HARGA MATI):**
- **JANGAN PERNAH** menanam logika JavaScript (tag `<script>`) secara *inline* di dalam file Blade `.blade.php`. Selalu buat dan gunakan file eksternal `.js` tersendiri di direktori `public/js/...`.
- **JANGAN** membuat *query N+1* atau melakukan komputasi berat/koneksi ulang ke *Service* di dalam `@foreach` file Blade. Semua data wajib di-*precompute* pada tingkatan Controller atau *Eager Load* di Service.

---

# Modul Aturan & Skill Tambahan (Separated Rules & Skills)

Instruksi kerja lainnya telah dipisahkan secara modular ke dalam direktori masing-masing:

- **Standar Kode & Anti-AI Slop**: [.agents/rules/code-standards.md](file:///d:/laragon/www/qc-project/.agents/rules/code-standards.md) (Prinsip Ponytail, efisiensi YAGNI, kode bebas AI slop).
- **Standar Tampilan UI & Modal**: [.agents/rules/ui-standardization.md](file:///d:/laragon/www/qc-project/.agents/rules/ui-standardization.md) (Pola Kakotora, Action Bar, Modal XL, DataTables DOM).
- **Prosedur QA Tester**: [.agents/rules/qa-tester.md](file:///d:/laragon/www/qc-project/.agents/rules/qa-tester.md) (Verifikasi mandiri syntax dan larangan lepas tangan).
- **Skill Humanizer (Teks/Prosa)**: [.agents/skills/humanizer/SKILL.md](file:///d:/laragon/www/qc-project/.agents/skills/humanizer/SKILL.md) (Penulisan ulang teks, PR, commit message agar bebas pola klise AI).
