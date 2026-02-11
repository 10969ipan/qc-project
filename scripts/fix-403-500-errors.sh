#!/bin/bash

###############################################################################
# FIX: 403 Forbidden & 500 Internal Server Error
# Path: /var/www/htdocs/qc
###############################################################################

echo "╔════════════════════════════════════════════════════════════╗"
echo "║     FIX: 403 Forbidden & 500 Error - QC Project           ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

cd /var/www/htdocs/qc

# ═══════════════════════════════════════════════════════════════
# FIX 1: 403 Forbidden - Storage Symlink
# ═══════════════════════════════════════════════════════════════
echo "→ FIX 1: Creating storage symlink..."

# Remove old symlink if exists
if [ -L "public/storage" ]; then
    echo "  Removing old symlink..."
    rm -f public/storage
fi

# Create new symlink
echo "  Creating new symlink..."
php artisan storage:link

# Verify symlink
if [ -L "public/storage" ]; then
    echo "  ✓ Symlink created successfully"
    ls -la public/storage
else
    echo "  ✗ Symlink creation failed, trying manual method..."
    ln -s /var/www/htdocs/qc/storage/app/public /var/www/htdocs/qc/public/storage
fi

echo ""

# ═══════════════════════════════════════════════════════════════
# FIX 2: Set Permissions
# ═══════════════════════════════════════════════════════════════
echo "→ FIX 2: Setting permissions..."

# Set permissions for storage
chmod -R 775 storage/app/public/
echo "  ✓ Permissions set to 775"

# Set owner (adjust based on your web server user)
# Uncomment the appropriate line:

# For Apache with www-data:
chown -R www-data:www-data storage/app/public/ 2>/dev/null && echo "  ✓ Owner set to www-data" || echo "  ⚠ Could not set owner (may need sudo)"

# For Nginx:
# chown -R nginx:nginx storage/app/public/ 2>/dev/null && echo "  ✓ Owner set to nginx" || echo "  ⚠ Could not set owner"

# For custom user:
# chown -R youruser:yourgroup storage/app/public/

echo ""

# ═══════════════════════════════════════════════════════════════
# FIX 3: Clear Cache
# ═══════════════════════════════════════════════════════════════
echo "→ FIX 3: Clearing cache..."

php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "  ✓ Cache cleared"
echo ""

# Recreate cache
php artisan config:cache
php artisan route:cache

echo "  ✓ Cache recreated"
echo ""

# ═══════════════════════════════════════════════════════════════
# VERIFICATION
# ═══════════════════════════════════════════════════════════════
echo "╔════════════════════════════════════════════════════════════╗"
echo "║                    VERIFICATION                            ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

echo "→ Checking symlink:"
if [ -L "public/storage" ]; then
    echo "  ✓ Symlink exists"
    readlink public/storage
else
    echo "  ✗ Symlink NOT found"
fi
echo ""

echo "→ Checking uploaded files:"
if [ -d "storage/app/public/calibration/verifications" ]; then
    echo "  ✓ Directory exists"
    ls -la storage/app/public/calibration/verifications/ | head -5
else
    echo "  ⚠ Directory not found"
fi
echo ""

echo "→ Checking routes:"
php artisan route:list | grep -E "(qr-data|public.calibration)"
echo ""

echo "→ Checking recent errors:"
if [ -f "storage/logs/laravel.log" ]; then
    echo "  Last 10 lines of error log:"
    tail -10 storage/logs/laravel.log
else
    echo "  ⚠ Log file not found"
fi
echo ""

echo "╔════════════════════════════════════════════════════════════╗"
echo "║                  FIX COMPLETED                             ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""
echo "Next steps:"
echo "1. Test PDF access in browser"
echo "2. Test QR code generation"
echo "3. Check error logs if still failing: tail -50 storage/logs/laravel.log"
echo ""
