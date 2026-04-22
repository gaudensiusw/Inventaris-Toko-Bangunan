<?php

namespace Modules\StockOpname\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StockOpnameController extends Controller
{
    public function index()
    {
        $opnames = [
            [
                'product' => 'Semen Portland - Gresik (CEM-001, kg)',
                'system' => '850', 'physical' => '850', 'diff' => '0',
                'value_diff' => 'Rp 0', 'status' => 'Sesuai'
            ],
            [
                'product' => 'Besi Beton 10mm x 12m (STL-002, piece)',
                'system' => '180', 'physical' => '180', 'diff' => '0',
                'value_diff' => 'Rp 0', 'status' => 'Sesuai'
            ],
            [
                'product' => 'Bata Merah Press (BRK-003, piece)',
                'system' => '15000', 'physical' => '15000', 'diff' => '0',
                'value_diff' => 'Rp 0', 'status' => 'Sesuai'
            ],
            [
                'product' => 'Pipa PVC 3" x 4m (PLB-005, piece)',
                'system' => '45', 'physical' => '45', 'diff' => '0',
                'value_diff' => 'Rp 0', 'status' => 'Sesuai'
            ]
        ];

        return view('stockopname::index', compact('opnames'));
    }
}
