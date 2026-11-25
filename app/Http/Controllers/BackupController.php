<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    protected string $backupDir = 'backups';

    public function __construct()
    {
        // Safe directory initialization
        try {
            if (!Storage::disk('local')->exists($this->backupDir)) {
                Storage::disk('local')->makeDirectory($this->backupDir);
            }
        } catch (\Throwable $e) {
            Log::error('BackupController directory initialization error: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan daftar file backup full system.
     */
    public function index()
    {
        try {
            if (!Storage::disk('local')->exists($this->backupDir)) {
                Storage::disk('local')->makeDirectory($this->backupDir);
            }

            $files = Storage::disk('local')->files($this->backupDir);
            $backups = [];

            foreach ($files as $file) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if ($ext === 'sql' || $ext === 'json') {
                    $backups[] = [
                        'filename' => basename($file),
                        'path' => $file,
                        'size' => $this->formatBytes(Storage::disk('local')->size($file)),
                        'created_at' => date('Y-m-d H:i:s', Storage::disk('local')->lastModified($file)),
                        'type' => str_contains(basename($file), 'module_') ? 'Per-Menu' : 'Full Database',
                    ];
                }
            }

            // Sort descending by creation date
            usort($backups, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

            return response()->json([
                'status' => 'success',
                'backups' => $backups
            ]);
        } catch (\Throwable $e) {
            Log::error('BackupController index error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memuat daftar backup: ' . $e->getMessage(),
                'backups' => []
            ], 500);
        }
    }

    /**
     * Buat Full Backup Database (.sql) dengan Streaming Memory-Efficient
     */
    public function createFullBackup()
    {
        try {
            @ini_set('memory_limit', '2048M');
            @set_time_limit(0);

            $filename = 'backup_full_' . date('Y-m-d_H-i-s') . '.sql';
            $relativeFilePath = $this->backupDir . '/' . $filename;
            $fullPath = Storage::disk('local')->path($relativeFilePath);

            $this->generateDatabaseDumpToFile($fullPath);

            $size = Storage::disk('local')->size($relativeFilePath);
            $sizeInGB = $size / (1024 * 1024 * 1024);
            $isLarge = $sizeInGB >= 2.0;

            $message = 'Full Database Backup berhasil dibuat!';
            if ($isLarge) {
                $message .= ' (Perhatian: File berukuran sangat besar: ' . $this->formatBytes($size) . ')';
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'filename' => $filename,
                'size' => $this->formatBytes($size),
                'is_large' => $isLarge,
                'size_bytes' => $size
            ]);
        } catch (\Throwable $e) {
            Log::error('Full Backup Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download file backup menggunakan chunked streaming (aman untuk file > 500 MB tanpa memakan RAM).
     */
    public function downloadBackup(string $filename)
    {
        $filename = basename($filename);
        $filePath = $this->backupDir . '/' . $filename;

        if (!Storage::disk('local')->exists($filePath)) {
            return redirect()->back()->with('error', 'File backup tidak ditemukan.');
        }

        $fullPath = Storage::disk('local')->path($filePath);
        $fileSize = @filesize($fullPath);

        @set_time_limit(0);

        $headers = [
            'Content-Type'      => 'application/sql',
            'Cache-Control'     => 'no-store, no-cache, must-revalidate',
            'X-Accel-Buffering' => 'no',
        ];
        if ($fileSize !== false) {
            $headers['Content-Length'] = (string) $fileSize;
        }

        return response()->streamDownload(function () use ($fullPath) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $stream = fopen($fullPath, 'rb');
            if ($stream === false) {
                return;
            }

            while (!feof($stream)) {
                echo fread($stream, 1024 * 1024);
                flush();
            }

            fclose($stream);
        }, $filename, $headers);
    }

    /**
     * Hapus file backup.
     */
    public function deleteBackup(string $filename)
    {
        $filename = basename($filename);
        $filePath = $this->backupDir . '/' . $filename;

        if (Storage::disk('local')->exists($filePath)) {
            Storage::disk('local')->delete($filePath);
            return response()->json(['status' => 'success', 'message' => 'File backup berhasil dihapus.']);
        }

        return response()->json(['status' => 'error', 'message' => 'File tidak ditemukan.'], 404);
    }

    /**
     * Restore Full System Database dengan Pre-Restore Snapshot otomatis.
     * Memprioritaskan binary native `mysql` CLI (10-50x lebih cepat untuk file ratusan MB)
     * dan fallback ke PHP streaming dengan AUTOCOMMIT=0 + UNIQUE_CHECKS=0 + batch COMMIT.
     */
    public function restoreFullBackup(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|max:2097152', // max 2GB (2097152 KB)
        ], [
            'backup_file.max' => 'Ukuran file restore melebihi batas maksimal 2GB. Gunakan per-menu import atau MySQL CLI server untuk restore file raksasa.'
        ]);

        try {
            @ini_set('memory_limit', '2048M');
            @set_time_limit(0);
            DB::disableQueryLog();

            // 1. Buat Pre-Restore Snapshot otomatis untuk keamanan data existing
            $snapshotFilename = 'pre_restore_snapshot_' . date('Y-m-d_H-i-s') . '.sql';
            $snapshotPath = Storage::disk('local')->path($this->backupDir . '/' . $snapshotFilename);
            $this->generateDatabaseDumpToFile($snapshotPath);

            // 2. Restore file SQL (prioritas native mysql CLI, fallback ke PHP batched streaming)
            $file = $request->file('backup_file');
            $realPath = $file->getRealPath();

            if (!$this->restoreDatabaseViaCli($realPath)) {
                $this->restoreDatabaseViaPhp($realPath);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Database berhasil di-restore! Pre-restore snapshot otomatis disimpan sebagai: ' . $snapshotFilename
            ]);
        } catch (\Throwable $e) {
            try {
                DB::statement('SET AUTOCOMMIT=1;');
                DB::statement('SET UNIQUE_CHECKS=1;');
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } catch (\Throwable $ignored) {
            }
            Log::error('Restore Full Backup Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengembalikan database: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cari path binary mysql / mysqldump (Linux PATH & Windows Laragon).
     */
    protected function resolveMysqlBinary(string $binaryName): ?string
    {
        $isWindows = DIRECTORY_SEPARATOR === '\\';
        $exe = $isWindows ? ($binaryName . '.exe') : $binaryName;

        // 1. Cek PATH sistem
        $whichCmd = $isWindows ? ('where ' . escapeshellarg($binaryName) . ' 2>NUL') : ('which ' . escapeshellarg($binaryName) . ' 2>/dev/null');
        $found = @shell_exec($whichCmd);
        if (is_string($found) && trim($found) !== '') {
            $firstLine = trim(preg_split('/\r\n|\r|\n/', trim($found))[0] ?? '');
            if ($firstLine !== '' && is_file($firstLine)) {
                return $firstLine;
            }
        }

        // 2. Cek lokasi standar Linux / Laragon Windows
        $candidates = $isWindows
            ? array_merge(
                glob('D:\\laragon\\bin\\mysql\\*\\bin\\' . $exe) ?: [],
                glob('C:\\laragon\\bin\\mysql\\*\\bin\\' . $exe) ?: [],
                glob('C:\\xampp\\mysql\\bin\\' . $exe) ?: []
            )
            : ['/usr/bin/' . $exe, '/usr/local/bin/' . $exe, '/usr/local/mysql/bin/' . $exe];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Restore cepat menggunakan native `mysql` CLI dengan streaming 1MB chunk (hemat RAM, 20-50x lebih cepat).
     */
    protected function restoreDatabaseViaCli(string $sqlFilePath): bool
    {
        $mysqlBin = $this->resolveMysqlBinary('mysql');
        if (!$mysqlBin || !function_exists('proc_open')) {
            return false;
        }

        $conn = config('database.connections.' . config('database.default'));
        $host = (string) ($conn['host'] ?? '127.0.0.1');
        $port = (string) ($conn['port'] ?? '3306');
        $user = (string) ($conn['username'] ?? 'root');
        $pass = (string) ($conn['password'] ?? '');
        $db   = (string) ($conn['database'] ?? '');

        $cmd = sprintf(
            '%s --host=%s --port=%s --user=%s --default-character-set=utf8mb4 %s',
            escapeshellarg($mysqlBin),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($user),
            escapeshellarg($db)
        );

        $env = array_merge($_ENV, $_SERVER);
        $env = array_filter($env, fn($v) => is_scalar($v));
        if ($pass !== '') {
            $env['MYSQL_PWD'] = $pass;
        }

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $proc = @proc_open($cmd, $descriptors, $pipes, null, $env);
        if (!is_resource($proc)) {
            return false;
        }

        try {
            fwrite($pipes[0], "SET FOREIGN_KEY_CHECKS=0;\nSET UNIQUE_CHECKS=0;\nSET AUTOCOMMIT=0;\n");
            $fh = fopen($sqlFilePath, 'rb');
            if ($fh) {
                while (!feof($fh)) {
                    $chunk = fread($fh, 1024 * 1024);
                    if ($chunk !== false && $chunk !== '') {
                        fwrite($pipes[0], $chunk);
                    }
                }
                fclose($fh);
            }
            fwrite($pipes[0], "\nCOMMIT;\nSET AUTOCOMMIT=1;\nSET UNIQUE_CHECKS=1;\nSET FOREIGN_KEY_CHECKS=1;\n");
            fclose($pipes[0]);

            stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $stderr = trim((string) stream_get_contents($pipes[2]));
            fclose($pipes[2]);

            $exitCode = proc_close($proc);
            if ($exitCode !== 0) {
                Log::warning('Native mysql CLI restore failed (fallback ke PHP): ' . $stderr);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('Native mysql CLI restore exception (fallback ke PHP): ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fallback restore via PHP PDO yang sudah dioptimasi (AUTOCOMMIT=0, UNIQUE_CHECKS=0, batch COMMIT).
     */
    protected function restoreDatabaseViaPhp(string $sqlFilePath): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::statement('SET UNIQUE_CHECKS=0;');
        DB::statement('SET AUTOCOMMIT=0;');

        $handle = fopen($sqlFilePath, 'r');
        if ($handle) {
            $query = '';
            $execCount = 0;
            while (($line = fgets($handle)) !== false) {
                $trimmed = trim($line);
                if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
                    continue;
                }
                $query .= $line;
                if (str_ends_with($trimmed, ';')) {
                    DB::unprepared($query);
                    $query = '';
                    $execCount++;
                    if ($execCount >= 200) {
                        DB::statement('COMMIT;');
                        $execCount = 0;
                    }
                }
            }
            if (trim($query) !== '') {
                DB::unprepared($query);
            }
            fclose($handle);
        }

        DB::statement('COMMIT;');
        DB::statement('SET AUTOCOMMIT=1;');
        DB::statement('SET UNIQUE_CHECKS=1;');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Export data per-menu (Modular JSON Export dengan Memory Optimization)
     */
    public function exportModuleData(string $module)
    {
        try {
            @ini_set('memory_limit', '1024M');

            $tablesMap = [
                'sub_assy' => ['sub_assy_checksheets'],
                'in_process' => ['in_process_checksheets'],
                'cross_cut' => ['cross_cut_checksheets', 'cross_cut_painting_checksheets'],
                'painting' => ['painting_checksheets'],
                'plating' => ['plating_checksheets', 'plating_cabut_records', 'plating_cabut_splits', 'plating_pasang_records'],
                'double_tape' => ['double_tape_checksheets'],
                'first_piece' => ['first_piece_approvals'],
                'sortir' => ['sortir_checksheets'],
                'durability' => ['durability_thickness_reports', 'standard_performance_tests'],
                'incoming' => ['incoming_parts', 'incoming_materials', 'incoming_sub_parts', 'incoming_chemicals', 'incoming_exports', 'incoming_part_arrivals', 'incoming_part_arrival_logs'],
                'items' => ['items', 'categories', 'categories_new', 'item_customers'],
                'categories' => ['categories', 'categories_new'],
                'kakotora' => ['kakotoras', 'kakotora_problems'],
                'calibration' => ['calibration_tools', 'calibration_verifications', 'calibration_tool_logs', 'calibration_tool_schedules', 'verification_tools', 'verification_verifications', 'verification_tool_logs', 'verification_schedules'],
                'customer_claims' => ['customer_claims', 'customer_claim_records'],
                'settings_system' => ['general_settings', 'app_menus', 'next_processes', 'checksheet_configs', 'machine_statuses', 'monthly_reports', 'production_reports', 'plants'],
                'users' => ['users', 'user_permissions', 'role_permissions', 'activity_logs', 'notifications'],
            ];

            if ($module === 'all') {
                $tables = DB::select('SHOW TABLES');
                $dbNameKey = 'Tables_in_' . DB::getDatabaseName();
                $allTables = [];
                $excludeSystemTables = ['cache', 'cache_locks', 'migrations', 'sessions', 'failed_jobs', 'jobs', 'job_batches'];
                foreach ($tables as $tObj) {
                    $tName = $tObj->$dbNameKey ?? current((array)$tObj);
                    if (!in_array($tName, $excludeSystemTables)) {
                        $allTables[] = $tName;
                    }
                }
                $tablesMap['all'] = $allTables;
            }

            if (!isset($tablesMap[$module])) {
                return response()->json(['status' => 'error', 'message' => 'Modul tidak valid.'], 400);
            }

            $filename = 'module_' . $module . '_' . date('Y-m-d_H-i-s') . '.json';
            $relativeFilePath = $this->backupDir . '/' . $filename;
            $fullPath = Storage::disk('local')->path($relativeFilePath);

            $this->generateModuleDumpToFile($module, $fullPath, $tablesMap);

            return Storage::disk('local')->download($relativeFilePath);
        } catch (\Throwable $e) {
            Log::error('Export Module Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export data modul: ' . $e->getMessage());
        }
    }

    /**
     * Helper untuk generate module JSON backup ke disk file.
     */
    protected function generateModuleDumpToFile(string $module, string $fullPath, array $tablesMap): void
    {
        $handle = fopen($fullPath, 'w');
        if (!$handle) {
            throw new \Exception("Gagal membuat file backup modul di lokasi disk: " . $fullPath);
        }

        fwrite($handle, "{\n");
        fwrite($handle, "  \"module\": " . json_encode($module) . ",\n");
        fwrite($handle, "  \"exported_at\": " . json_encode(date('Y-m-d H:i:s')) . ",\n");
        fwrite($handle, "  \"exported_by\": " . json_encode(Auth::user()->username ?? 'admin') . ",\n");
        fwrite($handle, "  \"tables\": {\n");

        $tableList = $tablesMap[$module];
        $validTables = [];
        foreach ($tableList as $t) {
            if (DB::getSchemaBuilder()->hasTable($t)) {
                $validTables[] = $t;
            }
        }

        $totalTables = count($validTables);
        foreach ($validTables as $tIdx => $table) {
            fwrite($handle, "    " . json_encode($table) . ": [\n");
            $first = true;
            foreach (DB::table($table)->cursor() as $row) {
                if (!$first) {
                    fwrite($handle, ",\n");
                }
                fwrite($handle, "      " . json_encode((array) $row, JSON_UNESCAPED_UNICODE));
                $first = false;
            }
            fwrite($handle, "\n    ]" . ($tIdx < $totalTables - 1 ? "," : "") . "\n");
        }

        fwrite($handle, "  }\n");
        fwrite($handle, "}\n");
        fclose($handle);
    }

    /**
     * Import data per-menu (Modular JSON Import dengan transaksi aman)
     */
    public function importModuleData(Request $request)
    {
        $request->validate([
            'module_file' => 'required|file|mimes:json,txt|max:51200', // max 50MB
        ]);

        try {
            @ini_set('memory_limit', '2048M');
            @set_time_limit(0);

            $file = $request->file('module_file');
            $content = file_get_contents($file->getRealPath());
            $data = json_decode($content, true);

            if (!$data || !isset($data['module']) || !isset($data['tables'])) {
                return response()->json(['status' => 'error', 'message' => 'Format file JSON modul tidak valid.'], 422);
            }

            DB::beginTransaction();
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $importedCount = 0;
            foreach ($data['tables'] as $table => $rows) {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    foreach (array_chunk($rows, 100) as $chunk) {
                        foreach ($chunk as $row) {
                            $rowArray = (array) $row;
                            if (isset($rowArray['id'])) {
                                DB::table($table)->updateOrInsert(
                                    ['id' => $rowArray['id']],
                                    $rowArray
                                );
                            } else {
                                DB::table($table)->insert($rowArray);
                            }
                            $importedCount++;
                        }
                    }
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil memulihkan ' . $importedCount . ' record data untuk modul [' . strtoupper($data['module']) . '].'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            Log::error('Import Module Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengimpor data modul: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Utility dump database langsung ke file disk.
     * Prioritas native `mysqldump` (20-50x lebih cepat untuk DB > 100 MB), fallback ke PHP cursor streaming.
     */
    protected function generateDatabaseDumpToFile(string $fullPath): void
    {
        @ini_set('memory_limit', '2048M');
        @set_time_limit(0);
        DB::disableQueryLog();

        // 1. Coba dump super cepat via native mysqldump CLI
        $mysqldumpBin = $this->resolveMysqlBinary('mysqldump');
        if ($mysqldumpBin && function_exists('proc_open')) {
            $conn = config('database.connections.' . config('database.default'));
            $host = (string) ($conn['host'] ?? '127.0.0.1');
            $port = (string) ($conn['port'] ?? '3306');
            $user = (string) ($conn['username'] ?? 'root');
            $pass = (string) ($conn['password'] ?? '');
            $db   = (string) ($conn['database'] ?? '');

            $cmd = sprintf(
                '%s --host=%s --port=%s --user=%s --single-transaction --quick --skip-lock-tables --default-character-set=utf8mb4 --result-file=%s %s',
                escapeshellarg($mysqldumpBin),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($user),
                escapeshellarg($fullPath),
                escapeshellarg($db)
            );

            $env = array_filter(array_merge($_ENV, $_SERVER), fn($v) => is_scalar($v));
            if ($pass !== '') {
                $env['MYSQL_PWD'] = $pass;
            }

            $proc = @proc_open($cmd, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, $env);
            if (is_resource($proc)) {
                fclose($pipes[0]);
                stream_get_contents($pipes[1]);
                fclose($pipes[1]);
                $stderr = trim((string) stream_get_contents($pipes[2]));
                fclose($pipes[2]);
                $exitCode = proc_close($proc);

                if ($exitCode === 0 && is_file($fullPath) && filesize($fullPath) > 0) {
                    return;
                }
                Log::warning('Native mysqldump failed (fallback ke PHP dump): ' . $stderr);
            }
        }

        // 2. Fallback: PHP cursor streaming
        $handle = fopen($fullPath, 'w');
        if (!$handle) {
            throw new \Exception("Gagal membuat file backup di lokasi disk: " . $fullPath);
        }

        fwrite($handle, "-- Backup Database QC Project\n");
        fwrite($handle, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
        fwrite($handle, "-- Database: " . DB::getDatabaseName() . "\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

        $tables = DB::select('SHOW TABLES');
        $dbNameKey = 'Tables_in_' . DB::getDatabaseName();

        foreach ($tables as $tableObj) {
            $tableName = $tableObj->$dbNameKey ?? current((array)$tableObj);

            $createTableStmt = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $createSql = $createTableStmt[0]->{'Create Table'} ?? '';

            fwrite($handle, "-- Table structure for `{$tableName}`\n");
            fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");
            fwrite($handle, $createSql . ";\n\n");

            fwrite($handle, "-- Data dumping for `{$tableName}`\n");
            
            $pdo = DB::connection()->getPdo();
            $batch = [];
            $batchCount = 0;

            foreach (DB::table($tableName)->cursor() as $row) {
                $rowVals = [];
                foreach ((array)$row as $val) {
                    if (is_null($val)) {
                        $rowVals[] = 'NULL';
                    } else {
                        $rowVals[] = $pdo->quote($val);
                    }
                }
                $batch[] = '(' . implode(',', $rowVals) . ')';
                $batchCount++;

                if ($batchCount >= 200) {
                    fwrite($handle, "INSERT INTO `{$tableName}` VALUES " . implode(',', $batch) . ";\n");
                    $batch = [];
                    $batchCount = 0;
                }
            }

            if ($batchCount > 0) {
                fwrite($handle, "INSERT INTO `{$tableName}` VALUES " . implode(',', $batch) . ";\n");
            }

            fwrite($handle, "\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
    }

    protected function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
