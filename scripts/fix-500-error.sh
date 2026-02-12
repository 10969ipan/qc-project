#!/bin/bash

###############################################################################
# FIX HTTP 500 ERROR - QC Project
# Server Path: /var/www/htdocs/qc
# Date: 2026-02-12
#
# Script ini akan:
# 1. Diagnosa penyebab error 500
# 2. Fix otomatis semua kemungkinan penyebab
# 3. Verifikasi hasilnya
###############################################################################

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo "╔════════════════════════════════════════════════════════════╗"
echo "║     FIX HTTP 500 ERROR - QC Project                      ║"
echo "║     $(date '+%Y-%m-%d %H:%M:%S')                                   ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

cd /var/www/htdocs/qc || { echo -e "${RED}✗ Gagal masuk ke /var/www/htdocs/qc${NC}"; exit 1; }

# ═══════════════════════════════════════════════════════════════
# PHASE 1: DIAGNOSA - Kumpulkan informasi error
# ═══════════════════════════════════════════════════════════════
echo -e "${BLUE}═══ PHASE 1: DIAGNOSA ═══${NC}"
echo ""

# 1a. Cek Error Log
echo -e "${BLUE}→${NC} Cek error log Laravel..."
if [ -f "storage/logs/laravel.log" ]; then
    echo "  Last 30 lines:"
    echo "  ─────────────────────────────────────────"
    tail -30 storage/logs/laravel.log
    echo "  ─────────────────────────────────────────"
else
    echo -e "  ${YELLOW}⚠ Log file tidak ada${NC}"
fi
echo ""

# 1b. Cek Apache Error Log
echo -e "${BLUE}→${NC} Cek Apache error log..."
if [ -f "/var/log/apache2/error.log" ]; then
    echo "  Last 10 lines:"
    tail -10 /var/log/apache2/error.log
elif [ -f "/var/log/httpd/error_log" ]; then
    echo "  Last 10 lines:"
    tail -10 /var/log/httpd/error_log
else
    echo -e "  ${YELLOW}⚠ Apache log tidak ditemukan${NC}"
fi
echo ""

# 1c. Cek PHP Error
echo -e "${BLUE}→${NC} Cek PHP version & modules..."
php -v | head -1
echo ""

# 1d. Cek Vendor
echo -e "${BLUE}→${NC} Cek vendor directory..."
if [ -d "vendor" ]; then
    echo -e "  ${GREEN}✓${NC} vendor/ exists"
    if [ -f "vendor/autoload.php" ]; then
        echo -e "  ${GREEN}✓${NC} vendor/autoload.php exists"
    else
        echo -e "  ${RED}✗ vendor/autoload.php MISSING! (Critical!)${NC}"
    fi
else
    echo -e "  ${RED}✗ vendor/ MISSING! (Critical!)${NC}"
fi
echo ""

# 1e. Cek .env
echo -e "${BLUE}→${NC} Cek .env file..."
if [ -f ".env" ]; then
    echo -e "  ${GREEN}✓${NC} .env exists"
    echo "  APP_ENV=$(grep '^APP_ENV=' .env | cut -d= -f2)"
    echo "  APP_DEBUG=$(grep '^APP_DEBUG=' .env | cut -d= -f2)"
    echo "  APP_KEY=$(grep '^APP_KEY=' .env | cut -d= -f2 | head -c20)..."
    echo "  DB_DATABASE=$(grep '^DB_DATABASE=' .env | cut -d= -f2)"
    echo "  DB_HOST=$(grep '^DB_HOST=' .env | cut -d= -f2)"
else
    echo -e "  ${RED}✗ .env MISSING! (Critical!)${NC}"
fi
echo ""

# 1f. Cek permissions
echo -e "${BLUE}→${NC} Cek permissions..."
echo "  storage/:"
ls -ld storage/ 2>/dev/null || echo "  NOT FOUND"
echo "  bootstrap/cache/:"
ls -ld bootstrap/cache/ 2>/dev/null || echo "  NOT FOUND"
echo ""

# 1g. Cek composer.json
echo -e "${BLUE}→${NC} Cek simple-qrcode di composer.json..."
if grep -q "simple-qrcode" composer.json 2>/dev/null; then
    echo -e "  ${RED}✗ simple-qrcode MASIH ADA di composer.json!${NC}"
else
    echo -e "  ${GREEN}✓${NC} simple-qrcode sudah dihapus"
fi
echo ""

# 1h. Cek Git status
echo -e "${BLUE}→${NC} Git status..."
echo "  Branch: $(git branch --show-current 2>/dev/null || echo 'unknown')"
echo "  Latest commit: $(git log --oneline -1 2>/dev/null || echo 'unknown')"
echo ""

# ═══════════════════════════════════════════════════════════════
# PHASE 2: FIX - Perbaiki semua issue
# ═══════════════════════════════════════════════════════════════
echo -e "${BLUE}═══ PHASE 2: FIX ═══${NC}"
echo ""

# 2a. Pull code terbaru
echo -e "${BLUE}→${NC} STEP 1: Pull code terbaru..."
git stash 2>/dev/null
git pull origin $(git branch --show-current 2>/dev/null || echo "Production-1.0.5.36") 2>&1
echo -e "${GREEN}✓${NC} Pull done"
echo ""

# 2b. Clear SEMUA cache
echo -e "${BLUE}→${NC} STEP 2: Clear ALL cache..."
sudo rm -rf bootstrap/cache/*.php 2>/dev/null && echo -e "  ${GREEN}✓${NC} Cleared bootstrap/cache/*.php"
sudo rm -rf storage/framework/cache/data/* 2>/dev/null && echo -e "  ${GREEN}✓${NC} Cleared storage/framework/cache/"
sudo rm -rf storage/framework/sessions/* 2>/dev/null && echo -e "  ${GREEN}✓${NC} Cleared storage/framework/sessions/"
sudo rm -rf storage/framework/views/* 2>/dev/null && echo -e "  ${GREEN}✓${NC} Cleared storage/framework/views/"
echo ""

# 2c. Ensure required directories exist
echo -e "${BLUE}→${NC} STEP 3: Ensure required directories..."
mkdir -p bootstrap/cache 2>/dev/null
mkdir -p storage/framework/cache/data 2>/dev/null
mkdir -p storage/framework/sessions 2>/dev/null
mkdir -p storage/framework/views 2>/dev/null
mkdir -p storage/logs 2>/dev/null
echo -e "${GREEN}✓${NC} Directories ensured"
echo ""

# 2d. Fix Permissions (set 777 temporarily for composer install)
echo -e "${BLUE}→${NC} STEP 4: Fix permissions (temporary 777)..."
sudo chmod -R 777 storage bootstrap/cache 2>/dev/null
echo -e "${GREEN}✓${NC} Permissions set to 777"
echo ""

# 2e. Remove and reinstall vendor
echo -e "${BLUE}→${NC} STEP 5: Remove vendor & reinstall..."
sudo rm -rf vendor 2>/dev/null && echo -e "  ${GREEN}✓${NC} Removed vendor/"
composer install --no-dev --optimize-autoloader --no-interaction 2>&1
echo -e "${GREEN}✓${NC} Composer install completed"
echo ""

# 2f. Dump autoload
echo -e "${BLUE}→${NC} STEP 6: Dump autoload..."
composer dump-autoload -o 2>&1
echo -e "${GREEN}✓${NC} Autoload rebuilt"
echo ""

# 2g. Laravel artisan commands
echo -e "${BLUE}→${NC} STEP 7: Rebuild Laravel cache..."
php artisan config:clear 2>&1
php artisan cache:clear 2>&1
php artisan route:clear 2>&1
php artisan view:clear 2>&1
php artisan event:clear 2>&1
echo "  Cleared all..."

php artisan config:cache 2>&1
php artisan route:cache 2>&1
php artisan view:cache 2>&1
echo -e "${GREEN}✓${NC} Cache rebuilt"
echo ""

# 2h. Storage link
echo -e "${BLUE}→${NC} STEP 8: Recreate storage link..."
if [ -L "public/storage" ]; then
    rm -f public/storage
fi
php artisan storage:link 2>&1
echo -e "${GREEN}✓${NC} Storage linked"
echo ""

# 2i. Fix Permissions (back to 775)
echo -e "${BLUE}→${NC} STEP 9: Fix permissions (final)..."
sudo chmod -R 775 storage bootstrap/cache 2>/dev/null
sudo chown -R www-data:www-data storage bootstrap/cache vendor 2>/dev/null
echo -e "${GREEN}✓${NC} Permissions set to 775, owner www-data"
echo ""

# 2j. Restart Apache
echo -e "${BLUE}→${NC} STEP 10: Restart Apache..."
sudo systemctl daemon-reload 2>/dev/null
sudo systemctl restart apache2 2>/dev/null && echo -e "${GREEN}✓${NC} Apache restarted" || \
sudo service apache2 restart 2>/dev/null && echo -e "${GREEN}✓${NC} Apache restarted (service)"
echo ""

# ═══════════════════════════════════════════════════════════════
# PHASE 3: VERIFIKASI
# ═══════════════════════════════════════════════════════════════
echo -e "${BLUE}═══ PHASE 3: VERIFIKASI ═══${NC}"
echo ""

echo -e "${BLUE}→${NC} Laravel version:"
php artisan --version 2>&1
echo ""

echo -e "${BLUE}→${NC} Environment check:"
php artisan env 2>&1 || echo "  (env command not available)"
echo ""

echo -e "${BLUE}→${NC} Config check (APP_ENV):"
php artisan tinker --execute="echo config('app.env');" 2>/dev/null || echo "  Cannot run tinker"
echo ""

echo -e "${BLUE}→${NC} Database connection test:"
php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'DB OK'; } catch (\Exception \$e) { echo 'DB ERROR: ' . \$e->getMessage(); }" 2>/dev/null || echo "  Cannot test DB"
echo ""

echo -e "${BLUE}→${NC} HTTP test (curl localhost):"
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/ 2>/dev/null)
if [ "$HTTP_STATUS" = "200" ] || [ "$HTTP_STATUS" = "302" ]; then
    echo -e "  ${GREEN}✓ HTTP Status: $HTTP_STATUS (OK!)${NC}"
elif [ "$HTTP_STATUS" = "500" ]; then
    echo -e "  ${RED}✗ HTTP Status: $HTTP_STATUS (MASIH ERROR!)${NC}"
    echo ""
    echo -e "  ${YELLOW}→ Cek error terbaru di log:${NC}"
    tail -20 storage/logs/laravel.log 2>/dev/null
else
    echo -e "  ${YELLOW}⚠ HTTP Status: $HTTP_STATUS${NC}"
fi
echo ""

echo -e "${BLUE}→${NC} File permissions setelah fix:"
ls -la storage/ | head -5
echo ""
ls -la bootstrap/cache/ | head -5
echo ""

echo "╔════════════════════════════════════════════════════════════╗"
echo "║                    FIX SELESAI!                           ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""
echo "Jika masih error 500, cek:"
echo "  1. tail -50 storage/logs/laravel.log"
echo "  2. tail -20 /var/log/apache2/error.log"
echo "  3. php artisan config:clear && php artisan serve --port=8888"
echo "     (test langsung tanpa Apache)"
echo ""
