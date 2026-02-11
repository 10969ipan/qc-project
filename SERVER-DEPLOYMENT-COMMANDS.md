# ═══════════════════════════════════════════════════════════════
# PERINTAH DEPLOYMENT LENGKAP - QC PROJECT SERVER
# Path Server: /var/www/htdocs/qc
# ═══════════════════════════════════════════════════════════════

## OPSI 1: Menggunakan Script Otomatis (RECOMMENDED)

### 1. Upload script ke server
```bash
# Dari komputer lokal, upload script
scp scripts/deploy-server-qc.sh user@your-server:/var/www/htdocs/qc/scripts/
```

### 2. SSH ke server dan jalankan
```bash
# SSH ke server
ssh user@your-server

# Masuk ke direktori
cd /var/www/htdocs/qc

# Beri permission execute
chmod +x scripts/deploy-server-qc.sh

# Jalankan script
bash scripts/deploy-server-qc.sh
```

---

## OPSI 2: Manual Step-by-Step (Copy-paste satu per satu)

### STEP 1: Masuk ke direktori project
```bash
cd /var/www/htdocs/qc
```

### STEP 2: Backup .env file (PENTING!)
```bash
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
```

### STEP 3: Cek status saat ini
```bash
# Lihat branch saat ini
git branch

# Lihat commit terbaru
git log --oneline -3

# Lihat perubahan yang belum di-commit
git status
```

### STEP 4: Stash perubahan lokal (jika ada)
```bash
git stash
```

### STEP 5: Fetch dan checkout branch yang benar
```bash
git fetch origin
git checkout Production-1.0.5.36
```

### STEP 6: Pull kode terbaru
```bash
git pull origin Production-1.0.5.36
```

### STEP 7: Verifikasi commit terbaru
```bash
# Pastikan commit 6b345fb atau lebih baru ada
git log --oneline -5
```

### STEP 8: Update Composer Dependencies
```bash
composer install --no-dev --optimize-autoloader --no-interaction
```

### STEP 9: Jalankan Database Migrations
```bash
# Cek status migration dulu
php artisan migrate:status

# Jalankan migration
php artisan migrate --force
```

### STEP 10: Clear ALL Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan event:clear
```

### STEP 11: Optimize untuk Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### STEP 12: Set File Permissions
```bash
chmod -R 775 storage bootstrap/cache

# Jika perlu set owner (mungkin butuh sudo)
# sudo chown -R www-data:www-data storage bootstrap/cache
```

### STEP 13: Restart Queue Workers
```bash
php artisan queue:restart
```

### STEP 14: Verifikasi Deployment
```bash
# Cek Laravel version
php artisan --version

# Cek environment
php artisan about | grep -E "(Environment|Debug Mode)"

# Cek branch dan commit
git branch --show-current
git log --oneline -1
```

### STEP 15: Test Save Functionality (Optional)
```bash
# Upload test script dulu dari lokal
# scp scripts/test-save-functionality.php user@server:/var/www/htdocs/qc/scripts/

# Jalankan test
php scripts/test-save-functionality.php
```

---

## OPSI 3: One-Liner (Semua perintah sekaligus)

**⚠️ PERHATIAN**: Pastikan Anda sudah backup dulu!

```bash
cd /var/www/htdocs/qc && \
cp .env .env.backup.$(date +%Y%m%d_%H%M%S) && \
git stash && \
git fetch origin && \
git checkout Production-1.0.5.36 && \
git pull origin Production-1.0.5.36 && \
composer install --no-dev --optimize-autoloader --no-interaction && \
php artisan migrate --force && \
php artisan config:clear && \
php artisan cache:clear && \
php artisan view:clear && \
php artisan route:clear && \
php artisan event:clear && \
php artisan config:cache && \
php artisan route:cache && \
php artisan view:cache && \
chmod -R 775 storage bootstrap/cache && \
php artisan queue:restart && \
echo "✓ Deployment selesai!" && \
git log --oneline -3
```

---

## Verifikasi Setelah Deployment

### 1. Cek commit terbaru
```bash
cd /var/www/htdocs/qc
git log --oneline -3
```
**Expected**: Harus ada commit `6b345fb` atau lebih baru

### 2. Cek migration status
```bash
php artisan migrate:status
```
**Expected**: Tidak ada pending migrations

### 3. Test save via browser
- Buka aplikasi di browser
- Masuk ke form verifikasi
- Isi semua field
- Submit
- **Expected**: Data tersimpan tanpa error

### 4. Cek error logs
```bash
tail -50 storage/logs/laravel.log
```
**Expected**: Tidak ada error baru setelah deployment

---

## Troubleshooting

### Jika "Permission denied"
```bash
chmod +x scripts/deploy-server-qc.sh
```

### Jika "composer: command not found"
```bash
# Gunakan path lengkap
/usr/local/bin/composer install --no-dev --optimize-autoloader
```

### Jika masih tidak bisa save setelah deployment
```bash
# 1. Cek logs
tail -100 storage/logs/laravel.log

# 2. Cek database schema
php artisan tinker
>>> DB::select('DESCRIBE calibration_verifications');
>>> exit

# 3. Test save functionality
php scripts/test-save-functionality.php
```

---

## Rollback (Jika Ada Masalah)

```bash
cd /var/www/htdocs/qc

# Kembali ke commit sebelumnya
git log --oneline -10  # Lihat history
git checkout <commit-hash-sebelumnya>

# Restore .env backup
cp .env.backup.YYYYMMDD_HHMMSS .env

# Clear cache
php artisan config:clear
php artisan cache:clear

# Restart
php artisan queue:restart
```

---

## Catatan Penting

1. ✅ **Backup sudah dibuat** di step 2
2. ✅ **Stash perubahan lokal** untuk menghindari conflict
3. ✅ **Migration dijalankan** untuk update schema
4. ✅ **Cache di-clear** untuk menghindari stale data
5. ✅ **Optimize untuk production** untuk performance

**Jika ada pertanyaan atau error, share output dari command yang error!**
