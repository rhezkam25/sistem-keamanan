<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Administrator',
            'nip' => 'ADM001',
            'email' => 'admin@kantor.com',
            'phone' => '08100000001',
            'jabatan' => 'Administrator Sistem',
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);

        $pejabat = User::create([
            'name' => 'Budi Santoso',
            'nip' => 'PJB001',
            'email' => 'pejabat@kantor.com',
            'phone' => '08100000002',
            'jabatan' => 'Kepala Divisi IT',
            'role' => 'pejabat',
            'password' => Hash::make('password'),
        ]);

        $staff = User::create([
            'name' => 'Siti Rahayu',
            'nip' => 'STF001',
            'email' => 'staff@kantor.com',
            'phone' => '08100000003',
            'jabatan' => 'Staff Administrasi',
            'role' => 'staff',
            'pejabat_id' => $pejabat->id,
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Andi Kurniawan',
            'nip' => 'SAT001',
            'email' => 'satpam@kantor.com',
            'phone' => '08100000004',
            'jabatan' => 'Petugas Keamanan',
            'role' => 'satpam',
            'password' => Hash::make('password'),
        ]);
    }
}
