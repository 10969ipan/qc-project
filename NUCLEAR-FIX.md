# 🚨 NUCLEAR FIX - Last Resort

## ⚠️ Masalah Persisten

Error `QrCodeServiceProvider not found` masih muncul setelah rollback karena:
1. **Cached services** masih ada di `bootstrap/cache/`
2. **Vendor directory** masih punya package lama
3. **Composer.lock** belum sync dengan composer.json

---

## 💣 NUCLEAR SOLUTION

Hapus **SEMUA** dan rebuild dari awal:

```bash
cd /var/www/htdocs/qc

# JALANKAN SCRIPT NUCLEAR FIX
bash scripts/nuclear-fix.sh
```

**Script akan**:
1. ✅ Hapus semua cache (bootstrap, storage, framework)
2. ✅ **HAPUS vendor/** (semua dependencies)
3. ✅ Set permission 777 (temporary)
4. ✅ **Reinstall composer** dari composer.json
5. ✅ Rebuild autoload
6. ✅ Rebuild Laravel cache
7. ✅ Fix permissions kembali ke 775
8. ✅ Restart Apache

---

## 📋 MANUAL COMMANDS (Jika Script Tidak Ada)

```bash
cd /var/www/htdocs/qc

# 1. HAPUS SEMUA CACHE
sudo rm -rf bootstrap/cache/*
sudo rm -rf storage/framework/cache/*
sudo rm -rf storage/framework/sessions/*
sudo rm -rf storage/framework/views/*

# 2. HAPUS VENDOR (NUCLEAR!)
sudo rm -rf vendor

# 3. FIX PERMISSIONS
sudo chmod -R 777 storage bootstrap/cache

# 4. REINSTALL COMPOSER
composer install --no-dev --optimize-autoloader

# 5. REBUILD AUTOLOAD
composer dump-autoload -o

# 6. REBUILD CACHE
php artisan config:cache
php artisan route:cache

# 7. FIX PERMISSIONS
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache

# 8. RESTART APACHE
sudo systemctl daemon-reload
sudo systemctl restart apache2
```

---

## 📋 ONE-LINER (Nuclear Option)

```bash
cd /var/www/htdocs/qc && sudo rm -rf bootstrap/cache/* storage/framework/cache/* storage/framework/sessions/* storage/framework/views/* vendor && sudo chmod -R 777 storage bootstrap/cache && composer install --no-dev --optimize-autoloader && composer dump-autoload -o && php artisan config:cache && php artisan route:cache && sudo chmod -R 775 storage bootstrap/cache && sudo chown -R www-data:www-data storage bootstrap/cache && sudo systemctl daemon-reload && sudo systemctl restart apache2 && echo "✓ Nuclear fix completed!"
```

---

## ✅ Expected Result

Setelah nuclear fix:
- ✅ Vendor directory ter-rebuild dari composer.json (TANPA simple-qrcode)
- ✅ Semua cache ter-clear
- ✅ Laravel services ter-load dengan benar
- ✅ Server berjalan normal
- ✅ Tidak ada error QrCodeServiceProvider

---

## 🔍 Jika Masih Error

Cek apakah composer.json di server sudah benar:

```bash
cd /var/www/htdocs/qc
cat composer.json | grep -A 10 '"require"'
```

**Harus TIDAK ada** `"simplesoftwareio/simple-qrcode"` di output!

Jika masih ada, berarti **belum pull code terbaru**:
```bash
git pull origin Production-1.0.5.36
git log --oneline -3
# Harus ada commit: cd3fde9 Fix: Remove QR code package from composer.json
```

---

**This is the LAST RESORT fix!** Setelah ini server HARUS normal. 🚀
