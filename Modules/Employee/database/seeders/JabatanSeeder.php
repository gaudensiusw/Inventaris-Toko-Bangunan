<?php

namespace Modules\Employee\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Employee\Models\Jabatan;

class JabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jabatans = [
            [
                'nama_jabatan' => 'Admin',
                'gaji_harian' => 50000,
                'uang_makan' => 15000,
                'uang_pulsa' => 50000,
            ],
            [
                'nama_jabatan' => 'Karyawan',
                'gaji_harian' => 70000,
                'uang_makan' => 15000,
                'uang_pulsa' => 50000,
            ],
            [
                'nama_jabatan' => 'Supir',
                'gaji_harian' => 85000,
                'uang_makan' => 15000,
                'uang_pulsa' => 50000,
            ],
        ];

        foreach ($jabatans as $jabatan) {
            Jabatan::firstOrCreate(['nama_jabatan' => $jabatan['nama_jabatan']], $jabatan);
        }
    }
}
