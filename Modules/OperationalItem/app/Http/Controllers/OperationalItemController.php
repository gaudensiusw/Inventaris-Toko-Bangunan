<?php

namespace Modules\OperationalItem\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OperationalItemController extends Controller
{
    public function index()
    {
        $items = [
            [
                'name' => 'Printer Thermal Epson',
                'category' => 'Elektronik',
                'stock' => '2',
                'condition' => 'Baik',
                'last_check' => '01 Mar 2024'
            ],
            [
                'name' => 'Timbangan Digital 500kg',
                'category' => 'Alat Berat',
                'stock' => '1',
                'condition' => 'Perlu Kalibrasi',
                'last_check' => '15 Feb 2024'
            ],
            [
                'name' => 'Laptop Kasir ASUS',
                'category' => 'Elektronik',
                'stock' => '3',
                'condition' => 'Baik',
                'last_check' => '10 Mar 2024'
            ],
            [
                'name' => 'Meja Kerja Office',
                'category' => 'Furniture',
                'stock' => '5',
                'condition' => 'Baik',
                'last_check' => '01 Jan 2024'
            ],
        ];

        return view('operationalitem::index', compact('items'));
    }
}
