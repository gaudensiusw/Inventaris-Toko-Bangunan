<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Product\Models\Product;
use Modules\Customer\Models\Customer;
use Modules\Supplier\Models\Supplier;
use Modules\TagihanSupplier\Models\TagihanSupplier;
use Modules\OperationalItem\Models\ItemOperasional;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_produk' => Product::count(),
            'stok_rendah' => Product::where('stok', '<=', 5)->count(),
            'total_customer' => Customer::count(),
            'total_supplier' => Supplier::count(),
            'total_hutang' => TagihanSupplier::where('status', '!=', 'lunas')->sum('total'),
            'hutang_jatuh_tempo' => TagihanSupplier::where('status', '!=', 'lunas')
                                    ->where('jatuh_tempo', '<=', now()->addDays(7))
                                    ->count(),
            'total_aset_operasional' => ItemOperasional::count(),
            'nilai_stok' => Product::sum(\DB::raw('stok * harga_beli')),
        ];

        return view('dashboard', compact('stats'));
    }
}
