# 📋 CATATAN PENTING: Upload Limits di Server

## ⚠️ .htaccess php_value TIDAK BERFUNGSI di PHP-FPM

File `.htaccess` sudah diupdate dengan:
```apache
<IfModule mod_php.c>
    php_value upload_max_filesize 10M
    php_value post_max_size 12M
</IfModule>
```

**TAPI** ini hanya berfungsi jika server menggunakan **mod_php** (Apache module).

## ✅ Cara Set Upload Limits di Server PHP-FPM

Jika server menggunakan **PHP-FPM** (lebih umum), Anda harus edit `php.ini`:

### Di Server:

```bash
# 1. Cari lokasi php.ini
php --ini

# Output akan menunjukkan path, contoh:
# Configuration File (php.ini) Path: /etc/php/8.2/fpm
# Loaded Configuration File:         /etc/php/8.2/fpm/php.ini

# 2. Edit php.ini
sudo nano /etc/php/8.2/fpm/php.ini

# 3. Cari dan ubah nilai berikut:
upload_max_filesize = 10M
post_max_size = 12M
max_file_uploads = 20

# 4. Save (Ctrl+X, Y, Enter)

# 5. Restart PHP-FPM
sudo systemctl restart php8.2-fpm
# atau
sudo systemctl restart php-fpm

# 6. Verifikasi
php -i | grep upload_max_filesize
```

## 🔍 Cara Cek Server Menggunakan Apa

```bash
# Cek apakah menggunakan PHP-FPM
ps aux | grep php-fpm

# Jika ada output, berarti menggunakan PHP-FPM
# Jika tidak ada, kemungkinan menggunakan mod_php
```

## 📝 Alternative: .user.ini (Untuk Shared Hosting)

Jika Anda tidak punya akses sudo, buat file `.user.ini` di root project:

```bash
cd /var/www/htdocs/qc
nano .user.ini
```

Isi:
```ini
upload_max_filesize = 10M
post_max_size = 12M
max_file_uploads = 20
```

Save dan tunggu 5 menit (PHP-FPM perlu reload).

## ✅ Kesimpulan

1. **Code validation Anda sudah BENAR** ✅
2. **.htaccess sudah diperbaiki** dengan `<IfModule>` check ✅
3. **Untuk production**, set upload limits di `php.ini` atau `.user.ini` ✅
