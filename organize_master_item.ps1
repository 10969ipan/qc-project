# Script untuk mengorganisir file PDF ke subfolder berdasarkan customer
# Path: c:\laragon\www\qc-project\public\master item

$masterItemPath = "c:\laragon\www\qc-project\public\master item"
Set-Location $masterItemPath

# Buat folder jika belum ada
$folders = @('ahm', 'yimm', 'others')
foreach ($folder in $folders) {
    if (-not (Test-Path $folder)) {
        New-Item -ItemType Directory -Path $folder | Out-Null
        Write-Host "Created folder: $folder" -ForegroundColor Green
    }
}

# Daftar file PDF yang ada di root master item
$pdfFiles = Get-ChildItem -Filter "*.pdf" | Where-Object { -not $_.PSIsContainer }

Write-Host "`nMemindahkan file PDF ke subfolder yang sesuai..." -ForegroundColor Cyan

foreach ($file in $pdfFiles) {
    $fileName = $file.Name
    $destination = ""
    
    # Tentukan tujuan berdasarkan nama customer atau pattern file
    # File AHM: biasanya dimulai dengan angka atau mengandung info AHM
    # File YIMM: biasanya mengandung info Yamaha
    
    if ($fileName -match "^(0101|0103|080|083|098)") {
        # File-file dengan nomor ini adalah AHM
        $destination = "ahm"
    } elseif ($fileName -match "^003") {
        # File 003 adalah YIMM (Tuning Fork Mark)
        $destination = "yimm"  
    } else {
        $destination = "others"
    }
    
    $targetPath = Join-Path $destination $fileName
    
    # Cek apakah file sudah ada di folder tujuan
    if (Test-Path $targetPath) {
        Write-Host "  [SKIP] $fileName sudah ada di folder $destination" -ForegroundColor Yellow
    } else {
        Move-Item -Path $fileName -Destination $targetPath
        Write-Host "  [MOVED] $fileName -> $destination/" -ForegroundColor Green
    }
}

Write-Host "`nSelesai! Struktur folder:" -ForegroundColor Cyan
Get-ChildItem -Directory | ForEach-Object {
    $count = (Get-ChildItem $_.FullName -Filter "*.pdf" | Measure-Object).Count
    Write-Host "  $($_.Name): $count file(s)" -ForegroundColor White
}
