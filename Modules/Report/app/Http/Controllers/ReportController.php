<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $reports = [
            [
                'name' => 'Laporan Penjualan Harian',
                'description' => 'Ringkasan transaksi penjualan per hari ini.',
                'icon' => 'chart-bar'
            ],
            [
                'name' => 'Laporan Stok Barang',
                'description' => 'Detail posisi stok dan nilai aset barang.',
                'icon' => 'cube'
            ],
            [
                'name' => 'Laporan Laba Rugi',
                'description' => 'Perhitungan margin keuntungan bersih.',
                'icon' => 'currency-dollar'
            ],
            [
                'name' => 'Laporan Hutang Supplier',
                'description' => 'Daftar tagihan yang belum dibayarkan.',
                'icon' => 'clipboard-list'
            ],
            [
                'name' => 'Laporan Performa Karyawan',
                'description' => 'Analisis produktivitas tim kasir dan sales.',
                'icon' => 'users'
            ],
        ];

        return view('report::index', compact('reports'));
    }
}
