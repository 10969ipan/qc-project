# 🚀 Panduan Deployment & Troubleshooting Server

## 📋 Ringkasan Masalah

**Gejala**: Data verifikasi bisa disimpan di lokal, tapi gagal di server  
**Penyebab Kemungkinan**: Kode di server belum ter-update dengan fix terbaru  
**Solusi**: Deploy kode terbaru dan sinkronisasi environment

---

## 🛠️ Tools yang Tersedia

### 1. `scripts/deploy-to-server.sh` - Script Deployment Otomatis
**Fungsi**: Deploy kode terbaru ke server dengan aman (termasuk backup)

**Cara Pakai**:
```bash
# Di server, jalankan:
cd /path/to/qc-project
bash scripts/deploy-to-server.sh
```

**Yang Dilakukan Script**:
- ✅ Backup database dan .env
- ✅ Pull kode terbaru (Production-1.0.5.36)
- ✅ Update composer dependencies
- ✅ Jalankan database migrations
- ✅ Clear semua cache
- ✅ Optimize untuk production
- ✅ Verifikasi deployment

### 2. `scripts/diagnose-server.sh` - Script Diagnostik
**Fungsi**: Kumpulkan informasi environment server untuk troubleshooting

**Cara Pakai**:
```bash
# Di server, jalankan:
cd /path/to/qc-project
bash scripts/diagnose-server.sh > diagnostic-report.txt
```

**Informasi yang Dikumpulkan**:
- Git branch dan commit terbaru
- PHP dan Laravel version
- Composer dependencies
- Database connection dan migration status
- File permissions
- Recent error logs
- Configuration status

### 3. `scripts/test-save-functionality.php` - Test Save Function
**Fungsi**: Test apakah fungsi save berfungsi dengan benar

**Cara Pakai**:
```bash
# Di server atau lokal:
cd /path/to/qc-project
php scripts/test-save-functionality.php
```

**Yang Ditest**:
- Database connection
- Table schema (calibration_verifications)
- JSON encoding/decoding untuk array data
- Simulasi save operation (dry run)
- Service class availability

---

## 📝 Langkah-Langkah Manual (Jika Script Tidak Bisa Digunakan)

### Opsi A: Deployment Manual di Server

```bash
# 1. Masuk ke direktori project
cd /path/to/qc-project

# 2. Backup dulu (PENTING!)
cp .env .env.backup.$(date +%Y%m%d)

# 3. Pull kode terbaru
git fetch origin
git checkout Production-1.0.5.36
git pull origin Production-1.0.5.36

# 4. Update dependencies
composer install --no-dev --optimize-autoloader

# 5. Jalankan migrations
php artisan migrate --force

# 6. Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# 7. Optimize
php artisan config:cache
php artisan route:cache

# 8. Restart queue workers
php artisan queue:restart

# 9. Verifikasi
php artisan about
git log --oneline -3
```

### Opsi B: Diagnostik Manual

```bash
# Cek branch dan commit
git branch
git log --oneline -5

# Cek Laravel version
php artisan --version

# Cek migration status
php artisan migrate:status

# Cek database schema
php artisan tinker
>>> DB::select('DESCRIBE calibration_verifications');
>>> exit

# Cek error logs
tail -50 storage/logs/laravel.log
```

---

## 🔍 Checklist Verifikasi Setelah Deployment

- [ ] **Git**: Commit terbaru adalah 6acc5b9 atau lebih baru
- [ ] **Migrations**: Tidak ada pending migrations
- [ ] **Cache**: Semua cache sudah di-clear
- [ ] **Logs**: Tidak ada error di `storage/logs/laravel.log`
- [ ] **Test Save**: Bisa menyimpan data verifikasi baru
- [ ] **Test Read**: Data yang disimpan bisa dibaca kembali

---

## ⚠️ Troubleshooting

### Problem: "Permission denied" saat jalankan script
**Solusi**:
```bash
chmod +x scripts/deploy-to-server.sh
chmod +x scripts/diagnose-server.sh
```

### Problem: "composer: command not found"
**Solusi**:
```bash
# Gunakan path lengkap
/usr/local/bin/composer install --no-dev --optimize-autoloader
```

### Problem: Migration error "Table already exists"
**Solusi**:
```bash
# Cek migration status dulu
php artisan migrate:status

# Jika perlu, rollback dan migrate ulang
php artisan migrate:rollback --step=1
php artisan migrate
```

### Problem: Masih tidak bisa save setelah deployment
**Langkah Debug**:
1. Jalankan `php scripts/test-save-functionality.php`
2. Cek error di `storage/logs/laravel.log`
3. Cek browser console untuk error JavaScript
4. Verify database column types:
   ```sql
   DESCRIBE calibration_verifications;
   ```

---

## 📞 Informasi yang Dibutuhkan untuk Support

Jika masih ada masalah, kumpulkan informasi berikut:

1. **Output dari diagnostic script**:
   ```bash
   bash scripts/diagnose-server.sh > diagnostic-report.txt
   ```

2. **Output dari test script**:
   ```bash
   php scripts/test-save-functionality.php > test-report.txt
   ```

3. **Error logs**:
   ```bash
   tail -100 storage/logs/laravel.log > error-logs.txt
   ```

4. **Browser console errors** (screenshot atau copy-paste)

5. **Network tab dari browser DevTools** saat submit form

---

## 🔄 Rollback Plan (Jika Deployment Bermasalah)

```bash
# 1. Kembali ke commit sebelumnya
git log --oneline -10  # Lihat commit history
git checkout <commit-hash-sebelumnya>

# 2. Restore database backup (jika ada)
# Sesuaikan dengan backup method Anda

# 3. Clear cache
php artisan config:clear
php artisan cache:clear

# 4. Restart services
php artisan queue:restart
```

---

## ✅ Best Practices untuk Deployment Selanjutnya

1. **Selalu backup** sebelum deployment
2. **Test di staging** dulu jika ada environment staging
3. **Gunakan script deployment** untuk konsistensi
4. **Monitor logs** setelah deployment
5. **Dokumentasikan** setiap perubahan

---

## 📚 Referensi

- Laravel Deployment: https://laravel.com/docs/deployment
- Git Workflow: https://git-scm.com/book/en/v2/Git-Branching-Branching-Workflows
- Composer: https://getcomposer.org/doc/

---

**Dibuat**: 2026-02-11  
**Versi**: 1.0  
**Target Branch**: Production-1.0.5.36
