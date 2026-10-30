<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DandoryItem;
use App\Models\AppMenu;
use App\Models\RolePermission;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Cache;

class DandoryQualitySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed 9 Item Awal Dandory (Sesuai Excel QC-KRW-F-0094)
        $awalItems = [
            ['order_no' => 1, 'item_check' => 'IK', 'standard' => 'Terpasang & update'],
            ['order_no' => 2, 'item_check' => 'PCCP & Flow Check', 'standard' => 'Terpasang & update'],
            ['order_no' => 3, 'item_check' => 'Packaging', 'standard' => 'Tersedia'],
            ['order_no' => 4, 'item_check' => 'Label Part', 'standard' => 'Sesuai part'],
            ['order_no' => 5, 'item_check' => 'Jig Hitung', 'standard' => 'Tersedia dan layak'],
            ['order_no' => 6, 'item_check' => 'Subpart', 'standard' => 'Tersedia'],
            ['order_no' => 7, 'item_check' => 'Alat Kerja', 'standard' => 'Tersedia dan layak'],
            ['order_no' => 8, 'item_check' => 'Limit Sample', 'standard' => 'Tersedia dan layak'],
            ['order_no' => 9, 'item_check' => 'Alat Ukur', 'standard' => 'Tersedia dan layak'],
        ];

        foreach ($awalItems as $item) {
            DandoryItem::updateOrCreate(
                ['phase' => 'awal', 'order_no' => $item['order_no']],
                [
                    'item_check' => $item['item_check'],
                    'standard'   => $item['standard'],
                    'is_active'  => true,
                ]
            );
        }

        // 2. Seed 10 Item Akhir Dandory (Sesuai Excel QC-KRW-F-0094)
        $akhirItems = [
            ['order_no' => 1,  'item_check' => 'IK', 'standard' => 'Dikembalikan ke tempatnya'],
            ['order_no' => 2,  'item_check' => 'PCCP & Flow Check', 'standard' => 'Dikembalikan ke tempatnya'],
            ['order_no' => 3,  'item_check' => 'Packaging', 'standard' => 'Dikembalikan ke tempatnya'],
            ['order_no' => 4,  'item_check' => 'Label Part', 'standard' => 'Dirobek/dibuang'],
            ['order_no' => 5,  'item_check' => 'Jig Hitung', 'standard' => 'Dikembalikan ke tempatnya'],
            ['order_no' => 6,  'item_check' => 'Limit Sample', 'standard' => 'Dikembalikan ke tempatnya'],
            ['order_no' => 7,  'item_check' => 'Alat Ukur', 'standard' => 'Dikembalikan ke tempatnya'],
            ['order_no' => 8,  'item_check' => 'Subpart', 'standard' => 'Dikembalikan ke tempatnya'],
            ['order_no' => 9,  'item_check' => 'Sisa Part', 'standard' => 'Di taruh ke penyimpanan sementara sesuai rule handling part sisa'],
            ['order_no' => 10, 'item_check' => 'Sisa part tidak standar packing', 'standard' => 'Di taruh di balance sesuai rule handling part tidak standard packing'],
        ];

        foreach ($akhirItems as $item) {
            DandoryItem::updateOrCreate(
                ['phase' => 'akhir', 'order_no' => $item['order_no']],
                [
                    'item_check' => $item['item_check'],
                    'standard'   => $item['standard'],
                    'is_active'  => true,
                ]
            );
        }

        // 3. Daftarkan Menu Dandory Quality ke AppMenu (Jakarta & Karawang)
        $roles = ['admin', 'manager', 'asst_manager', 'supervisor', 'kashift', 'karu_qc', 'inspector'];

        // Plant Jakarta (Parent 10)
        $jktParent = AppMenu::find(10);
        if ($jktParent) {
            $maxOrderJkt = AppMenu::where('parent_id', 10)->max('order') ?? 0;
            $jktMenu = AppMenu::firstOrCreate(
                [
                    'parent_id'  => 10,
                    'name'       => 'Dandory Quality',
                    'plant_code' => 'jakarta',
                ],
                [
                    'route'      => 'checksheet.dandory.index',
                    'order'      => $maxOrderJkt + 1,
                    'is_active'  => true,
                    'icon'       => 'fas fa-tasks',
                ]
            );

            foreach ($roles as $role) {
                RolePermission::firstOrCreate(
                    ['role' => $role, 'menu_id' => $jktMenu->id],
                    [
                        'can_view'   => true,
                        'can_input'  => true,
                        'can_edit'   => ($role !== 'inspector'),
                        'can_delete' => in_array($role, ['admin', 'manager', 'supervisor']),
                    ]
                );
            }
        }

        // Plant Karawang (Parent 28)
        $krwParent = AppMenu::find(28);
        if ($krwParent) {
            $maxOrderKrw = AppMenu::where('parent_id', 28)->max('order') ?? 0;
            $krwMenu = AppMenu::firstOrCreate(
                [
                    'parent_id'  => 28,
                    'name'       => 'Dandory Quality',
                    'plant_code' => 'karawang',
                ],
                [
                    'route'      => 'checksheet.dandory.index',
                    'order'      => $maxOrderKrw + 1,
                    'is_active'  => true,
                    'icon'       => 'fas fa-tasks',
                ]
            );

            foreach ($roles as $role) {
                RolePermission::firstOrCreate(
                    ['role' => $role, 'menu_id' => $krwMenu->id],
                    [
                        'can_view'   => true,
                        'can_input'  => true,
                        'can_edit'   => ($role !== 'inspector'),
                        'can_delete' => in_array($role, ['admin', 'manager', 'supervisor']),
                    ]
                );
            }
        }

        // 4. Default Daftar Meja per Plant di GeneralSetting (ponytail: reusable settings)
        $defaultTablesKrw = ['Meja 1', 'Meja 2', 'Meja 3', 'Meja 4', 'Meja 5', 'Meja 6', 'Meja 7', 'Meja 8', 'Meja 9', 'Meja 10'];
        $defaultTablesJkt = ['Meja 1', 'Meja 2', 'Meja 3', 'Meja 4', 'Meja 5', 'Meja 6', 'Meja 7', 'Meja 8'];

        GeneralSetting::updateOrCreate(
            ['key' => 'dandory_tables_karawang'],
            [
                'plant_code'  => 'karawang',
                'category'    => 'dandory',
                'value'       => json_encode($defaultTablesKrw),
                'description' => 'Daftar nama meja inspeksi untuk Dandory Quality Plant Karawang',
            ]
        );

        GeneralSetting::updateOrCreate(
            ['key' => 'dandory_tables_jakarta'],
            [
                'plant_code'  => 'jakarta',
                'category'    => 'dandory',
                'value'       => json_encode($defaultTablesJkt),
                'description' => 'Daftar nama meja inspeksi untuk Dandory Quality Plant Jakarta',
            ]
        );

        // 5. Default Doc Header untuk Dandory Quality
        GeneralSetting::updateOrCreate(
            ['key' => 'doc_header_checksheet_dandory_karawang'],
            [
                'plant_code'  => 'karawang',
                'category'    => 'doc_header',
                'value'       => json_encode([
                    'no_dokumen' => 'QC-KRW-F-0094',
                    'tgl_terbit' => '17/10/2025',
                    'revisi'     => '-',
                    'halaman'    => '1 / 1',
                ]),
                'description' => 'Document Header Checksheet Dandory Quality Karawang',
            ]
        );

        GeneralSetting::updateOrCreate(
            ['key' => 'doc_header_checksheet_dandory_jakarta'],
            [
                'plant_code'  => 'jakarta',
                'category'    => 'doc_header',
                'value'       => json_encode([
                    'no_dokumen' => 'QC-JKT-F-0094',
                    'tgl_terbit' => '17/10/2025',
                    'revisi'     => '-',
                    'halaman'    => '1 / 1',
                ]),
                'description' => 'Document Header Checksheet Dandory Quality Jakarta',
            ]
        );

        Cache::flush();
    }
}
