<?php

namespace Modules\POS\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class POSController extends Controller
{
    public function index()
    {
        $products = [
            [
                'name' => 'Semen Portland - Gresik',
                'price' => 'Rp 75,000',
                'original_price' => 'Rp 80,000',
                'unit' => 'kg',
                'stock' => 850
            ],
            [
                'name' => 'Besi Beton 10mm x 12m',
                'price' => 'Rp 105,000',
                'original_price' => 'Rp 115,000',
                'unit' => 'piece',
                'stock' => 180
            ],
            [
                'name' => 'Bata Merah Press',
                'price' => 'Rp 1,200',
                'original_price' => 'Rp 1,500',
                'unit' => 'piece',
                'stock' => 15000
            ],
            [
                'name' => 'Cat Tembok Weathershield',
                'price' => 'Rp 725,000',
                'original_price' => 'Rp 750,000',
                'unit' => 'kg',
                'stock' => 95
            ],
        ];

        return view('pos::index', compact('products'));
    }
}
