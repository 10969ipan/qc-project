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

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $users = \App\Models\User::with('plant')->orderBy('name', 'asc')->get();
        $plants = \App\Models\Plant::all();
        $roles = \App\Models\User::distinct()->whereNotNull('role')->pluck('role')->toArray();
        
        // Fetch all top-level menus with their children (deeply nested for permission matrix)
        $menus = \App\Models\AppMenu::whereNull('parent_id')
            ->with(['children' => function($q) {
                $q->with(['children' => function($sq) {
                    $sq->with('children');
                }]);
            }])
            ->orderBy('order')
            ->get();
        
        // Fetch permissions for the first role (or selected role)
        $selectedRole = $request->get('role', $roles[0] ?? null);
        $permissions = \App\Models\RolePermission::where('role', $selectedRole)->get()
            ->keyBy('menu_id');

        // Fetch general settings
        $generalSettings = \App\Models\GeneralSetting::where('category', 'security')
            ->orWhere('key', 'daily_approval_gate_enabled')
            ->get()
            ->keyBy('key');

        return view('settings.index', compact('users', 'plants', 'roles', 'menus', 'permissions', 'selectedRole', 'generalSettings'));
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

        $data = $request->only(['name', 'email', 'role', 'plant_id', 'initials']);
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        ActivityLogger::log('updated', $user, "Memperbarui data user: {$user->name}");

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

        ActivityLogger::log('reset_password', $user, "Mereset password user: {$user->name}");

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
        $user->is_active = $request->input('is_active');
        $user->save();

        $status = $user->is_active ? 'aktif' : 'non-aktif';
        ActivityLogger::log('status_toggle', $user, "Mengubah status user {$user->name} menjadi {$status}");

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

        ActivityLogger::log('updated', $menu, "Memperbarui detail menu: {$menu->name}");

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
                GeneralSetting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }
            DB::commit();
            
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
}
