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
        // Directory initialization
        if (!Storage::disk('local')->exists($this->backupDir)) {
            Storage::disk('local')->makeDirectory($this->backupDir);
        }
    }

    /**
     * Tampilkan daftar file backup full system.
     */
    public function index()
    {
        $files = Storage::disk('local')->files($this->backupDir);
        $backups = [];

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'sql' || pathinfo($file, PATHINFO_EXTENSION) === 'json') {
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
    }

    /**
     * Buat Full Backup Database (.sql) dengan Streaming Memory-Efficient
     */
    public function createFullBackup()
    {
        try {
            @ini_set('memory_limit', '1024M');
            @set_time_limit(300);

            $filename = 'backup_full_' . date('Y-m-d_H-i-s') . '.sql';
            $relativeFilePath = $this->backupDir . '/' . $filename;
            $fullPath = Storage::disk('local')->path($relativeFilePath);

            $this->generateDatabaseDumpToFile($fullPath);

            $size = Storage::disk('local')->size($relativeFilePath);

            return response()->json([
                'status' => 'success',
                'message' => 'Full Database Backup berhasil dibuat!',
                'filename' => $filename,
                'size' => $this->formatBytes($size)
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
     * Download file backup.
     */
    public function downloadBackup(string $filename)
    {
        $filename = basename($filename);
        $filePath = $this->backupDir . '/' . $filename;

        if (!Storage::disk('local')->exists($filePath)) {
            return redirect()->back()->with('error', 'File backup tidak ditemukan.');
        }

        return Storage::disk('local')->download($filePath);
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
     * Restore Full System Database dengan Pre-Restore Snapshot otomatis (Line-by-line Streaming).
     */
    public function restoreFullBackup(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|max:204800', // max 200MB
        ]);

        try {
            @ini_set('memory_limit', '1024M');
            @set_time_limit(300);

            // 1. Buat Pre-Restore Snapshot otomatis untuk keamanan data existing
            $snapshotFilename = 'pre_restore_snapshot_' . date('Y-m-d_H-i-s') . '.sql';
            $snapshotPath = Storage::disk('local')->path($this->backupDir . '/' . $snapshotFilename);
            $this->generateDatabaseDumpToFile($snapshotPath);

            // 2. Baca isi file dan jalankan per statement SQL untuk efisiensi RAM
            $file = $request->file('backup_file');
            $realPath = $file->getRealPath();

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $handle = fopen($realPath, 'r');
            if ($handle) {
                $query = '';
                while (($line = fgets($handle)) !== false) {
                    $trimmed = trim($line);
                    if (empty($trimmed) || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
                        continue;
                    }
                    $query .= $line;
                    if (str_ends_with($trimmed, ';')) {
                        DB::unprepared($query);
                        $query = '';
                    }
                }
                if (!empty(trim($query))) {
                    DB::unprepared($query);
                }
                fclose($handle);
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return response()->json([
                'status' => 'success',
                'message' => 'Database berhasil di-restore! Pre-restore snapshot otomatis disimpan sebagai: ' . $snapshotFilename
            ]);
        } catch (\Throwable $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            Log::error('Restore Full Backup Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengembalikan database: ' . $e->getMessage()
            ], 500);
        }
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
            @ini_set('memory_limit', '1024M');
            @set_time_limit(300);

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
     * Utility streaming dump database langsung ke file disk (RAM usage < 5MB).
     */
    protected function generateDatabaseDumpToFile(string $fullPath): void
    {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(300);

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
