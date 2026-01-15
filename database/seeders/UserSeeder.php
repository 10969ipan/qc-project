<?php

namespace Database\Seeders;

// To run this seeder independently, use the command:
// php artisan db:seed --class=UserSeeder

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $defaultPassword = env('DEFAULT_QC_PASSWORD', 'indoplat2526');
        $adminPassword = env('ADMIN_QC_PASSWORD', 'admin123');

        $users = [
            // Admin (access to both plants)
            ['name' => 'Administrator', 'email' => 'admin@qc.com', 'role' => 'admin', 'plant' => 'karawang', 'password' => $adminPassword],

            // ===== PLANT KARAWANG =====
            // Supervisor
            ['name' => 'Mida Herdiyani', 'email' => 'spvqa@qc.com', 'role' => 'supervisor', 'plant' => 'karawang', 'password' => $defaultPassword],
            ['name' => 'Arief Hidayat', 'email' => 'spvqc@qc.com', 'role' => 'supervisor', 'plant' => 'karawang', 'password' => $defaultPassword],

            // Inspector (Karawang)
            ['name' => 'Irfan Arfian Kusnadi', 'email' => 'irfan@qc.com', 'role' => 'inspector', 'plant' => 'karawang', 'password' => $defaultPassword],
            ['name' => 'Anggi Purnama', 'email' => 'anggi@qc.com', 'role' => 'inspector', 'plant' => 'karawang', 'password' => $defaultPassword],
            ['name' => 'Gugun Kurniadi', 'email' => 'gugun@qc.com', 'role' => 'inspector', 'plant' => 'karawang', 'password' => $defaultPassword],
            ['name' => 'Dede Supriyadi', 'email' => 'dede@qc.com', 'role' => 'inspector', 'plant' => 'karawang', 'password' => $defaultPassword],
            ['name' => 'Arga Yudistira', 'email' => 'arga@qc.com', 'role' => 'inspector', 'plant' => 'karawang', 'password' => $defaultPassword],
            ['name' => 'Sopian Handani', 'email' => 'sopian@qc.com', 'role' => 'inspector', 'plant' => 'karawang', 'password' => $defaultPassword],
            ['name' => 'Yono Supriatno', 'email' => 'yono@qc.com', 'role' => 'inspector', 'plant' => 'karawang', 'password' => $defaultPassword],
            ['name' => 'Dinar Ashobar', 'email' => 'dinar@qc.com', 'role' => 'inspector', 'plant' => 'karawang', 'password' => $defaultPassword],

            // Ka. Shift (Karawang)
            ['name' => 'Ahmad Jaeni', 'email' => 'kashift@qc.com', 'role' => 'kashift', 'plant' => 'karawang', 'password' => $defaultPassword],

            // Asst. Manager (Karawang)
            ['name' => 'Iwan Setiawan', 'email' => 'manager@qc.com', 'role' => 'asst_manager', 'plant' => 'karawang', 'password' => $defaultPassword],

            // Manager (Karawang)
            ['name' => 'Desti Kurniasari', 'email' => 'generalmanager@qc.com', 'role' => 'manager', 'plant' => 'karawang', 'password' => $defaultPassword],

            // Karu QC (Karawang)
            ['name' => 'Fitri', 'email' => 'fitri@qc.com', 'role' => 'karu_qc', 'plant' => 'karawang', 'password' => $defaultPassword],
            ['name' => 'Pipit', 'email' => 'pipit@qc.com', 'role' => 'karu_qc', 'plant' => 'karawang', 'password' => $defaultPassword],
            ['name' => 'Parlinah', 'email' => 'parlinah@qc.com', 'role' => 'karu_qc', 'plant' => 'karawang', 'password' => $defaultPassword],

            // Kashift Plating (Karawang)
            ['name' => 'Kashift Plating', 'email' => 'kashiftplating@qc.com', 'role' => 'kashift_plating', 'plant' => 'karawang', 'password' => $defaultPassword],

            // SPV Plating (Karawang)
            ['name' => 'SPV Plating', 'email' => 'spvplating@qc.com', 'role' => 'supervisor_plating', 'plant' => 'karawang', 'password' => $defaultPassword],

            // Manager Plating (Karawang)
            ['name' => 'Manager Plating', 'email' => 'managerplating@qc.com', 'role' => 'manager_plating', 'plant' => 'karawang', 'password' => $defaultPassword],

            // ===== PLANT JAKARTA =====
            // Supervisor Jakarta
            ['name' => 'Masuli', 'email' => 'masuli.jkt@qc.com', 'role' => 'supervisor', 'plant' => 'jakarta', 'password' => $defaultPassword],

            // Kepala Regu Jakarta
            ['name' => 'Marsiah', 'email' => 'marsiah.jkt@qc.com', 'role' => 'karu_qc', 'plant' => 'jakarta', 'password' => $defaultPassword],

            // In-Process Jakarta
            ['name' => 'Afrin Wibowo', 'email' => 'afrin.jkt@qc.com', 'role' => 'inspector', 'plant' => 'jakarta', 'password' => $defaultPassword],
            ['name' => 'Anggriyani', 'email' => 'anggriyani.jkt@qc.com', 'role' => 'inspector', 'plant' => 'jakarta', 'password' => $defaultPassword],
            ['name' => 'Okah Retno Amriani', 'email' => 'okah.jkt@qc.com', 'role' => 'inspector', 'plant' => 'jakarta', 'password' => $defaultPassword],
            ['name' => 'M. Miftahul Ulum', 'email' => 'ulum.jkt@qc.com', 'role' => 'inspector', 'plant' => 'jakarta', 'password' => $defaultPassword],
            ['name' => 'Ilham Aldi Pratama', 'email' => 'ilham.jkt@qc.com', 'role' => 'inspector', 'plant' => 'jakarta', 'password' => $defaultPassword],
            ['name' => 'Tri Rahmadhani', 'email' => 'tri.jkt@qc.com', 'role' => 'inspector', 'plant' => 'jakarta', 'password' => $defaultPassword],

            // Sub Assy Jakarta
            ['name' => 'Sabrina Kurniawati', 'email' => 'sabrina.jkt@qc.com', 'role' => 'inspector', 'plant' => 'jakarta', 'password' => $defaultPassword],
            ['name' => 'Ririn Eka Prasetia', 'email' => 'ririn.jkt@qc.com', 'role' => 'inspector', 'plant' => 'jakarta', 'password' => $defaultPassword],
            ['name' => 'Syadina Juhro', 'email' => 'syadina.jkt@qc.com', 'role' => 'inspector', 'plant' => 'jakarta', 'password' => $defaultPassword],
        ];

        foreach ($users as $userData) {
            $user = User::where('email', $userData['email'])->first();

            if ($user) {
                // Jangan update password jika user sudah ada (agar tidak tertimpa ke default)
                $user->update([
                    'name' => $userData['name'],
                    'role' => $userData['role'],
                    'plant' => $userData['plant'],
                ]);
            } else {
                User::create([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => Hash::make($userData['password']),
                    'role' => $userData['role'],
                    'plant' => $userData['plant'],
                ]);
            }
        }
    }
}
