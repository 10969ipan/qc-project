#!/bin/bash

###############################################################################
# Script Diagnostik Server - QC Project
# Tujuan: Mengumpulkan informasi untuk diagnosa masalah save di server
###############################################################################

# Warna untuk output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fungsi helper
print_header() {
    echo ""
    echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
    echo -e "${BLUE}  ${1}${NC}"
    echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
}

print_info() {
    echo -e "${GREEN}→${NC} ${1}"
}

# Banner
echo "╔════════════════════════════════════════════════════════════╗"
echo "║        QC Project - Server Diagnostic Tool                ║"
echo "╚════════════════════════════════════════════════════════════╝"

###############################################################################
# 1. Git Information
###############################################################################
print_header "1. GIT INFORMATION"

print_info "Current Branch:"
git branch --show-current

print_info "Latest 5 Commits:"
git log --oneline -5

print_info "Git Status:"
git status -s

print_info "Remote URL:"
git remote -v

###############################################################################
# 2. PHP & Laravel Information
###############################################################################
print_header "2. PHP & LARAVEL INFORMATION"

print_info "PHP Version:"
php -v | head -n 1

print_info "Laravel Version:"
php artisan --version

print_info "Environment:"
php artisan about | grep -E "(Environment|Debug Mode|URL|Timezone)"

###############################################################################
# 3. Composer Dependencies
###############################################################################
print_header "3. COMPOSER DEPENDENCIES"

print_info "Critical Packages:"
composer show -i | grep -E "(laravel/framework|barryvdh/laravel-dompdf|revolution/laravel-google-sheets)"

print_info "Composer Version:"
composer --version

###############################################################################
# 4. Database Information
###############################################################################
print_header "4. DATABASE INFORMATION"

print_info "Database Connection Test:"
php artisan db:show 2>&1 | head -n 10

print_info "Migration Status:"
php artisan migrate:status | tail -n 10

print_info "Calibration Verifications Table Schema:"
php artisan tinker --execute="
\$table = 'calibration_verifications';
\$columns = DB::select('DESCRIBE ' . \$table);
foreach (\$columns as \$col) {
    if (in_array(\$col->Field, ['nilai_alat', 'nilai_koreksi', 'nilai_ketidakpastian', 'hasil_verifikasi'])) {
        echo \$col->Field . ': ' . \$col->Type . PHP_EOL;
    }
}
" 2>/dev/null || echo "  ⚠ Tidak bisa query schema"

###############################################################################
# 5. File Permissions
###############################################################################
print_header "5. FILE PERMISSIONS"

print_info "Storage Directory:"
ls -la storage/ | head -n 5

print_info "Bootstrap Cache:"
ls -la bootstrap/cache/ | head -n 5

###############################################################################
# 6. Recent Logs
###############################################################################
print_header "6. RECENT ERROR LOGS"

if [ -f "storage/logs/laravel.log" ]; then
    print_info "Last 20 lines of Laravel log:"
    tail -n 20 storage/logs/laravel.log
else
    echo "  ⚠ Log file tidak ditemukan"
fi

###############################################################################
# 7. Configuration Check
###############################################################################
print_header "7. CONFIGURATION CHECK"

print_info "Environment Variables (sensitive data hidden):"
cat .env | grep -E "(APP_ENV|APP_DEBUG|DB_CONNECTION|DB_DATABASE|CACHE_STORE|SESSION_DRIVER)" | sed 's/=.*/=***/'

print_info "Config Cache Status:"
if [ -f "bootstrap/cache/config.php" ]; then
    echo "  ✓ Config is cached"
    echo "  Last modified: $(stat -c %y bootstrap/cache/config.php 2>/dev/null || stat -f %Sm bootstrap/cache/config.php)"
else
    echo "  ✗ Config is not cached"
fi

###############################################################################
# 8. Critical Files Check
###############################################################################
print_header "8. CRITICAL FILES CHECK"

print_info "Checking critical files for save functionality:"

files=(
    "app/Services/IncomingSubPartService.php"
    "app/Http/Controllers/CalibrationController.php"
    "database/migrations/2026_02_09_095530_force_fix_calibration_verification_columns.php"
)

for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        echo "  ✓ $file exists"
        echo "    Last modified: $(stat -c %y "$file" 2>/dev/null || stat -f %Sm "$file")"
    else
        echo "  ✗ $file NOT FOUND"
    fi
done

###############################################################################
# 9. Disk Space
###############################################################################
print_header "9. DISK SPACE"

print_info "Disk Usage:"
df -h . | tail -n 1

###############################################################################
# Summary
###############################################################################
echo ""
echo "╔════════════════════════════════════════════════════════════╗"
echo "║              DIAGNOSTIC REPORT SELESAI                     ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""
echo "Silakan share output di atas untuk analisis lebih lanjut."
echo ""
