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
        // Buat Akun Admin
        User::updateOrCreate(
            ['email' => 'admin@qc.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]
        );

        // Buat Akun Supervisor
        User::updateOrCreate(
            ['email' => 'spvqa@qc.com'],
            [
                'name' => 'Mida Herdiyani',
                'password' => Hash::make('indoplat2526'),
                'role' => 'supervisor',
            ]
        );

        User::updateOrCreate(
            ['email' => 'spvqc@qc.com'],
            [
                'name' => 'Arief Hidayat',
                'password' => Hash::make('indoplat2526'),
                'role' => 'supervisor',
            ]
        );

        // Buat Akun Ka. Shift
        User::updateOrCreate(
            ['email' => 'kashift@qc.com'],
            [
                'name' => 'Ahmad Jaeni',
                'password' => Hash::make('indoplat2526'),
                'role' => 'kashift',
            ]
        );

        // Buat Akun Asst. Manager
        User::updateOrCreate(
            ['email' => 'manager@qc.com'],
            [
                'name' => 'Iwan Setiawan',
                'password' => Hash::make('indoplat2526'),
                'role' => 'asst_manager',
            ]
        );

        // Buat Akun Manager
        User::updateOrCreate(
            ['email' => 'generalmanager@qc.com'],
            [
                'name' => 'Desti Kurniasari',
                'password' => Hash::make('indoplat2526'),
                'role' => 'manager',
            ]
        );

        // Buat Akun Inspector
        $inspectors = [
            ['name' => 'IRFAN ARFIAN KUSNADI'],
            ['name' => 'ANGGI PURNAMA'],
            ['name' => 'GUGUN KURNIADI'],
            ['name' => 'DEDE SUPRIYADI'],
            ['name' => 'ARGA YUDISTIRA'],
            ['name' => 'SOPIAN HANDANI'],
            ['name' => 'YONO SUPRIATNO'],
            ['name' => 'DINAR ASHOBAR'],
            ['name' => 'FITRI NUR HIKMAH'],
            ['name' => 'PARLINAH'],
            ['name' => 'RITA PIPIT N'],
            ['name' => 'KUSNIATI'],
            ['name' => 'SUJI PIADINI'],
            ['name' => 'SITI AISYAH'],
            ['name' => 'CHERLEY AGUSTINI'],
            ['name' => 'YUDI SETIAWAN'],
            ['name' => 'AINUN NABILLAH NURMEILIA'],
            ['name' => 'DEWI MURTASIMAH'],
            ['name' => 'ENDI KASPARI'],
            ['name' => 'MAYA DWI PRATIWI'],
            ['name' => 'SHERLI PURWANTI'],
            ['name' => 'RADI YANA'],
            ['name' => 'TARYEM KURNIA'],
            ['name' => 'CONDRO LUKITO'],
            ['name' => 'EKA AGUNG NUGROHO'],
            ['name' => 'ELSA SUHASTINA'],
            ['name' => 'SYIFA MEILANI'],
            ['name' => 'ZHAFIRA NUR HALIZAH'],
            ['name' => 'RINJANI UMI SHOLIHATIN'],
            ['name' => 'ETI NURJANAH'],
            ['name' => 'SASKIA NUR AINI ASHILA'],
            ['name' => 'NURAENI'],
            ['name' => 'TEGUH HAERUDIN'],
            ['name' => 'TARISA RUHYANTI'],
            ['name' => 'CICI DWI ANJANI'],
            ['name' => 'FERDY JO'],
            ['name' => 'ULYA SALSABILA'],
            ['name' => 'LINGGA PERDANI LINTANG A'],
            ['name' => 'NENENG NUR FADILLAH'],
            ['name' => 'DADI'],
            ['name' => 'TARMAN MAULANA'],
            ['name' => 'IRFAN AHMAD FAUZI'],
            ['name' => 'RENI NURJANAH'],
            ['name' => 'HALYZAH RUDIANAWATI'],
            ['name' => 'MUHAMAD APRIYAN'],
            ['name' => 'MUHAMAD PANJI AKBARi'],
            ['name' => 'ANA ERI YANTI'],
            ['name' => 'MUHAMAD DIAZ'],
            ['name' => 'BUDI ARIF NIZARFIRMANSYAH'],
            ['name' => 'MAHESA CHANDRA FERI'],
            ['name' => 'DEA SAFITRI BASHA'],
            ['name' => 'ALISA NURFAUZIAH'],
            ['name' => 'SAEFUL ANWAR'],
            ['name' => 'MUHAMMAD FARHAN HAKIM'],
            ['name' => 'FARHAN PUTRA PERDANA'],
            ['name' => 'AZZAHRA HODIJAH'],
            ['name' => 'M RIZKY SEPTIYANTO'],
            ['name' => 'REZA WULANDARI'],
            ['name' => 'OSHEF'],
        ];

        foreach ($inspectors as $inspectorData) {
            $firstName = explode(' ', $inspectorData['name'])[0];
            $email = strtolower($firstName) . '@qc.com';
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => ucwords(strtolower($inspectorData['name'])),
                    'password' => Hash::make('indoplat2526'),
                    'role' => 'inspector',
                ]
            );
        }
    }
}
