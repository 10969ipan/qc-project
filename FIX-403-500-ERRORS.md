# 🔧 FIX: 403 Forbidden & 500 Error - Sertifikat & QR Code

## ✅ Progress: Data Sudah Bisa Disimpan!

Tapi ada 2 masalah baru:

---

## ❌ Masalah 1: 403 Forbidden - Sertifikat PDF

**Error**:
```
/qc/public/storage/calibration/verifications/1770793137_Digimatic%20Caliper%20CD-6%20ASX.pdf
Failed to load resource: the server responded with a status of 403 (Forbidden)
```

### Root Cause:
File PDF tersimpan di `storage/app/public/calibration/verifications/` tapi **symlink** dari `public/storage` ke `storage/app/public` belum dibuat atau rusak.

### Solusi:

```bash
cd /var/www/htdocs/qc

# 1. Hapus symlink lama (jika ada)
rm -f public/storage

# 2. Buat symlink baru
php artisan storage:link

# 3. Verifikasi symlink
ls -la public/storage
# Harus menunjukkan: public/storage -> ../storage/app/public

# 4. Set permission
chmod -R 775 storage/app/public/
chown -R www-data:www-data storage/app/public/
```

**Alternative** (jika `php artisan storage:link` error):
```bash
# Manual symlink
ln -s /var/www/htdocs/qc/storage/app/public /var/www/htdocs/qc/public/storage
```

---

## ❌ Masalah 2: 500 Internal Server Error - QR Code

**Error**:
```
/qc/calibration/verifications/ee07539c-e74d-4363-94ed-cfc19fe0c09a/qr-data
Failed to load resource: the server responded with a status of 500 (Internal Server Error)
```

### Kemungkinan Penyebab:

1. **QR Code library error** - SimpleSoftwareIO\QrCode
2. **UUID parsing error** - `ee07539c-e74d-4363-94ed-cfc19fe0c09a` bukan integer ID
3. **Route not found** - `public.calibration.download` route tidak ada

### Solusi:

#### A. Cek Error Log
```bash
cd /var/www/htdocs/qc
tail -50 storage/logs/laravel.log | grep -A 10 "qr-data"
```

#### B. Fix Route Issue
Kemungkinan route `public.calibration.download` tidak terdefinisi.

Cek di `routes/calibration.php` atau `routes/web.php`:
```bash
grep -r "public.calibration.download" routes/
```

Jika tidak ada, tambahkan route:
```php
// Di routes/calibration.php atau routes/web.php
Route::get('/public/calibration/verification/{id}/download', [CalibrationController::class, 'publicVerificationsDownload'])
    ->name('public.calibration.download');
```

#### C. Fix UUID vs Integer ID Issue
Method `verificationsQrData` menggunakan `$id` yang bisa UUID atau integer.

Cek apakah `CalibrationVerification` model menggunakan UUID:
```bash
grep -A 5 "class CalibrationVerification" app/Models/CalibrationVerification.php
```

Jika menggunakan UUID, pastikan route mendukung:
```php
// Di routes/calibration.php
Route::get('verifications/{id}/qr-data', [CalibrationController::class, 'verificationsQrData'])
    ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
    ->name('verifications.qr-data');
```

---

## 🚀 QUICK FIX - Jalankan di Server

```bash
cd /var/www/htdocs/qc

# 1. FIX SYMLINK (untuk 403 error)
rm -f public/storage
php artisan storage:link
chmod -R 775 storage/app/public/
chown -R www-data:www-data storage/app/public/

# 2. CEK ERROR LOG (untuk 500 error)
tail -50 storage/logs/laravel.log

# 3. CLEAR CACHE
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan route:cache

# 4. TEST
# Buka browser, coba akses sertifikat dan QR code lagi
```

---

## 🔍 Diagnostic Commands

```bash
cd /var/www/htdocs/qc

# Cek symlink
ls -la public/storage

# Cek file ada
ls -la storage/app/public/calibration/verifications/

# Cek permission
ls -la storage/app/public/calibration/

# Cek error log untuk QR code
tail -100 storage/logs/laravel.log | grep -i "qr"

# Cek routes
php artisan route:list | grep -i "qr-data"
php artisan route:list | grep -i "public.calibration"
```

---

## 📝 Expected Result

Setelah fix:
- ✅ Sertifikat PDF bisa dibuka di browser
- ✅ QR Code bisa di-generate
- ✅ Tidak ada 403 Forbidden error
- ✅ Tidak ada 500 Internal Server Error

---

## 💡 Penjelasan

### Kenapa 403 Forbidden?
Laravel menyimpan file upload di `storage/app/public/`, tapi web server hanya bisa akses file di `public/`.

**Symlink** membuat shortcut dari `public/storage` → `storage/app/public/` sehingga file bisa diakses via URL.

### Kenapa 500 Error di QR Code?
Kemungkinan:
1. Route `public.calibration.download` tidak ada
2. QR Code library tidak ter-install
3. UUID tidak di-handle dengan benar

Cek error log untuk detail spesifik.
