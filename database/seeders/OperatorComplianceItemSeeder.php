<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OperatorComplianceItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OperatorComplianceItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Data 100% sesuai dengan file: 096. Checksheet Kepatuhan Operator Quality.xlsx
     */
    public function run(): void
    {
        $items = [
            // =====================================================================
            // 1. Mematuhi Standar Kerja (MSK)
            // =====================================================================
            [
                'prinsip_dasar' => 'Mematuhi Standar Kerja (MSK)',
                'item_check'    => 'WI/IK',
                'standard'      => 'Terpasang di masing-masing meja kerja, dalam kondisi baik dan terupdate',
                'order_no'      => 1,
            ],
            [
                'prinsip_dasar' => 'Mematuhi Standar Kerja (MSK)',
                'item_check'    => 'WI/IK',
                'standard'      => 'Inspector bekerja sesuai IK',
                'order_no'      => 2,
            ],
            [
                'prinsip_dasar' => 'Mematuhi Standar Kerja (MSK)',
                'item_check'    => 'Identitas Part',
                'standard'      => 'Tersedia Kartu Produksi sesuai dengan Part yang dikerjakan',
                'order_no'      => 3,
            ],
            [
                'prinsip_dasar' => 'Mematuhi Standar Kerja (MSK)',
                'item_check'    => 'Identitas Part',
                'standard'      => 'Tersedia Label sesuai dengan Part yang dikerjakan',
                'order_no'      => 4,
            ],
            [
                'prinsip_dasar' => 'Mematuhi Standar Kerja (MSK)',
                'item_check'    => 'Checksheet Part (Hasil Pengechekan)',
                'standard'      => 'Ada dan terisi dengan benar dan update setiap dandory (ganti model/part)',
                'order_no'      => 5,
            ],
            [
                'prinsip_dasar' => 'Mematuhi Standar Kerja (MSK)',
                'item_check'    => 'Checksheet Dandory (Pergantian Part)',
                'standard'      => 'Tersedia dan terisi saat awal dan akhir pergantian model',
                'order_no'      => 6,
            ],
            [
                'prinsip_dasar' => 'Mematuhi Standar Kerja (MSK)',
                'item_check'    => 'Alat Ukur',
                'standard'      => 'Tersedia dan sesuai dengan yang tercantum di IK',
                'order_no'      => 7,
            ],
            [
                'prinsip_dasar' => 'Mematuhi Standar Kerja (MSK)',
                'item_check'    => 'Alat Ukur',
                'standard'      => 'Alat Ukur dalam kondisi layak pakai dan terdapat identitas',
                'order_no'      => 8,
            ],
            [
                'prinsip_dasar' => 'Mematuhi Standar Kerja (MSK)',
                'item_check'    => 'Alat Ukur',
                'standard'      => 'Pengukuran dilakukan sesuai frekuensi dalam IK/ Checksheet',
                'order_no'      => 9,
            ],
            [
                'prinsip_dasar' => 'Mematuhi Standar Kerja (MSK)',
                'item_check'    => 'Alat Bantu Kerja',
                'standard'      => 'Tersedia dalam kondisi layak pakai dan sesuai dengan yang tercantum di IK',
                'order_no'      => 10,
            ],
            [
                'prinsip_dasar' => 'Mematuhi Standar Kerja (MSK)',
                'item_check'    => 'Alat Bantu Kerja',
                'standard'      => 'Alat bantu kerja harus lengkap, tidak diperkenankan memulai pekerjaan jika ada alat bantu yang kurang',
                'order_no'      => 11,
            ],
            [
                'prinsip_dasar' => 'Mematuhi Standar Kerja (MSK)',
                'item_check'    => 'Alat Bantu Kerja',
                'standard'      => 'Digunakan sesuai dengan yang tercantum di IK',
                'order_no'      => 12,
            ],
            [
                'prinsip_dasar' => 'Mematuhi Standar Kerja (MSK)',
                'item_check'    => 'Limit Sample',
                'standard'      => 'Limit Sample disiapkan sebelum memulai proses kerja',
                'order_no'      => 13,
            ],
            [
                'prinsip_dasar' => 'Mematuhi Standar Kerja (MSK)',
                'item_check'    => 'PCCP',
                'standard'      => 'Terpasang dan terupdate',
                'order_no'      => 14,
            ],
            [
                'prinsip_dasar' => 'Mematuhi Standar Kerja (MSK)',
                'item_check'    => 'PCCP',
                'standard'      => 'Inspector melakukan pengecheckan sesuai PCCP (sampling check part N=1)',
                'order_no'      => 15,
            ],

            // =====================================================================
            // 2. Kerja Tuntas (Pengecheckan saat Istirahat)
            // =====================================================================
            [
                'prinsip_dasar' => 'Kerja Tuntas (Pengecheckan saat Istirahat)',
                'item_check'    => 'Kondisi Kerja Tuntas',
                'standard'      => 'Inspector melakukan pekerjaan secara tuntas sesuai ketentuan yang sudah ditetapkan (1 cycle)',
                'order_no'      => 16,
            ],
            [
                'prinsip_dasar' => 'Kerja Tuntas (Pengecheckan saat Istirahat)',
                'item_check'    => 'Penempatan Part',
                'standard'      => 'Posisi part before after benar sesuai layout',
                'order_no'      => 17,
            ],
            [
                'prinsip_dasar' => 'Kerja Tuntas (Pengecheckan saat Istirahat)',
                'item_check'    => 'Identitas Part',
                'standard'      => 'Menempelkan identitas part (Part Belum Selesai Proses) ketika meninggalkan meja kerja (ke toilet/istirahat)',
                'order_no'      => 18,
            ],
            [
                'prinsip_dasar' => 'Kerja Tuntas (Pengecheckan saat Istirahat)',
                'item_check'    => 'Pencatatan hasil Kerja di Kartu Produksi',
                'standard'      => 'Hasil kerja wajib ditulis setelah menyelesaikan 1 lot',
                'order_no'      => 19,
            ],
            [
                'prinsip_dasar' => 'Kerja Tuntas (Pengecheckan saat Istirahat)',
                'item_check'    => 'Point Preparation Alat Kerja/Support',
                'standard'      => 'Inspector paham frekuensi penggunaan dan maintenace alat bantu /support',
                'order_no'      => 20,
            ],

            // =====================================================================
            // 3. Penanganan Part NG
            // =====================================================================
            [
                'prinsip_dasar' => 'Penanganan Part NG',
                'item_check'    => 'Rule',
                'standard'      => 'Inspector melakukan handling part NG sesuai IK (paham penanganan part NG)',
                'order_no'      => 21,
            ],
            [
                'prinsip_dasar' => 'Penanganan Part NG',
                'item_check'    => 'Penempatan Part',
                'standard'      => 'Bucket part NG tersedia di area kerja (bawah meja)',
                'order_no'      => 22,
            ],
            [
                'prinsip_dasar' => 'Penanganan Part NG',
                'item_check'    => 'Penempatan Part',
                'standard'      => 'Part NG ditaruh di bucket khusus part NG jika ada',
                'order_no'      => 23,
            ],
            [
                'prinsip_dasar' => 'Penanganan Part NG',
                'item_check'    => 'Penempatan Part',
                'standard'      => 'Tidak ada barang tidak jelas di tempat part NG',
                'order_no'      => 24,
            ],
            [
                'prinsip_dasar' => 'Penanganan Part NG',
                'item_check'    => 'Label Part NG',
                'standard'      => 'Di akhir pengerjaan barang per 1 lot inspector melakukan labeling part NG',
                'order_no'      => 25,
            ],
            [
                'prinsip_dasar' => 'Penanganan Part NG',
                'item_check'    => 'Marking Part NG',
                'standard'      => 'Inspector melakukan marking di area part NG sesuai IK',
                'order_no'      => 26,
            ],
            [
                'prinsip_dasar' => 'Penanganan Part NG',
                'item_check'    => 'Part NG Pertama',
                'standard'      => 'Part NG pertama dilakukan cross cut Test',
                'order_no'      => 27,
            ],
            [
                'prinsip_dasar' => 'Penanganan Part NG',
                'item_check'    => 'Pencatatan Part NG',
                'standard'      => 'Jenis NG dicatat di kartu produksi tiap ditemukan part NG',
                'order_no'      => 28,
            ],
            [
                'prinsip_dasar' => 'Penanganan Part NG',
                'item_check'    => 'Alat Kerja/Support',
                'standard'      => 'Inspector paham jika alat kerja/support NG tidak dapat memulai / melanjutkan proses produksi',
                'order_no'      => 29,
            ],
            [
                'prinsip_dasar' => 'Penanganan Part NG',
                'item_check'    => 'Alat Kerja/Support',
                'standard'      => 'Inspector konfirmasi ke Leader bila ada alat kerja/support yang NG',
                'order_no'      => 30,
            ],

            // =====================================================================
            // 4. Penanganan Part Sebelum dan Sesudah Proses
            // =====================================================================
            [
                'prinsip_dasar' => 'Penanganan Part Sebelum dan Sesudah Proses',
                'item_check'    => 'Rule',
                'standard'      => 'Inspector paham rule handling maupun penempatan part sebelum dan sesudah proses',
                'order_no'      => 31,
            ],
            [
                'prinsip_dasar' => 'Penanganan Part Sebelum dan Sesudah Proses',
                'item_check'    => 'Identitas Proses',
                'standard'      => 'Inspector memasang pin before & after proses di bucket',
                'order_no'      => 32,
            ],
            [
                'prinsip_dasar' => 'Penanganan Part Sebelum dan Sesudah Proses',
                'item_check'    => 'Identitas Proses',
                'standard'      => 'Inspector menempatkan part sesuai dengan layout',
                'order_no'      => 33,
            ],
            [
                'prinsip_dasar' => 'Penanganan Part Sebelum dan Sesudah Proses',
                'item_check'    => 'Identitas Proses',
                'standard'      => 'Memastikan identitas part jelas (part vs Label)',
                'order_no'      => 34,
            ],

            // =====================================================================
            // 5. Kontrol Awal Operator
            // =====================================================================
            [
                'prinsip_dasar' => 'Kontrol Awal Operator',
                'item_check'    => 'Skill Map',
                'standard'      => 'Minimal Skill Map Operator adalah Grade A, jika dibawah itu harus ditraining ulang s/d OK (grade A)',
                'order_no'      => 35,
            ],
            [
                'prinsip_dasar' => 'Kontrol Awal Operator',
                'item_check'    => 'Skill Mapping',
                'standard'      => 'Inspector dinyatakan OK mengerjakan part sesuai spesialist',
                'order_no'      => 36,
            ],
            [
                'prinsip_dasar' => 'Kontrol Awal Operator',
                'item_check'    => 'Skill Mapping',
                'standard'      => 'Skill mapping tertempel di area kerja dan terupdate',
                'order_no'      => 37,
            ],
            [
                'prinsip_dasar' => 'Kontrol Awal Operator',
                'item_check'    => 'Initial Kontrol',
                'standard'      => 'Inspector sudah dinyatakan lulus dan terecord dalam initial kontrol operator',
                'order_no'      => 38,
            ],

            // =====================================================================
            // 6. APD
            // =====================================================================
            [
                'prinsip_dasar' => 'APD',
                'item_check'    => 'Penggunaan APD',
                'standard'      => 'Memakai APD sesuai standar',
                'order_no'      => 39,
            ],
        ];

        foreach ($items as $item) {
            OperatorComplianceItem::updateOrCreate(
                [
                    'prinsip_dasar' => $item['prinsip_dasar'],
                    'order_no'      => $item['order_no'],
                ],
                [
                    'item_check' => $item['item_check'],
                    'standard'   => $item['standard'],
                    'is_active'  => true,
                ]
            );
        }

        $this->command->info('OperatorComplianceItem seeder selesai: ' . count($items) . ' items inserted.');
    }
}
