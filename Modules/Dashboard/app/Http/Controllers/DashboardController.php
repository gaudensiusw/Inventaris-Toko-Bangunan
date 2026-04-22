<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $overdueInvoices = [
            ['supplier' => 'UD Karya Mandiri', 'inv' => 'INV-2026-018', 'amount' => 'Rp 3.6M', 'days' => '2'],
            ['supplier' => 'CV Sukses Makmur', 'inv' => 'INV-2026-020', 'amount' => 'Rp 7.8M', 'days' => '3'],
        ];

        $dueSoonInvoices = [
            ['supplier' => 'Toko Bangunan Sentosa', 'inv' => 'INV-2026-015', 'days' => '0'],
        ];

        return view('dashboard::index', compact('overdueInvoices', 'dueSoonInvoices'));
    }
}
