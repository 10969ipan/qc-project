#!/bin/bash

###############################################################################
# COMPLETE PERMISSION FIX for QC Project
# Path: /var/www/htdocs/qc
# 
# This script fixes ALL permission issues that prevent Laravel from running
###############################################################################

echo "╔════════════════════════════════════════════════════════════╗"
echo "║     COMPLETE PERMISSION FIX - QC Project                  ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

cd /var/www/htdocs/qc

# ═══════════════════════════════════════════════════════════════
# STEP 1: Detect Web Server User
# ═══════════════════════════════════════════════════════════════
echo "→ STEP 1: Detecting web server user..."

# Try to detect web server user
WEB_USER=""
if ps aux | grep -E 'apache2|httpd' | grep -v grep | head -1 | grep -q www-data; then
    WEB_USER="www-data"
    echo "  ✓ Detected Apache with user: www-data"
elif ps aux | grep -E 'nginx' | grep -v grep | head -1 | grep -q nginx; then
    WEB_USER="nginx"
    echo "  ✓ Detected Nginx with user: nginx"
elif ps aux | grep -E 'php-fpm' | grep -v grep | head -1 | grep -q www-data; then
    WEB_USER="www-data"
    echo "  ✓ Detected PHP-FPM with user: www-data"
else
    WEB_USER="www-data"
    echo "  ⚠ Could not detect web server user, defaulting to: www-data"
fi

echo ""

# ═══════════════════════════════════════════════════════════════
# STEP 2: Remove Problematic Cache Files
# ═══════════════════════════════════════════════════════════════
echo "→ STEP 2: Removing problematic cache files..."

# Remove cache files that might have wrong permissions
rm -f bootstrap/cache/routes-v7.php 2>/dev/null && echo "  ✓ Removed routes-v7.php" || echo "  - routes-v7.php not found"
rm -f bootstrap/cache/config.php 2>/dev/null && echo "  ✓ Removed config.php" || echo "  - config.php not found"
rm -f bootstrap/cache/services.php 2>/dev/null && echo "  ✓ Removed services.php" || echo "  - services.php not found"
rm -f bootstrap/cache/packages.php 2>/dev/null && echo "  ✓ Removed packages.php" || echo "  - packages.php not found"

echo ""

# ═══════════════════════════════════════════════════════════════
# STEP 3: Create Missing Directories
# ═══════════════════════════════════════════════════════════════
echo "→ STEP 3: Creating missing directories..."

mkdir -p storage/logs && echo "  ✓ Created storage/logs"
mkdir -p storage/framework/cache && echo "  ✓ Created storage/framework/cache"
mkdir -p storage/framework/sessions && echo "  ✓ Created storage/framework/sessions"
mkdir -p storage/framework/views && echo "  ✓ Created storage/framework/views"
mkdir -p storage/app/public && echo "  ✓ Created storage/app/public"
mkdir -p bootstrap/cache && echo "  ✓ Created bootstrap/cache"

echo ""

# ═══════════════════════════════════════════════════════════════
# STEP 4: Set Directory Permissions (775)
# ═══════════════════════════════════════════════════════════════
echo "→ STEP 4: Setting directory permissions to 775..."

chmod 775 storage 2>/dev/null && echo "  ✓ storage/" || echo "  ✗ Failed: storage/"
chmod -R 775 storage/logs 2>/dev/null && echo "  ✓ storage/logs/" || echo "  ✗ Failed: storage/logs/"
chmod -R 775 storage/framework 2>/dev/null && echo "  ✓ storage/framework/" || echo "  ✗ Failed: storage/framework/"
chmod -R 775 storage/app 2>/dev/null && echo "  ✓ storage/app/" || echo "  ✗ Failed: storage/app/"
chmod 775 bootstrap/cache 2>/dev/null && echo "  ✓ bootstrap/cache/" || echo "  ✗ Failed: bootstrap/cache/"

echo ""

# ═══════════════════════════════════════════════════════════════
# STEP 5: Set Owner (requires sudo)
# ═══════════════════════════════════════════════════════════════
echo "→ STEP 5: Setting owner to $WEB_USER (may require sudo)..."

if [ "$EUID" -eq 0 ]; then
    # Running as root
    chown -R $WEB_USER:$WEB_USER storage && echo "  ✓ storage/ owner set"
    chown -R $WEB_USER:$WEB_USER bootstrap/cache && echo "  ✓ bootstrap/cache/ owner set"
else
    # Not root, try with sudo
    echo "  ⚠ Not running as root, trying with sudo..."
    sudo chown -R $WEB_USER:$WEB_USER storage && echo "  ✓ storage/ owner set" || echo "  ✗ Failed to set owner (permission denied)"
    sudo chown -R $WEB_USER:$WEB_USER bootstrap/cache && echo "  ✓ bootstrap/cache/ owner set" || echo "  ✗ Failed to set owner (permission denied)"
fi

echo ""

# ═══════════════════════════════════════════════════════════════
# STEP 6: Create Empty Log File
# ═══════════════════════════════════════════════════════════════
echo "→ STEP 6: Creating/fixing log file..."

touch storage/logs/laravel.log 2>/dev/null && echo "  ✓ Created laravel.log"
chmod 664 storage/logs/laravel.log 2>/dev/null && echo "  ✓ Set log file permissions"

if [ "$EUID" -eq 0 ]; then
    chown $WEB_USER:$WEB_USER storage/logs/laravel.log && echo "  ✓ Set log file owner"
else
    sudo chown $WEB_USER:$WEB_USER storage/logs/laravel.log 2>/dev/null && echo "  ✓ Set log file owner" || echo "  ⚠ Could not set log file owner"
fi

echo ""

# ═══════════════════════════════════════════════════════════════
# STEP 7: Test Write Permissions
# ═══════════════════════════════════════════════════════════════
echo "→ STEP 7: Testing write permissions..."

# Test storage/logs
if [ -w "storage/logs" ]; then
    echo "  ✓ storage/logs is writable"
else
    echo "  ✗ storage/logs is NOT writable"
fi

# Test bootstrap/cache
if [ -w "bootstrap/cache" ]; then
    echo "  ✓ bootstrap/cache is writable"
else
    echo "  ✗ bootstrap/cache is NOT writable"
fi

echo ""

# ═══════════════════════════════════════════════════════════════
# STEP 8: Clear and Rebuild Cache
# ═══════════════════════════════════════════════════════════════
echo "→ STEP 8: Rebuilding cache..."

php artisan config:clear 2>/dev/null && echo "  ✓ Config cache cleared" || echo "  ✗ Failed to clear config cache"
php artisan route:clear 2>/dev/null && echo "  ✓ Route cache cleared" || echo "  ✗ Failed to clear route cache"
php artisan view:clear 2>/dev/null && echo "  ✓ View cache cleared" || echo "  ✗ Failed to clear view cache"

php artisan config:cache 2>/dev/null && echo "  ✓ Config cached" || echo "  ✗ Failed to cache config"
php artisan route:cache 2>/dev/null && echo "  ✓ Routes cached" || echo "  ✗ Failed to cache routes"

echo ""

# ═══════════════════════════════════════════════════════════════
# VERIFICATION
# ═══════════════════════════════════════════════════════════════
echo "╔════════════════════════════════════════════════════════════╗"
echo "║                    VERIFICATION                            ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

echo "→ Directory Permissions:"
ls -la storage/ | head -10
echo ""

echo "→ Log File:"
ls -la storage/logs/laravel.log 2>/dev/null || echo "  ⚠ Log file not found"
echo ""

echo "→ Cache Directory:"
ls -la bootstrap/cache/ | head -5
echo ""

echo "╔════════════════════════════════════════════════════════════╗"
echo "║                  FIX COMPLETED                             ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""
echo "Next steps:"
echo "1. Test artisan commands: php artisan route:list"
echo "2. Test web application in browser"
echo "3. Check logs: tail -20 storage/logs/laravel.log"
echo ""
echo "If still getting permission errors:"
echo "  sudo chmod -R 777 storage bootstrap/cache  (temporary, not recommended for production)"
echo ""
