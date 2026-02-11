# 🔧 COMPLETE PERMISSION FIX GUIDE

## ❌ Masalah

```
Permission denied: /var/www/htdocs/qc/storage/logs/laravel.log
Permission denied: /var/www/htdocs/qc/bootstrap/cache/routes-v7.php
Permission denied: /var/www/htdocs/qc/bootstrap/cache/config.php
```

**Root Cause**: File dan direktori dimiliki oleh user yang salah, web server tidak bisa write.

---

## ✅ SOLUSI LENGKAP

### Option 1: Automated Script (RECOMMENDED)

```bash
cd /var/www/htdocs/qc
bash scripts/fix-permissions-complete.sh
```

Script akan:
- ✅ Auto-detect web server user (www-data/nginx)
- ✅ Hapus cache files yang bermasalah
- ✅ Buat missing directories
- ✅ Set permissions 775
- ✅ Set owner ke web server user
- ✅ Test write permissions
- ✅ Rebuild cache

---

### Option 2: Manual Commands

```bash
cd /var/www/htdocs/qc

# 1. Hapus cache files
rm -f bootstrap/cache/*.php

# 2. Set permissions
sudo chmod -R 775 storage bootstrap/cache

# 3. Set owner (ganti www-data jika berbeda)
sudo chown -R www-data:www-data storage bootstrap/cache

# 4. Buat log file
sudo touch storage/logs/laravel.log
sudo chmod 664 storage/logs/laravel.log
sudo chown www-data:www-data storage/logs/laravel.log

# 5. Clear & rebuild cache
php artisan config:clear
php artisan route:clear
php artisan config:cache
php artisan route:cache
```

---

### Option 3: Nuclear Option (TEMPORARY ONLY!)

**⚠️ WARNING**: Ini membuat direktori writable oleh semua user. **JANGAN** gunakan di production!

```bash
cd /var/www/htdocs/qc
sudo chmod -R 777 storage bootstrap/cache
php artisan config:cache
php artisan route:cache
```

Setelah test berhasil, **SEGERA** kembalikan ke permission yang aman:
```bash
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

---

## 🔍 Cara Cek Web Server User

```bash
# Cek Apache
ps aux | grep apache2 | grep -v grep | head -1

# Cek Nginx
ps aux | grep nginx | grep -v grep | head -1

# Cek PHP-FPM
ps aux | grep php-fpm | grep -v grep | head -1
```

User yang muncul (biasanya `www-data` atau `nginx`) adalah yang harus jadi owner.

---

## 🧪 Verifikasi Fix

```bash
cd /var/www/htdocs/qc

# 1. Cek permissions
ls -la storage/logs/
ls -la bootstrap/cache/

# 2. Cek owner
stat storage/logs/laravel.log

# 3. Test write
echo "test" >> storage/logs/laravel.log && echo "✓ Writable" || echo "✗ Not writable"

# 4. Test artisan
php artisan route:list | head -5
```

---

## 📋 Checklist

Setelah fix, pastikan:
- [ ] `storage/logs/` writable
- [ ] `bootstrap/cache/` writable
- [ ] Owner adalah web server user (www-data/nginx)
- [ ] `php artisan config:cache` berhasil
- [ ] `php artisan route:cache` berhasil
- [ ] Web application bisa diakses
- [ ] QR code bisa di-generate

---

## 💡 Penjelasan

### Kenapa Permission Penting?

Laravel perlu write access ke:
1. **storage/logs/** - Untuk logging errors
2. **storage/framework/** - Untuk cache, sessions, views
3. **bootstrap/cache/** - Untuk config & route cache
4. **storage/app/public/** - Untuk uploaded files

### Permission yang Benar:

- **Directories**: `775` (rwxrwxr-x)
  - Owner: read, write, execute
  - Group: read, write, execute
  - Others: read, execute
  
- **Files**: `664` (rw-rw-r--)
  - Owner: read, write
  - Group: read, write
  - Others: read

### Owner yang Benar:

File harus dimiliki oleh **web server user**:
- Apache: `www-data` (Ubuntu/Debian) atau `apache` (CentOS/RHEL)
- Nginx: `nginx` atau `www-data`
- PHP-FPM: Biasanya `www-data`

---

## 🚨 Troubleshooting

### Masih Error Setelah Fix?

```bash
# 1. Cek SELinux (jika di CentOS/RHEL)
getenforce
# Jika "Enforcing", temporary disable:
sudo setenforce 0

# 2. Cek file individual
find storage -type f ! -perm 664 -ls
find storage -type d ! -perm 775 -ls

# 3. Fix semua file sekaligus
find storage -type f -exec chmod 664 {} \;
find storage -type d -exec chmod 775 {} \;
find bootstrap/cache -type f -exec chmod 664 {} \;
find bootstrap/cache -type d -exec chmod 775 {} \;
```

### Error "Operation not permitted"?

Anda perlu sudo:
```bash
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

---

## ✅ Expected Result

Setelah fix permission:
- ✅ Tidak ada error "Permission denied"
- ✅ `php artisan` commands berfungsi
- ✅ Web application berfungsi normal
- ✅ Logs ter-write ke `storage/logs/laravel.log`
- ✅ Cache files ter-create di `bootstrap/cache/`
- ✅ QR code generation berfungsi
