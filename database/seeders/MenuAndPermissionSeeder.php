<?php

namespace Database\Seeders;

use App\Models\AppMenu;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Database\Seeder;

class MenuAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        AppMenu::truncate();
        RolePermission::truncate();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $menus = [
            [
                'name' => 'Dashboard',
                'icon' => 'fas fa-tachometer-alt',
                'route' => 'dashboard',
                'order' => 1,
            ],
            [
                'name' => 'Quality Control',
                'icon' => 'fas fa-clipboard-check',
                'order' => 2,
                'children' => [
                    [
                        'name' => 'PLANT JAKARTA',
                        'plant_code' => 'jakarta',
                        'order' => 1,
                        'children' => [
                            [
                                'name' => 'MASTER DATA',
                                'order' => 1,
                                'children' => [
                                    ['name' => 'Data Item', 'route' => 'admin.items.index', 'order' => 1],
                                    ['name' => 'Kategori', 'route' => 'admin.categories.index', 'order' => 2],
                                ]
                            ],
                            [
                                'name' => 'ANALYSIS',
                                'order' => 2,
                                'children' => [
                                    ['name' => 'Sub Assy Anls', 'route' => 'analysis.monthly_ng', 'order' => 1],
                                    ['name' => 'Inprocess Anls', 'route' => 'analysis.monthly_ng_in_process', 'order' => 2],
                                ]
                            ],
                            [
                                'name' => 'CHECKSHEET',
                                'order' => 3,
                                'children' => [
                                    ['name' => 'Sub Assy', 'route' => 'checksheet.sub_assy', 'order' => 1],
                                    ['name' => 'Inprocess', 'route' => 'in_process.create', 'order' => 2],
                                    ['name' => 'FPA', 'route' => 'first_piece_approval.create', 'order' => 3],
                                    ['name' => 'Sortir', 'route' => 'sortir.create', 'order' => 4],
                                    ['name' => 'Incoming Part', 'route' => 'incoming.parts.create', 'order' => 5],
                                ]
                            ],
                            [
                                'name' => 'LAPORAN',
                                'order' => 4,
                                'children' => [
                                    ['name' => 'Sub Assy', 'route' => 'admin.checksheets.index', 'order' => 1],
                                    ['name' => 'Inprocess', 'route' => 'in_process.index', 'order' => 2],
                                    ['name' => 'FPA', 'route' => 'first_piece_approval.index', 'order' => 3],
                                    ['name' => 'Sortir', 'route' => 'sortir.index', 'order' => 4],
                                    ['name' => 'Incoming Part', 'route' => 'incoming.parts.index', 'order' => 5],
                                ]
                            ],
                        ]
                    ],
                    [
                        'name' => 'PLANT KARAWANG',
                        'plant_code' => 'karawang',
                        'order' => 2,
                        'children' => [
                            [
                                'name' => 'MASTER DATA',
                                'order' => 1,
                                'children' => [
                                    ['name' => 'Data Item', 'route' => 'admin.items.index', 'order' => 1],
                                    ['name' => 'Kategori', 'route' => 'admin.categories.index', 'order' => 2],
                                ]
                            ],
                            [
                                'name' => 'ANALYSIS',
                                'order' => 2,
                                'children' => [
                                    ['name' => 'Sub Assy Anls', 'route' => 'analysis.monthly_ng', 'order' => 1],
                                    ['name' => 'Inprocess Anls', 'route' => 'analysis.monthly_ng_in_process', 'order' => 2],
                                    ['name' => 'Cross Cut Anls', 'route' => 'analysis.monthly_ng_cross_cut', 'order' => 3],
                                ]
                            ],
                            [
                                'name' => 'CHECKSHEET',
                                'order' => 3,
                                'children' => [
                                    ['name' => 'Sub Assy', 'route' => 'checksheet.sub_assy', 'order' => 1],
                                    ['name' => 'Inprocess', 'route' => 'in_process.create', 'order' => 2],
                                    ['name' => 'FPA', 'route' => 'first_piece_approval.create', 'order' => 3],
                                    ['name' => 'Cross Cut Plating', 'route' => 'cross_cut.create', 'order' => 4],
                                    ['name' => 'Cross Cut Painting', 'route' => 'cross_cut_painting.create', 'order' => 5],
                                    ['name' => 'Sortir', 'route' => 'sortir.create', 'order' => 6],
                                    ['name' => 'Plating', 'route' => 'plating.create', 'order' => 7],
                                    ['name' => 'Painting', 'route' => 'painting.create', 'order' => 8],
                                    ['name' => 'Double Tape', 'route' => 'double_tape.create', 'order' => 9],
                                    ['name' => 'Incoming Part', 'route' => 'incoming.parts.create', 'order' => 10],
                                    ['name' => 'Incoming Material', 'route' => 'incoming.materials.create', 'order' => 11],
                                    ['name' => 'Incoming Sub-Part', 'route' => 'incoming.sub_parts.create', 'order' => 12],
                                    ['name' => 'Incoming Export', 'route' => 'incoming.exports.create', 'order' => 13],
                                    ['name' => 'Incoming Chemical', 'route' => 'incoming.chemicals.create', 'order' => 14],
                                ]
                            ],
                            [
                                'name' => 'LAPORAN',
                                'order' => 4,
                                'children' => [
                                    ['name' => 'Sub Assy', 'route' => 'admin.checksheets.index', 'order' => 1],
                                    ['name' => 'Inprocess', 'route' => 'in_process.index', 'order' => 2],
                                    ['name' => 'FPA', 'route' => 'first_piece_approval.index', 'order' => 3],
                                    ['name' => 'Cross Cut Plating', 'route' => 'cross_cut.index', 'order' => 4],
                                    ['name' => 'Cross Cut Painting', 'route' => 'cross_cut_painting.index', 'order' => 5],
                                    ['name' => 'Sortir', 'route' => 'sortir.index', 'order' => 6],
                                    ['name' => 'Plating', 'route' => 'plating.index', 'order' => 7],
                                    ['name' => 'Painting', 'route' => 'painting.index', 'order' => 8],
                                    ['name' => 'Double Tape', 'route' => 'double_tape.index', 'order' => 9],
                                    ['name' => 'Incoming Part', 'route' => 'incoming.parts.index', 'order' => 10],
                                    ['name' => 'Incoming Material', 'route' => 'incoming.materials.index', 'order' => 11],
                                    ['name' => 'Incoming Sub-Part', 'route' => 'incoming.sub_parts.index', 'order' => 12],
                                    ['name' => 'Incoming Export', 'route' => 'incoming.exports.index', 'order' => 13],
                                    ['name' => 'Incoming Chemical', 'route' => 'incoming.chemicals.index', 'order' => 14],
                                ]
                            ],
                        ]
                    ],
                ]
            ],
            [
                'name' => 'Quality Assurance',
                'icon' => 'fas fa-award',
                'order' => 3,
                'children' => [
                    [
                        'name' => 'PLANT JAKARTA',
                        'plant_code' => 'jakarta',
                        'order' => 1,
                        'children' => [
                            ['name' => 'List Claim', 'route' => 'admin.customer-claim-records.index', 'order' => 1],
                        ]
                    ],
                    [
                        'name' => 'PLANT KARAWANG',
                        'plant_code' => 'karawang',
                        'order' => 2,
                        'children' => [
                            ['name' => 'List Claim', 'route' => 'admin.customer-claim-records.index', 'order' => 1],
                        ]
                    ],
                    ['name' => 'Input Ppm dan Total Claim', 'route' => 'admin.customer-claims.index', 'order' => 3],
                ]
            ],
            [
                'name' => 'Quality System',
                'icon' => 'fas fa-chart-bar',
                'order' => 4,
                'children' => [
                    [
                        'name' => 'PLANT JAKARTA',
                        'plant_code' => 'jakarta',
                        'order' => 1,
                        'children' => [
                            [
                                'name' => 'KALIBRASI',
                                'order' => 1,
                                'children' => [
                                    ['name' => 'Jadwal Kalibrasi', 'route' => 'calibration.schedule.index', 'order' => 1],
                                    ['name' => 'Hasil Verif', 'route' => 'calibration.verifications.index', 'order' => 2],
                                    ['name' => 'Daftar Alat', 'route' => 'calibration.tools.index', 'order' => 3],
                                ]
                            ],
                            ['name' => 'KAKOTORA', 'route' => 'kakotora.index', 'order' => 2],
                            [
                                'name' => 'DURABILITY',
                                'route' => 'standard-performance-tests.index',
                                'order' => 3,
                            ],
                        ]
                    ],
                    [
                        'name' => 'PLANT KARAWANG',
                        'plant_code' => 'karawang',
                        'order' => 2,
                        'children' => [
                             [
                                'name' => 'KALIBRASI',
                                'order' => 1,
                                'children' => [
                                    ['name' => 'Jadwal Kalibrasi', 'route' => 'calibration.schedule.index', 'order' => 1],
                                    ['name' => 'Hasil Verif', 'route' => 'calibration.verifications.index', 'order' => 2],
                                    ['name' => 'Daftar Alat', 'route' => 'calibration.tools.index', 'order' => 3],
                                ]
                            ],
                            ['name' => 'KAKOTORA', 'route' => 'kakotora.index', 'order' => 2],
                            [
                                'name' => 'DURABILITY',
                                'route' => 'standard-performance-tests.index',
                                'order' => 3,
                            ],
                        ]
                    ],
                ]
            ],
            [
                'name' => 'Pengaturan',
                'icon' => 'fas fa-cogs',
                'route' => 'admin.settings.index',
                'order' => 5,
            ],
        ];

        $this->seedMenus($menus);

        // Seed default permissions for all known roles
        $allMenus = AppMenu::all();
        $roles = [
            'admin', 
            'manager', 
            'asst_manager', 
            'supervisor', 
            'kashift', 
            'inspector', 
            'karu_qc', 
            'kashift_plating', 
            'supervisor_plating', 
            'manager_plating'
        ];

        foreach ($roles as $role) {
            foreach ($allMenus as $menu) {
                RolePermission::create([
                    'role' => $role,
                    'menu_id' => $menu->id,
                    'can_view' => true, 
                    'can_input' => in_array($role, ['admin', 'supervisor', 'inspector', 'kashift']),
                    'can_edit' => in_array($role, ['admin', 'supervisor']),
                    'can_delete' => $role === 'admin',
                    'can_approve' => in_array($role, ['admin', 'manager', 'asst_manager', 'supervisor', 'kashift']),
                    'can_export' => true,
                ]);
            }
        }
    }

    private function seedMenus(array $menus, $parentId = null)
    {
        foreach ($menus as $menuData) {
            $children = isset($menuData['children']) ? $menuData['children'] : null;
            unset($menuData['children']);
            
            $menuData['parent_id'] = $parentId;
            $menu = AppMenu::create($menuData);

            if ($children) {
                $this->seedMenus($children, $menu->id);
            }
        }
    }
}
