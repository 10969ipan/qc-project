# Script untuk memindahkan file dari items_files ke master item/others/
# Path: c:\laragon\www\qc-project\migrate_items_files.ps1

$sourceFolder = "c:\laragon\www\qc-project\public\items_files"
$destFolder = "c:\laragon\www\qc-project\public\master item\others"

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  Migrasi File dari items_files" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

# Cek apakah source folder ada
if (-not (Test-Path $sourceFolder)) {
    Write-Host "[INFO] Folder items_files tidak ditemukan atau sudah kosong." -ForegroundColor Yellow
    Write-Host "       Path: $sourceFolder" -ForegroundColor Gray
    exit
}

# Buat destination folder jika belum ada
if (-not (Test-Path $destFolder)) {
    New-Item -ItemType Directory -Path $destFolder -Force | Out-Null
    Write-Host "[CREATED] Folder destination dibuat: $destFolder" -ForegroundColor Green
}

# Dapatkan semua file PDF di source folder
$files = Get-ChildItem -Path $sourceFolder -Filter "*.pdf" -File

if ($files.Count -eq 0) {
    Write-Host "[INFO] Tidak ada file PDF di folder items_files." -ForegroundColor Yellow
    Write-Host "`nSelesai! Tidak ada file untuk dipindahkan." -ForegroundColor Cyan
    exit
}

Write-Host "Ditemukan $($files.Count) file PDF untuk dipindahkan`n" -ForegroundColor White

$successCount = 0
$skipCount = 0
$errorCount = 0

foreach ($file in $files) {
    $destPath = Join-Path $destFolder $file.Name
    
    try {
        # Cek apakah file sudah ada di destination
        if (Test-Path $destPath) {
            Write-Host "  [SKIP] $($file.Name) - sudah ada di folder tujuan" -ForegroundColor Yellow
            $skipCount++
        } else {
            # Pindahkan file
            Move-Item -Path $file.FullName -Destination $destPath -Force
            Write-Host "  [MOVED] $($file.Name)" -ForegroundColor Green
            $successCount++
        }
    } catch {
        Write-Host "  [ERROR] $($file.Name) - $($_.Exception.Message)" -ForegroundColor Red
        $errorCount++
    }
}

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  Ringkasan Migrasi" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Berhasil dipindahkan: $successCount file" -ForegroundColor Green
Write-Host "  Dilewati (sudah ada): $skipCount file" -ForegroundColor Yellow
Write-Host "  Error: $errorCount file" -ForegroundColor Red
Write-Host "========================================`n" -ForegroundColor Cyan

# Cek apakah folder items_files sudah kosong
$remainingFiles = Get-ChildItem -Path $sourceFolder -File

if ($remainingFiles.Count -eq 0) {
    Write-Host "[INFO] Folder items_files sudah kosong." -ForegroundColor Green
    $response = Read-Host "Hapus folder items_files? (y/n)"
    
    if ($response -eq 'y' -or $response -eq 'Y') {
        Remove-Item -Path $sourceFolder -Recurse -Force
        Write-Host "[DELETED] Folder items_files berhasil dihapus." -ForegroundColor Green
    } else {
        Write-Host "[INFO] Folder items_files tidak dihapus." -ForegroundColor Yellow
    }
} else {
    Write-Host "[INFO] Masih ada $($remainingFiles.Count) file di folder items_files." -ForegroundColor Yellow
}

Write-Host "`nMigrasi selesai!`n" -ForegroundColor Cyan
