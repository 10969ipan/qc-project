#!/bin/bash

###############################################################################
# NUCLEAR FIX - Complete Server Restoration
# This script aggressively clears EVERYTHING and rebuilds from scratch
###############################################################################

echo "╔════════════════════════════════════════════════════════════╗"
echo "║     NUCLEAR FIX - Complete Server Restoration             ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

cd /var/www/htdocs/qc

# ═══════════════════════════════════════════════════════════════
# STEP 1: Remove ALL Cache Files
# ═══════════════════════════════════════════════════════════════
echo "→ STEP 1: Removing ALL cache files..."

sudo rm -rf bootstrap/cache/* && echo "  ✓ Removed bootstrap/cache/*"
sudo rm -rf storage/framework/cache/* && echo "  ✓ Removed storage/framework/cache/*"
sudo rm -rf storage/framework/sessions/* && echo "  ✓ Removed storage/framework/sessions/*"
sudo rm -rf storage/framework/views/* && echo "  ✓ Removed storage/framework/views/*"
sudo rm -rf storage/logs/* && echo "  ✓ Removed storage/logs/*"

echo ""

# ═══════════════════════════════════════════════════════════════
# STEP 2: Remove Vendor Directory (NUCLEAR!)
# ═══════════════════════════════════════════════════════════════
echo "→ STEP 2: Removing vendor directory..."

sudo rm -rf vendor && echo "  ✓ Removed vendor/"

echo ""

# ═══════════════════════════════════════════════════════════════
# STEP 3: Fix Permissions
# ═══════════════════════════════════════════════════════════════
echo "→ STEP 3: Setting permissions to 777 (temporary)..."

sudo chmod -R 777 storage bootstrap/cache && echo "  ✓ Permissions set to 777"

echo ""

# ═══════════════════════════════════════════════════════════════
# STEP 4: Reinstall Dependencies
# ═══════════════════════════════════════════════════════════════
echo "→ STEP 4: Reinstalling composer dependencies..."

composer install --no-dev --optimize-autoloader && echo "  ✓ Composer install completed"

echo ""

# ═══════════════════════════════════════════════════════════════
# STEP 5: Rebuild Autoload
# ═══════════════════════════════════════════════════════════════
echo "→ STEP 5: Rebuilding autoload..."

composer dump-autoload -o && echo "  ✓ Autoload rebuilt"

echo ""

# ═══════════════════════════════════════════════════════════════
# STEP 6: Rebuild Laravel Cache
# ═══════════════════════════════════════════════════════════════
echo "→ STEP 6: Rebuilding Laravel cache..."

php artisan config:cache && echo "  ✓ Config cached"
php artisan route:cache && echo "  ✓ Routes cached"

echo ""

# ═══════════════════════════════════════════════════════════════
# STEP 7: Fix Permissions (Back to 775)
# ═══════════════════════════════════════════════════════════════
echo "→ STEP 7: Setting proper permissions..."

sudo chmod -R 775 storage bootstrap/cache && echo "  ✓ Permissions set to 775"
sudo chown -R www-data:www-data storage bootstrap/cache && echo "  ✓ Owner set to www-data"

echo ""

# ═══════════════════════════════════════════════════════════════
# STEP 8: Restart Web Server
# ═══════════════════════════════════════════════════════════════
echo "→ STEP 8: Restarting web server..."

sudo systemctl daemon-reload && echo "  ✓ Systemd reloaded"
sudo systemctl restart apache2 && echo "  ✓ Apache restarted"

echo ""

# ═══════════════════════════════════════════════════════════════
# VERIFICATION
# ═══════════════════════════════════════════════════════════════
echo "╔════════════════════════════════════════════════════════════╗"
echo "║                    VERIFICATION                            ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

echo "→ Current commit:"
git log --oneline -1

echo ""
echo "→ Composer packages:"
composer show | grep -E "(laravel|barryvdh|setasign)" | head -5

echo ""
echo "→ Laravel version:"
php artisan --version

echo ""

echo "╔════════════════════════════════════════════════════════════╗"
echo "║                  NUCLEAR FIX COMPLETED                     ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""
echo "Server should now be fully restored!"
echo "Test the application in your browser."
echo ""
