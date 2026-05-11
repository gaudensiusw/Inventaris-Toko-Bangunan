<?php

namespace Modules\Employee\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Employee\Models\Karyawan;
use Modules\Employee\Models\Jabatan;
use Carbon\Carbon;

class KaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminId = Jabatan::where('nama_jabatan', 'Admin')->value('id');
        $karyawanId = Jabatan::where('nama_jabatan', 'Karyawan')->value('id');
        $supirId = Jabatan::where('nama_jabatan', 'Supir')->value('id');

        $karyawans = [
            [
                'kode_karyawan' => 'EMP-001',
                'jabatan_id' => $adminId,
                'nama' => 'Rasti Melinda',
                'email' => 'rasti@tokobangunan.com',
                'bonus_tetap' => 500000,
                'tanggal_masuk' => Carbon::now()->format('Y-m-d'),
                'aktif' => true,
            ],
            [
                'kode_karyawan' => 'EMP-002',
                'jabatan_id' => $karyawanId,
                'nama' => 'Yuril',
                'email' => 'yuril@tokobangunan.com',
                'bonus_tetap' => 500000,
                'tanggal_masuk' => Carbon::now()->format('Y-m-d'),
                'aktif' => true,
            ],
            [
                'kode_karyawan' => 'EMP-003',
                'jabatan_id' => $karyawanId,
                'nama' => 'Dio',
                'email' => 'dio@tokobangunan.com',
                'bonus_tetap' => 500000,
                'tanggal_masuk' => Carbon::now()->format('Y-m-d'),
                'aktif' => true,
            ],
            [
                'kode_karyawan' => 'EMP-004',
                'jabatan_id' => $karyawanId,
                'nama' => 'Tony',
                'email' => 'tony@tokobangunan.com',
                'bonus_tetap' => 500000,
                'tanggal_masuk' => Carbon::now()->format('Y-m-d'),
                'aktif' => true,
            ],
            [
                'kode_karyawan' => 'EMP-005',
                'jabatan_id' => $supirId,
                'nama' => 'Yan',
                'email' => 'yan@tokobangunan.com',
                'bonus_tetap' => 500000,
                'tanggal_masuk' => Carbon::now()->format('Y-m-d'),
                'aktif' => true,
            ],
        ];

        foreach ($karyawans as $karyawan) {
            Karyawan::firstOrCreate(['nama' => $karyawan['nama']], $karyawan);
        }
    }
}
