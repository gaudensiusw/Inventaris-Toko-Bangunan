<?php

namespace Modules\StockManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StockManagementController extends Controller
{
    public function index()
    {
        $stocks = [
            [
                'product' => 'Semen Portland - Gresik', 'sku' => 'CEM-001', 'stock' => '850 kg',
                'minmax' => '200/2000', 'value' => 'Rp 52,700,000', 'status' => 'Normal'
            ],
            [
                'product' => 'Besi Beton 10mm x 12m', 'sku' => 'STL-002', 'stock' => '180 piece',
                'minmax' => '50/500', 'value' => 'Rp 15,300,000', 'status' => 'Normal'
            ],
            [
                'product' => 'Bata Merah Press', 'sku' => 'BRK-003', 'stock' => '15000 piece',
                'minmax' => '5000/30000', 'value' => 'Rp 14,250,000', 'status' => 'Normal'
            ],
            [
                'product' => 'Pipa PVC 3" x 4m', 'sku' => 'PLB-005', 'stock' => '45 piece',
                'minmax' => '100/400', 'value' => 'Rp 2,160,000', 'status' => 'Low Stock'
            ]
        ];

        $transactions = [
            [
                'date' => '17 Mar 2026', 'type' => 'in', 'product' => 'Semen Portland - Gresik',
                'qty' => '200 kg', 'price' => 'Rp 62,000', 'total' => 'Rp 12,400,000', 'ref' => 'PO-2026-001', 'notes' => 'Restock'
            ],
            [
                'date' => '16 Mar 2026', 'type' => 'out', 'product' => 'Besi Beton 10mm x 12m',
                'qty' => '10 piece', 'price' => 'Rp 85,000', 'total' => 'Rp 850,000', 'ref' => 'USE-001', 'notes' => 'Proyek A'
            ],
            [
                'date' => '15 Mar 2026', 'type' => 'sale', 'product' => 'Bata Merah Press',
                'qty' => '500 piece', 'price' => 'Rp 950', 'total' => 'Rp 475,000', 'ref' => 'INV-2026-015', 'notes' => '-'
            ],
            [
                'date' => '14 Mar 2026', 'type' => 'adjustment', 'product' => 'Pipa PVC 3" x 4m',
                'qty' => '-5 piece', 'price' => 'Rp 48,000', 'total' => 'Rp -240,000', 'ref' => 'ADJ-001', 'notes' => 'Rusak'
            ],
        ];

        return view('stockmanagement::index', compact('stocks', 'transactions'));
    }
}
