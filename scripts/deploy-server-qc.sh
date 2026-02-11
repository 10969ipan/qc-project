#!/bin/bash

###############################################################################
# DEPLOYMENT COMMANDS - QC Project Server
# Server Path: /var/www/htdocs/qc
# Target Branch: Production-1.0.5.36
###############################################################################

# Warna untuk output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo "╔════════════════════════════════════════════════════════════╗"
echo "║     QC Project - Server Deployment Commands               ║"
echo "║     Path: /var/www/htdocs/qc                               ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

# Masuk ke direktori project
echo -e "${BLUE}→${NC} Masuk ke direktori project..."
cd /var/www/htdocs/qc

# STEP 1: Backup (PENTING!)
echo -e "${BLUE}→${NC} STEP 1: Backup .env file..."
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
echo -e "${GREEN}✓${NC} Backup selesai"

# STEP 2: Cek status git saat ini
echo -e "${BLUE}→${NC} STEP 2: Cek status git saat ini..."
echo "Current branch:"
git branch
echo ""
echo "Current commit:"
git log --oneline -3
echo ""

# STEP 3: Stash perubahan lokal (jika ada)
echo -e "${BLUE}→${NC} STEP 3: Stash perubahan lokal (jika ada)..."
git stash
echo -e "${GREEN}✓${NC} Stash selesai"

# STEP 4: Fetch dan checkout branch yang benar
echo -e "${BLUE}→${NC} STEP 4: Fetch dan checkout branch Production-1.0.5.36..."
git fetch origin
git checkout Production-1.0.5.36
echo -e "${GREEN}✓${NC} Checkout selesai"

# STEP 5: Pull kode terbaru
echo -e "${BLUE}→${NC} STEP 5: Pull kode terbaru..."
git pull origin Production-1.0.5.36
echo ""
echo "Commit terbaru setelah pull:"
git log --oneline -3
echo -e "${GREEN}✓${NC} Pull selesai"

# STEP 6: Install/Update Composer Dependencies
echo -e "${BLUE}→${NC} STEP 6: Update composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction
echo -e "${GREEN}✓${NC} Composer selesai"

# STEP 7: Jalankan Database Migrations
echo -e "${BLUE}→${NC} STEP 7: Jalankan database migrations..."
echo "Migration status sebelum migrate:"
php artisan migrate:status
echo ""
php artisan migrate --force
echo -e "${GREEN}✓${NC} Migrations selesai"

# STEP 8: Clear ALL Caches
echo -e "${BLUE}→${NC} STEP 8: Clear semua cache..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan event:clear
echo -e "${GREEN}✓${NC} Cache cleared"

# STEP 9: Optimize untuk Production
echo -e "${BLUE}→${NC} STEP 9: Optimize untuk production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo -e "${GREEN}✓${NC} Optimization selesai"

# STEP 10: Set Permissions
echo -e "${BLUE}→${NC} STEP 10: Set file permissions..."
chmod -R 775 storage bootstrap/cache
# Uncomment jika perlu set owner (biasanya butuh sudo)
# chown -R www-data:www-data storage bootstrap/cache
echo -e "${GREEN}✓${NC} Permissions set"

# STEP 11: Restart Queue Workers
echo -e "${BLUE}→${NC} STEP 11: Restart queue workers..."
php artisan queue:restart
echo -e "${GREEN}✓${NC} Queue restarted"

# STEP 12: Verifikasi
echo ""
echo "╔════════════════════════════════════════════════════════════╗"
echo "║                    VERIFIKASI                              ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""
echo -e "${BLUE}Laravel Version:${NC}"
php artisan --version
echo ""
echo -e "${BLUE}Current Branch:${NC}"
git branch --show-current
echo ""
echo -e "${BLUE}Latest Commit:${NC}"
git log --oneline -1
echo ""
echo -e "${BLUE}Environment:${NC}"
php artisan about | grep -E "(Environment|Debug Mode)"
echo ""

# STEP 13: Test Save Functionality (Optional)
echo -e "${YELLOW}→${NC} STEP 13: Test save functionality (optional)..."
echo "Jalankan command berikut untuk test:"
echo "  php scripts/test-save-functionality.php"
echo ""

echo "╔════════════════════════════════════════════════════════════╗"
echo "║              DEPLOYMENT SELESAI! ✓                         ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""
echo -e "${GREEN}✓${NC} Silakan test fungsi save di aplikasi web!"
echo ""
