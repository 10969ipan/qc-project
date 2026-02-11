# 🔄 ROLLBACK TO WORKING VERSION (Feb 10)

## 🎯 Strategy: Rollback ke Commit Terakhir yang Working

Karena semua perubahan hari ini menyebabkan server error, kita akan **rollback** ke commit terakhir tanggal 10 Februari yang masih berfungsi normal.

---

## 📋 LANGKAH ROLLBACK DI SERVER:

### Option 1: Rollback ke Commit Spesifik (RECOMMENDED)

```bash
cd /var/www/htdocs/qc

# 1. Cek commit history
git log --oneline --before="2026-02-11" -10

# 2. Pilih commit terakhir tanggal 10 Feb (contoh: d1b61b5)
# Ganti COMMIT_HASH dengan hash yang benar dari output di atas
git checkout COMMIT_HASH

# 3. Atau buat branch baru dari commit tersebut
git checkout -b Production-1.0.5.36-stable COMMIT_HASH

# 4. Clear cache
sudo rm -rf bootstrap/cache/*
sudo chmod -R 777 storage bootstrap/cache
php artisan config:clear
php artisan route:clear
php artisan config:cache
php artisan route:cache

# 5. Fix permissions
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache

# 6. Restart Apache
sudo systemctl restart apache2
```

### Option 2: Reset Branch ke Commit Lama

```bash
cd /var/www/htdocs/qc

# 1. Cari commit terakhir yang working (sebelum 11 Feb)
git log --oneline --before="2026-02-11" -5

# 2. Hard reset ke commit tersebut (HATI-HATI: ini akan hapus semua perubahan!)
git reset --hard COMMIT_HASH

# 3. Clear cache
sudo rm -rf bootstrap/cache/*
php artisan config:clear
php artisan route:clear
php artisan config:cache

# 4. Restart Apache
sudo systemctl restart apache2
```

---

## 🔍 Cara Cari Commit yang Benar:

```bash
cd /var/www/htdocs/qc

# Lihat commit dengan tanggal
git log --oneline --date=short --format="%h %ad %s" --before="2026-02-11" -10

# Output contoh:
# d1b61b5 2026-02-10 Add deployment and diagnostic scripts
# 6b2b203 2026-02-10 Fix: Add 'sometimes' to certification validation
# abc1234 2026-02-09 Previous working commit
```

Pilih commit **SEBELUM** semua perubahan QR code (kemungkinan commit `d1b61b5` atau sebelumnya).

---

## ✅ Setelah Rollback:

Server akan kembali ke kondisi tanggal 10 Feb:
- ✅ Semua fitur berfungsi normal
- ✅ Tidak ada error QR code
- ✅ Save data berfungsi
- ⚠️ Perubahan hari ini (11 Feb) akan hilang

---

## 📝 Catatan Penting:

1. **Backup dulu** jika ada data penting di server
2. **Catat commit hash** yang dipilih untuk rollback
3. **Test** semua fitur setelah rollback
4. Nanti bisa **cherry-pick** commit yang bagus dari hari ini jika perlu

---

## 🚀 Quick Rollback (One-liner):

```bash
# Ganti COMMIT_HASH dengan hash yang benar!
cd /var/www/htdocs/qc && git reset --hard COMMIT_HASH && sudo rm -rf bootstrap/cache/* && sudo chmod -R 777 storage bootstrap/cache && php artisan config:clear && php artisan route:clear && php artisan config:cache && php artisan route:cache && sudo chmod -R 775 storage bootstrap/cache && sudo chown -R www-data:www-data storage bootstrap/cache && sudo systemctl restart apache2 && echo "✓ Rollback completed!"
```

---

**Next**: Jalankan `git log` di server untuk cari commit hash yang tepat, lalu rollback!
