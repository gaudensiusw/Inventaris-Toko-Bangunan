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
                'jabatan_id' => $adminId,
                'nama' => 'Rasti Melinda',
                'tanggal_masuk' => Carbon::now()->format('Y-m-d'),
                'aktif' => true,
            ],
            [
                'jabatan_id' => $karyawanId,
                'nama' => 'Yuril',
                'tanggal_masuk' => Carbon::now()->format('Y-m-d'),
                'aktif' => true,
            ],
            [
                'jabatan_id' => $karyawanId,
                'nama' => 'Dio',
                'tanggal_masuk' => Carbon::now()->format('Y-m-d'),
                'aktif' => true,
            ],
            [
                'jabatan_id' => $karyawanId,
                'nama' => 'Tony',
                'tanggal_masuk' => Carbon::now()->format('Y-m-d'),
                'aktif' => true,
            ],
            [
                'jabatan_id' => $supirId,
                'nama' => 'Yan',
                'tanggal_masuk' => Carbon::now()->format('Y-m-d'),
                'aktif' => true,
            ],
        ];

        foreach ($karyawans as $karyawan) {
            Karyawan::firstOrCreate(['nama' => $karyawan['nama']], $karyawan);
        }
    }
}
