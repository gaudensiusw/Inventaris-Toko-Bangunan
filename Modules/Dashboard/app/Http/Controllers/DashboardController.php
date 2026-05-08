<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\POS\Models\POS;
use Modules\Product\Models\Product;
use Modules\TagihanSupplier\Models\TagihanSupplier;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->role === 'operator') {
            return $this->operatorDashboard();
        }

        // Owner/Supervisor Dashboard Data
        $overdueInvoices = TagihanSupplier::with('supplier')
            ->where('status', '!=', 'lunas')
            ->where('jatuh_tempo', '<', now())
            ->get();

        $dueSoonInvoices = TagihanSupplier::with('supplier')
            ->where('status', '!=', 'lunas')
            ->whereBetween('jatuh_tempo', [now(), now()->addDays(7)])
            ->get();

        $stats = [
            'pendapatan_hari_ini' => POS::whereDate('tgl_transaksi', now())->sum('jumlah_bayar'),
            'total_piutang' => POS::where('status_pembayaran', '!=', 'lunas')->sum(DB::raw('total_tagihan - jumlah_bayar')),
            'nilai_stok' => Product::sum(DB::raw('stok * harga_beli')),
            'low_stock_products' => Product::whereRaw('stok <= min_stok')->with('category')->get(),
        ];

        return view('dashboard::index', compact('overdueInvoices', 'dueSoonInvoices', 'stats'));
    }

    private function operatorDashboard()
    {
        $user = auth()->user();

        $stats = [
            'sales_today' => POS::where('user_id', $user->id)
                                ->whereDate('tgl_transaksi', now())
                                ->sum('total_tagihan') ?? 0,
            'trx_count'   => POS::where('user_id', $user->id)
                                ->whereDate('tgl_transaksi', now())
                                ->count() ?? 0,
        ];

        $recentTransactions = POS::where('user_id', $user->id)
            ->with('pelanggan')
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard::operator', compact('stats', 'recentTransactions'));
    }
}
