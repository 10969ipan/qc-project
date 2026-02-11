# 🚀 DEPLOYMENT FINAL - File Upload Fix

## ✅ Perubahan yang Sudah Di-Push

**Commit**: `5247ea7` - "Fix: Improve file upload validation and error handling"

**Branch**: `Production-1.0.5.36`

**Files Changed**:
1. ✅ `app/Http/Controllers/CalibrationController.php` - Custom validation & directory auto-creation
2. ✅ `.htaccess` - PHP-FPM compatible upload limits & XSRF token handling
3. ✅ `UPLOAD-LIMITS-CONFIG.md` - Server configuration documentation

---

## 📋 DEPLOYMENT KE SERVER - STEP BY STEP

### STEP 1: Fix Permissions (PALING PENTING!)

```bash
# SSH ke server
ssh user@your-server

# Masuk ke direktori
cd /var/www/htdocs/qc

# Fix permissions untuk storage dan bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Set owner ke web server user
sudo chown -R www-data:www-data storage bootstrap/cache

# Atau jika user berbeda, cek dulu:
ps aux | grep -E '(apache|nginx|php-fpm)' | head -3
# Lalu sesuaikan: sudo chown -R <user>:<group> storage bootstrap/cache
```

### STEP 2: Pull Code Terbaru

```bash
cd /var/www/htdocs/qc

# Pull perubahan
git pull origin Production-1.0.5.36

# Verifikasi commit
git log --oneline -3
# Harus menunjukkan commit 5247ea7
```

### STEP 3: Clear Cache

```bash
cd /var/www/htdocs/qc

# Clear semua cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Recreate cache
php artisan config:cache
php artisan route:cache
```

### STEP 4: Set Upload Limits di PHP (Jika Pakai PHP-FPM)

```bash
# Cek apakah pakai PHP-FPM
ps aux | grep php-fpm

# Jika ada output, berarti pakai PHP-FPM
# Edit php.ini
sudo nano /etc/php/8.2/fpm/php.ini

# Cari dan ubah:
# upload_max_filesize = 10M
# post_max_size = 12M

# Save (Ctrl+X, Y, Enter)

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# Verifikasi
php -i | grep upload_max_filesize
```

### STEP 5: Test Save Functionality

1. **Buka aplikasi di browser**
2. **Masuk ke form verifikasi**
3. **Test scenario**:
   - [ ] Save TANPA upload file → Harus berhasil ✅
   - [ ] Save DENGAN upload file PDF valid → Harus berhasil ✅
   - [ ] Save dengan file >10MB → Harus error dengan pesan jelas ✅

---

## 🔧 ONE-LINER DEPLOYMENT

```bash
cd /var/www/htdocs/qc && \
sudo chmod -R 775 storage bootstrap/cache && \
sudo chown -R www-data:www-data storage bootstrap/cache && \
git pull origin Production-1.0.5.36 && \
php artisan config:clear && \
php artisan cache:clear && \
php artisan view:clear && \
php artisan route:clear && \
php artisan config:cache && \
php artisan route:cache && \
echo "✓ Deployment selesai!" && \
git log --oneline -3
```

---

## 🎯 Apa yang Sudah Diperbaiki

### 1. Custom File Upload Validation ✅
- Cek file validity SEBELUM Laravel validation
- Error message yang informatif (termasuk server limit)
- Mencegah error "certification failed to upload"

### 2. Directory Auto-Creation ✅
- Auto-create `storage/app/public/calibration/tools`
- Auto-create `storage/app/public/calibration/verifications`
- Mencegah error "directory not found"

### 3. .htaccess Improvements ✅
- PHP upload limits dengan `<IfModule>` check (aman untuk PHP-FPM)
- X-XSRF-Token header handling untuk CSRF

### 4. Documentation ✅
- `UPLOAD-LIMITS-CONFIG.md` untuk panduan konfigurasi server

---

## 🔍 Troubleshooting

### Jika Masih Error "Permission denied"

```bash
# Cek owner saat ini
ls -la storage/logs/
ls -la bootstrap/cache/

# Cek user web server
ps aux | grep -E '(apache|nginx|php-fpm)' | head -3

# Set owner sesuai user web server
sudo chown -R <user>:<group> storage bootstrap/cache
```

### Jika Masih Error "certification failed to upload"

```bash
# Cek upload limits
php -i | grep -E "(upload_max_filesize|post_max_size)"

# Jika terlalu kecil, edit php.ini atau buat .user.ini
echo "upload_max_filesize = 10M" > .user.ini
echo "post_max_size = 12M" >> .user.ini
```

### Jika Error "directory not found"

```bash
# Manual create directories
mkdir -p storage/app/public/calibration/tools
mkdir -p storage/app/public/calibration/verifications
chmod -R 775 storage/app/public/
```

---

## ✅ Expected Result

Setelah deployment:
- ✅ Save data TANPA file upload → **BERHASIL**
- ✅ Save data DENGAN file upload → **BERHASIL**
- ✅ Error message informatif jika file terlalu besar
- ✅ Tidak ada permission errors
- ✅ Tidak ada "certification failed to upload" errors

---

## 📞 Jika Masih Ada Masalah

Jalankan diagnostic dan share output:

```bash
cd /var/www/htdocs/qc

# 1. Cek permissions
ls -la storage/logs/ bootstrap/cache/

# 2. Cek PHP limits
php -i | grep -E "(upload_max_filesize|post_max_size)"

# 3. Cek error logs
tail -50 storage/logs/laravel.log

# 4. Cek commit
git log --oneline -3
```

---

**Status**: ✅ Code sudah di-push, siap untuk deployment!  
**Next Step**: Jalankan deployment commands di server  
**Expected Time**: ~5 menit
