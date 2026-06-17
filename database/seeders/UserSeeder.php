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

            // Sub Administrator (view and input only, no edit/delete/approve)

            // ===== PLANT KARAWANG =====
            // Supervisor
            ['name' => 'Mida Herdiyani', 'email' => 'spvqa@qc.com', 'role' => 'supervisor', 'plant' => 'karawang', 'password' => $defaultPassword, 'initials' => 'Mida'],
            ['name' => 'Arief Hidayat', 'email' => 'spvqc@qc.com', 'role' => 'supervisor', 'plant' => 'karawang', 'password' => $defaultPassword, 'initials' => 'Arief'],

            // Inspector (Karawang)
            ['name' => 'Irfan Arfian Kusnadi', 'email' => 'irfan@qc.com', 'role' => 'inspector', 'plant' => 'karawang', 'password' => $defaultPassword, 'initials' => 'ia'],
            ['name' => 'Anggi Purnama', 'email' => 'anggi@qc.com', 'role' => 'inspector', 'plant' => 'karawang', 'password' => $defaultPassword, 'initials' => 'ap'],
            ['name' => 'Gugun Kurniadi', 'email' => 'gugun@qc.com', 'role' => 'inspector', 'plant' => 'karawang', 'password' => $defaultPassword, 'initials' => 'gk'],
            ['name' => 'Dede Supriyadi', 'email' => 'dede@qc.com', 'role' => 'inspector', 'plant' => 'karawang', 'password' => $defaultPassword, 'initials' => 'ds'],
            ['name' => 'Arga Yudistira', 'email' => 'arga@qc.com', 'role' => 'inspector', 'plant' => 'karawang', 'password' => $defaultPassword, 'initials' => 'ay'],
            ['name' => 'Sopian Handani', 'email' => 'sopian@qc.com', 'role' => 'inspector', 'plant' => 'karawang', 'password' => $defaultPassword, 'initials' => 'sh'],
            ['name' => 'Yono Supriatno', 'email' => 'yono@qc.com', 'role' => 'inspector', 'plant' => 'karawang', 'password' => $defaultPassword, 'initials' => 'ys'],
            ['name' => 'Dinar Ashobar', 'email' => 'dinar@qc.com', 'role' => 'inspector', 'plant' => 'karawang', 'password' => $defaultPassword, 'initials' => 'da'],

            // Ka. Shift (Karawang)
            ['name' => 'Ahmad Jaeni', 'email' => 'kashift@qc.com', 'role' => 'kashift', 'plant' => 'karawang', 'password' => $defaultPassword, 'initials' => 'Ahmad'],

            // Asst. Manager (Karawang)
            ['name' => 'Iwan Setiawan', 'email' => 'manager@qc.com', 'role' => 'asst_manager', 'plant' => 'karawang', 'password' => $defaultPassword, 'initials' => 'Iwan'],

            // Manager (Karawang)
            ['name' => 'Desti Kurniasari', 'email' => 'generalmanager@qc.com', 'role' => 'manager', 'plant' => 'karawang', 'password' => $defaultPassword, 'initials' => 'Desti'],

            // Karu QC (Karawang)
            ['name' => 'Fitri', 'email' => 'fitri@qc.com', 'role' => 'karu_qc', 'plant' => 'karawang', 'password' => $defaultPassword, 'initials' => 'Fitri'],
            ['name' => 'Pipit', 'email' => 'pipit@qc.com', 'role' => 'karu_qc', 'plant' => 'karawang', 'password' => $defaultPassword, 'initials' => 'Pipit'],
            ['name' => 'Parlinah', 'email' => 'parlinah@qc.com', 'role' => 'karu_qc', 'plant' => 'karawang', 'password' => $defaultPassword, 'initials' => 'Parlinah'],

            // Kashift Plating (Karawang)
            ['name' => 'Kashift Plating', 'email' => 'kashiftplating@qc.com', 'role' => 'kashift_plating', 'plant' => 'karawang', 'password' => $defaultPassword],

            // SPV Plating (Karawang)
            ['name' => 'SPV Plating', 'email' => 'spvplating@qc.com', 'role' => 'supervisor_plating', 'plant' => 'karawang', 'password' => $defaultPassword],

            // Manager Plating (Karawang)
            ['name' => 'Manager Plating', 'email' => 'managerplating@qc.com', 'role' => 'manager_plating', 'plant' => 'karawang', 'password' => $defaultPassword],

            // ===== PLANT JAKARTA =====
            // Supervisor Jakarta
            ['name' => 'Masuli', 'email' => 'masuli.jkt@qc.com', 'role' => 'supervisor', 'plant' => 'jakarta', 'password' => $defaultPassword, 'initials' => 'Masuli'],

            // Kepala Regu Jakarta
            ['name' => 'Marsiah', 'email' => 'marsiah.jkt@qc.com', 'role' => 'karu_qc', 'plant' => 'jakarta', 'password' => $defaultPassword, 'initials' => 'Marsiah'],

            // In-Process Jakarta
            ['name' => 'Afrin Wibowo', 'email' => 'afrin.jkt@qc.com', 'role' => 'inspector', 'plant' => 'jakarta', 'password' => $defaultPassword, 'initials' => 'af'],
            ['name' => 'Anggriyani', 'email' => 'anggriyani.jkt@qc.com', 'role' => 'inspector', 'plant' => 'jakarta', 'password' => $defaultPassword, 'initials' => 'ay'],
            ['name' => 'Okah Retno Amriani', 'email' => 'okah.jkt@qc.com', 'role' => 'inspector', 'plant' => 'jakarta', 'password' => $defaultPassword, 'initials' => 'or'],
            ['name' => 'M. Miftahul Ulum', 'email' => 'ulum.jkt@qc.com', 'role' => 'inspector', 'plant' => 'jakarta', 'password' => $defaultPassword, 'initials' => 'um'],
            ['name' => 'Ilham Aldi Pratama', 'email' => 'ilham.jkt@qc.com', 'role' => 'inspector', 'plant' => 'jakarta', 'password' => $defaultPassword, 'initials' => 'ip'],
            ['name' => 'Tri Rahmadhani', 'email' => 'tri.jkt@qc.com', 'role' => 'inspector', 'plant' => 'jakarta', 'password' => $defaultPassword, 'initials' => 'tr'],

            // Sub Assy Jakarta
            ['name' => 'Sabrina Kurniawati', 'email' => 'sabrina.jkt@qc.com', 'role' => 'inspector', 'plant' => 'jakarta', 'password' => $defaultPassword, 'initials' => 'sb'],
            ['name' => 'Ririn Eka Prasetia', 'email' => 'ririn.jkt@qc.com', 'role' => 'inspector', 'plant' => 'jakarta', 'password' => $defaultPassword, 'initials' => 'rr'],
            ['name' => 'Syadina Juhro', 'email' => 'syadina.jkt@qc.com', 'role' => 'inspector', 'plant' => 'jakarta', 'password' => $defaultPassword, 'initials' => 'sj'],
        ];

        foreach ($users as $userData) {
            $user = User::where('email', $userData['email'])->first();

            // Resolve plant name to UUID
            $plantId = \App\Models\Plant::resolveId($userData['plant']);

            if ($user) {
                // Update existing user (don't update password to preserve custom passwords)
                $user->update([
                    'name' => $userData['name'],
                    'role' => $userData['role'],
                    'plant_id' => $plantId,
                    'initials' => $userData['initials'] ?? null,
                ]);
            } else {
                // Create new user
                User::create([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => Hash::make($userData['password']),
                    'role' => $userData['role'],
                    'plant_id' => $plantId,
                    'initials' => $userData['initials'] ?? null,
                ]);
            }
        }
    }
}
