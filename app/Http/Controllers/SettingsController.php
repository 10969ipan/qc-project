<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Plant;
use App\Models\AppMenu;
use App\Models\RolePermission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\ActivityLogger;
use App\Models\GeneralSetting;
use App\Models\NextProcess;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $users = \App\Models\User::with('plant')->orderBy('name', 'asc')->get();
        $plants = \App\Models\Plant::all();
        $dbRoles = \App\Models\User::distinct()->whereNotNull('role')->pluck('role')->toArray();
        $defaultRoles = ['admin', 'manager', 'asst_manager', 'supervisor', 'inspector', 'kashift', 'karu_qc', 'manager_plating', 'asst_manager_plating', 'supervisor_plating', 'kashift_plating'];
        $roles = array_values(array_unique(array_merge($dbRoles, $defaultRoles)));
        sort($roles);
        
        // Fetch all top-level menus with their children (deeply nested for permission matrix)
        $menus = \App\Models\AppMenu::whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => function($q) {
                $q->where('is_active', true)
                  ->with(['children' => function($sq) {
                    $sq->where('is_active', true)->with('children');
                }]);
            }])
            ->orderBy('order')
            ->get();
        
        // Fetch permissions for the first role (or selected role)
        $selectedRole = $request->get('role', $roles[0] ?? null);
        $permissions = \App\Models\RolePermission::where('role', $selectedRole)->get()
            ->keyBy('menu_id');

        // Fetch general settings - use resilient query in case migration hasn't been run
        $query = \App\Models\GeneralSetting::query();
        if (\Illuminate\Support\Facades\Schema::hasColumn('general_settings', 'category')) {
            $query->where('category', 'security')->orWhereIn('key', ['daily_approval_gate_enabled', 'fpa_categories']);
        } else {
            $query->whereIn('key', ['daily_approval_gate_enabled', 'fpa_categories']);
        }
        $generalSettings = $query->get()->keyBy('key');

        // Fetch Next Processes with plant info
        $nextProcesses = NextProcess::with('plant')
            ->whereHas('plant', function($query) {
                $query->where('name', '!=', 'TOTAL');
            })
            ->orderBy('module')
            ->orderBy('plant_id')
            ->orderBy('order')
            ->get();

        $qcModules = [
            'incoming_materials' => 'Incoming Material',
            'sub_assy' => 'Sub Assy',
            'sortir' => 'Sortir',
            'plating' => 'Plating',
            'Painting' => 'Painting',
            'in_process' => 'In-Process',
            'first_piece_approval' => 'First Piece Approval (FPA)',
            'double_tape' => 'Double Tape',
            'cross_cut' => 'Cross-Cut',
            'cross_cut_painting' => 'Cross-Cut Painting',
            'master_standard_performance_test' => 'Master Standar Plating',
            'thickness' => 'Thickness Test',
            'corrodkote' => 'Corrodkote Test',
            'cass' => 'CASS Test',
            'salt_spray' => 'Salt Spray Test',
            'porecount' => 'Porecount Test',
        ];

        return view('settings.index', compact('users', 'plants', 'roles', 'menus', 'permissions', 'selectedRole', 'generalSettings', 'nextProcesses', 'qcModules'));
    }

    /**
     * Get permissions for a specific role (AJAX)
     */
    public function getPermissions(Request $request)
    {
        $role = $request->get('role');
        $userId = $request->get('user_id');

        if ($userId) {
            $user = User::findOrFail($userId);
            
            // Baseline from role (convert to array to avoid type mismatch warnings)
            $permissions = \App\Models\RolePermission::where('role', $user->role)->get()
                ->keyBy('menu_id')->all();
            
            // User specific overrides
            $userOverrides = \App\Models\UserPermission::where('user_id', $userId)->get()
                ->keyBy('menu_id');
            
            // Merge overrides into baseline
            foreach ($userOverrides as $menuId => $override) {
                $permissions[$menuId] = $override;
            }
        } else {
            $permissions = \App\Models\RolePermission::where('role', $role)->get()
                ->keyBy('menu_id')->all();
        }
            
        return response()->json($permissions);
    }

    /**
     * Store a new user
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'nullable|string|min:6', // Optional password
            'role' => 'required|string',
            'plant_id' => 'nullable|exists:plants,id',
            'initials' => 'nullable|string|max:10',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password ?: 'indoplat2526'),
            'role' => $request->role,
            'plant_id' => $request->plant_id,
            'initials' => $request->initials,
        ]);

        ActivityLogger::log('created', $user, "Menambahkan user baru: {$user->name} ({$user->role})");

        return response()->json([
            'status' => 'success',
            'message' => 'User berhasil ditambahkan. ' . ($request->password ? '' : 'Password default: indoplat2526'),
            'user' => $user->load('plant')
        ]);
    }

    /**
     * Update an existing user
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$id,
            'password' => 'nullable|string|min:6', // Optional password update
            'role' => 'required|string',
            'plant_id' => 'nullable|exists:plants,id',
            'initials' => 'nullable|string|max:10',
        ]);

        $oldData = $user->only(['name', 'email', 'role', 'plant_id', 'initials']);

        $data = $request->only(['name', 'email', 'role', 'plant_id', 'initials']);
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // ponytail: track only actually changed fields
        $changes = [];
        foreach ($oldData as $key => $oldVal) {
            $newVal = $user->$key;
            if ((string)$oldVal !== (string)$newVal) {
                $changes[$key] = ['old' => $oldVal, 'new' => $newVal];
            }
        }
        if ($request->filled('password')) {
            $changes['password'] = ['old' => '(Tersembunyi)', 'new' => $request->password];
        }

        ActivityLogger::log('updated', $user, "Memperbarui data user: {$user->name}", $changes ?: null);

        return response()->json([
            'status' => 'success',
            'message' => 'Data user berhasil diperbarui.' . ($request->filled('password') ? ' Password juga telah diperbarui.' : ''),
            'user' => $user->load('plant')
        ]);
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $newPassword = 'indoplat2526'; // Default reset password
        
        $user->update([
            'password' => Hash::make($newPassword)
        ]);

        $changes = [
            'password' => ['old' => '(Tersembunyi)', 'new' => $newPassword]
        ];

        ActivityLogger::log('reset_password', $user, "Mereset password user: {$user->name}", $changes);

        return response()->json([
            'status' => 'success',
            'message' => "Password user {$user->name} telah direset menjadi: {$newPassword}"
        ]);
    }

    /**
     * Delete a user
     */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        ActivityLogger::log('deleted', $user, "Menghapus user: {$user->name}");
        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User berhasil dihapus.'
        ]);
    }

    /**
     * Toggle user is_active status
     */
    public function toggleUserStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $oldStatus = $user->is_active ? 'aktif' : 'non-aktif';
        $user->is_active = $request->input('is_active');
        $user->save();

        $newStatus = $user->is_active ? 'aktif' : 'non-aktif';
        ActivityLogger::log('status_toggle', $user, "Mengubah status user {$user->name} menjadi {$newStatus}", [
            'status' => ['old' => $oldStatus, 'new' => $newStatus]
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status user berhasil diperbarui.',
            'is_active' => $user->is_active
        ]);
    }

    /**
     * Export users to CSV
     */
    public function exportUsers()
    {
        $users = User::with('plant')->get();
        $filename = "Export_User_Config_" . date('Ymd_His') . ".csv";
        
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['Name', 'Email', 'Role', 'Plant Code', 'Initials', 'Status (1=Active, 0=Inactive)'];

        $callback = function() use ($users, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($users as $user) {
                fputcsv($file, [
                    $user->name,
                    $user->email,
                    $user->role,
                    $user->plant ? $user->plant->code : '',
                    $user->initials,
                    $user->is_active ? '1' : '0'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import users from CSV (Create or Update)
     */
    public function importUsers(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), "r");
        
        // Skip header
        fgetcsv($handle);

        $importCount = 0;
        $updateCount = 0;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < 2) continue; // Basic validation

                $name = $row[0];
                $email = $row[1];
                $role = $row[2] ?? 'inspector';
                $plantCode = $row[3] ?? null;
                $initials = $row[4] ?? null;
                $isActive = isset($row[5]) ? (bool)$row[5] : true;

                $plantId = null;
                if ($plantCode) {
                    $plant = Plant::where('code', $plantCode)->first();
                    $plantId = $plant ? $plant->id : null;
                }

                $user = User::where('email', $email)->first();
                
                $userData = [
                    'name' => $name,
                    'role' => $role,
                    'plant_id' => $plantId,
                    'initials' => $initials,
                    'is_active' => $isActive,
                ];

                if ($user) {
                    $user->update($userData);
                    $updateCount++;
                } else {
                    $userData['email'] = $email;
                    $userData['password'] = Hash::make('password123'); // Default password for new users
                    User::create($userData);
                    $importCount++;
                }
            }
            DB::commit();
            fclose($handle);

            ActivityLogger::log('updated', null, "Melakukan import/update user secara massal ({$importCount} baru, {$updateCount} diperbarui)");

            return redirect()->back()->with('success', "Berhasil memproses data: $importCount user baru ditambahkan, $updateCount user diperbarui.");
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return redirect()->back()->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }
    /**
     * Get details for a specific menu (AJAX)
     */
    public function getMenuDetails($id)
    {
        $menu = AppMenu::findOrFail($id);
        return response()->json($menu);
    }

    /**
     * Update menu details (AJAX)
     */
    public function updateMenuDetails(Request $request, $id)
    {
        $menu = AppMenu::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'required|boolean',
            'is_maintenance' => 'required|boolean',
            'maintenance_message' => 'nullable|string',
            'plant_jkt' => 'boolean',
            'plant_krw' => 'boolean',
        ]);

        $oldData = $menu->only(['name', 'is_active', 'is_maintenance', 'maintenance_message', 'plant_code']);

        $plants = [];
        if ($request->plant_jkt) $plants[] = 'JKT';
        if ($request->plant_krw) $plants[] = 'KRW';

        $menu->update([
            'name' => $request->name,
            'is_active' => $request->is_active,
            'is_maintenance' => $request->is_maintenance,
            'maintenance_message' => $request->maintenance_message,
            'plant_code' => !empty($plants) ? implode(',', $plants) : null,
        ]);

        $changes = [];
        foreach ($oldData as $key => $oldVal) {
            $newVal = $menu->$key;
            if ((string)$oldVal !== (string)$newVal) {
                $changes[$key] = ['old' => $oldVal, 'new' => $newVal];
            }
        }

        ActivityLogger::log('updated', $menu, "Memperbarui detail menu: {$menu->name}", $changes ?: null);

        return response()->json([
            'status' => 'success',
            'message' => 'Detail menu berhasil diperbarui.'
        ]);
    }

    /**
     * Update menu order and structure (AJAX)
     */
    public function updateMenuOrder(Request $request)
    {
        $items = $request->input('items', []);
        
        DB::beginTransaction();
        try {
            foreach ($items as $item) {
                AppMenu::where('id', $item['id'])->update([
                    'order' => $item['order'],
                    'parent_id' => $item['parent_id'] ?: null,
                ]);
            }
            DB::commit();
            
            ActivityLogger::log('updated', null, "Memperbarui susunan menu sidebar");

            return response()->json([
                'status' => 'success',
                'message' => 'Susunan menu berhasil disimpan.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan susunan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save role permissions matrix (AJAX)
     */
    public function savePermissions(Request $request)
    {
        $role = $request->input('role');
        $userId = $request->input('user_id');
        $permissions = $request->input('permissions', []);
        
        if (!$role && !$userId) {
            return response()->json(['status' => 'error', 'message' => 'Role atau User tidak valid.'], 400);
        }

        DB::beginTransaction();
        try {
            if ($userId) {
                // Remove existing permissions for this user
                \App\Models\UserPermission::where('user_id', $userId)->delete();

                // Insert new permissions
                foreach ($permissions as $menuId => $perms) {
                    \App\Models\UserPermission::create([
                        'user_id' => $userId,
                        'menu_id' => $menuId,
                        'can_view' => filter_var($perms['view'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'can_input' => filter_var($perms['input'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'can_edit' => filter_var($perms['edit'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'can_delete' => filter_var($perms['edit'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'can_approve' => filter_var($perms['approve'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'can_approve_all' => filter_var($perms['approve_all'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'can_export' => filter_var($perms['export'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    ]);
                }
                $targetName = User::find($userId)->name ?? "User #{$userId}";
                ActivityLogger::log('updated', null, "Memperbarui matriks izin khusus untuk user: {$targetName}");
            } else {
                // Remove existing permissions for this role
                RolePermission::where('role', $role)->delete();

                // Insert new permissions
                foreach ($permissions as $menuId => $perms) {
                    RolePermission::create([
                        'role' => $role,
                        'menu_id' => $menuId,
                        'can_view' => filter_var($perms['view'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'can_input' => filter_var($perms['input'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'can_edit' => filter_var($perms['edit'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'can_delete' => filter_var($perms['edit'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'can_approve' => filter_var($perms['approve'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'can_approve_all' => filter_var($perms['approve_all'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'can_export' => filter_var($perms['export'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    ]);
                }
                ActivityLogger::log('updated', null, "Memperbarui matriks izin untuk role: {$role}");
            }
            
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Matriks izin berhasil diperbarui.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan izin: ' . $e->getMessage(), [
                'exception' => $e,
                'role' => $request->role,
                'user_id' => $request->user_id,
                'permissions_count' => count($request->permissions ?? [])
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan izin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update general settings
     */
    public function updateGeneralSettings(Request $request)
    {
        $settings = $request->input('settings', []);
        
        DB::beginTransaction();
        try {
            foreach ($settings as $key => $value) {
                if ($key === 'fpa_categories' && is_string($value)) {
                    $lines = preg_split('/\r\n|\r|\n|,/', $value);
                    $cleanLines = array_values(array_unique(array_filter(array_map('strtoupper', array_map('trim', $lines)))));
                    $value = implode("\n", $cleanLines);
                }

                $updated = GeneralSetting::where('key', $key)->update(['value' => $value]);
                if (!$updated) {
                    GeneralSetting::create([
                        'key' => $key,
                        'value' => $value
                    ]);
                }
            }
            DB::commit();
            
            try {
                \Illuminate\Support\Facades\Artisan::call('view:clear');
            } catch (\Throwable $th) {
                // Ignore if view:clear fails in certain restricted environments
            }

            ActivityLogger::log('updated', null, "Memperbarui konfigurasi sistem umum");

            return response()->json([
                'status' => 'success',
                'message' => 'Konfigurasi umum berhasil disimpan.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan konfigurasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new next process
     */
    public function storeNextProcess(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'plant_id' => 'required|exists:plants,id',
            'module' => 'required|string',
            'order' => 'nullable|integer'
        ]);

        // Check for uniqueness per plant & module
        $exists = NextProcess::where('name', strtoupper($request->name))
            ->where('plant_id', $request->plant_id)
            ->where('module', $request->module)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Proses ini sudah ada untuk plant dan modul yang dipilih.'
            ], 422);
        }

        $nextProcess = NextProcess::create([
            'name' => strtoupper($request->name),
            'plant_id' => $request->plant_id,
            'module' => $request->module,
            'order' => $request->order ?? 0,
            'is_active' => true
        ]);

        ActivityLogger::log('created', $nextProcess, "Menambahkan Next Process baru: {$nextProcess->name}");

        return response()->json([
            'status' => 'success',
            'message' => 'Next Process berhasil ditambahkan.',
            'next_process' => $nextProcess
        ]);
    }

    /**
     * Update a next process
     */
    public function updateNextProcess(Request $request, $id)
    {
        $nextProcess = NextProcess::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'plant_id' => 'required|exists:plants,id',
            'module' => 'required|string',
            'order' => 'nullable|integer',
            'is_active' => 'boolean'
        ]);

        // Check for uniqueness per plant & module excluding current ID
        $exists = NextProcess::where('name', strtoupper($request->name))
            ->where('plant_id', $request->plant_id)
            ->where('module', $request->module)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Proses ini sudah ada untuk plant dan modul yang dipilih.'
            ], 422);
        }

        $oldData = $nextProcess->only(['name', 'plant_id', 'module', 'order', 'is_active']);

        $nextProcess->update([
            'name' => strtoupper($request->name),
            'plant_id' => $request->plant_id,
            'module' => $request->module,
            'order' => $request->order ?? $nextProcess->order,
            'is_active' => $request->has('is_active') ? $request->is_active : $nextProcess->is_active
        ]);

        $changes = [];
        foreach ($oldData as $key => $oldVal) {
            $newVal = $nextProcess->$key;
            if ((string)$oldVal !== (string)$newVal) {
                $changes[$key] = ['old' => $oldVal, 'new' => $newVal];
            }
        }

        ActivityLogger::log('updated', $nextProcess, "Memperbarui Next Process: {$nextProcess->name}", $changes ?: null);

        return response()->json([
            'status' => 'success',
            'message' => 'Next Process berhasil diperbarui.',
            'next_process' => $nextProcess
        ]);
    }

    /**
     * Delete a next process
     */
    public function deleteNextProcess($id)
    {
        $nextProcess = NextProcess::findOrFail($id);
        ActivityLogger::log('deleted', $nextProcess, "Menghapus Next Process: {$nextProcess->name}");
        $nextProcess->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Next Process berhasil dihapus.'
        ]);
    }

    /**
     * Get Document Headers (AJAX)
     */
    public function getDocumentHeaders()
    {
        $headers = GeneralSetting::where('category', 'document_control')->get();
        return response()->json($headers);
    }

    /**
     * Store or Update a Document Header
     */
    public function storeDocumentHeader(Request $request)
    {
        $request->validate([
            'key' => 'required|string|max:255',
            'plant_code' => 'required|string|max:50',
            'no_dokumen' => 'required|string|max:255',
            'tgl_terbit' => 'required|string|max:255',
            'revisi' => 'required|string|max:255',
            'halaman' => 'required|string|max:255',
        ]);

        $value = json_encode([
            'no_dokumen' => $request->no_dokumen,
            'tgl_terbit' => $request->tgl_terbit,
            'revisi' => $request->revisi,
            'halaman' => $request->halaman,
        ]);

        // Key structure: {module}_{plant_code} or we just rely on key=module and plant_code=plant
        $setting = GeneralSetting::updateOrCreate(
            [
                'category' => 'document_control',
                'key' => $request->key,
                'plant_code' => $request->plant_code
            ],
            [
                'value' => $value,
                'description' => 'Document Header Configuration'
            ]
        );

        ActivityLogger::log('updated', null, "Memperbarui Header Dokumen untuk {$request->key} ({$request->plant_code})");

        return response()->json([
            'status' => 'success',
            'message' => 'Header Dokumen berhasil disimpan.',
            'data' => $setting
        ]);
    }

    /**
     * Delete a Document Header
     */
    public function deleteDocumentHeader($id)
    {
        $setting = GeneralSetting::findOrFail($id);
        
        if ($setting->category !== 'document_control') {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid setting category.'
            ], 403);
        }

        ActivityLogger::log('deleted', null, "Menghapus Header Dokumen untuk {$setting->key} ({$setting->plant_code})");
        $setting->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Header Dokumen berhasil dihapus.'
        ]);
    }

    /**
     * Get Dashboard Layout for a specific role
     */
    public function getDashboardLayouts(Request $request)
    {
        $role = $request->get('role');
        $setting = GeneralSetting::where('category', 'dashboard_layout')
            ->where('key', $role)
            ->first();

        $layout = [];
        if ($setting && is_string($setting->value)) {
            $decoded = json_decode($setting->value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $layout = $decoded;
            }
        }

        return response()->json($layout);
    }

    /**
     * Save Dashboard Layout for a specific role
     */
    public function saveDashboardLayouts(Request $request)
    {
        $role = $request->input('role');
        $layout = $request->input('layout', []);

        // jQuery AJAX sends booleans as strings "true" / "false"
        foreach ($layout as $key => $val) {
            if ($val === 'true') {
                $layout[$key] = true;
            } elseif ($val === 'false') {
                $layout[$key] = false;
            } else {
                $layout[$key] = filter_var($val, FILTER_VALIDATE_BOOLEAN);
            }
        }

        if (!$role) {
            return response()->json(['status' => 'error', 'message' => 'Role tidak valid.'], 400);
        }

        try {
            $setting = GeneralSetting::updateOrCreate(
                [
                    'category' => 'dashboard_layout',
                    'key' => $role
                ],
                [
                    'value' => json_encode($layout),
                    'description' => "Dashboard layout configuration for role: $role"
                ]
            );

            ActivityLogger::log('updated', null, "Memperbarui layout dashboard untuk role: {$role}");

            return response()->json([
                'status' => 'success',
                'message' => 'Layout dashboard berhasil disimpan.'
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan layout dashboard: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan layout dashboard: ' . $e->getMessage()
            ], 500);
        }
    }
}
