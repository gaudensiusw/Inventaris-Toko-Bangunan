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
    public function index(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->role === 'operator') {
            return $this->operatorDashboard();
        }

        $filter = $request->query('filter', 'bulan');
        $now = \Carbon\Carbon::now();

        switch($filter) {
            case 'hari':
                $startDate = $now->copy()->startOfDay();
                $endDate = $now->copy()->endOfDay();
                break;
            case 'minggu':
                $startDate = $now->copy()->startOfWeek();
                $endDate = $now->copy()->endOfWeek();
                break;
            case 'bulan':
            default:
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                break;
        }

        // Owner/Supervisor Dashboard Data
        $overdueInvoices = TagihanSupplier::with('supplier')
            ->where('status', '!=', 'lunas')
            ->where('jatuh_tempo', '<', $now->toDateTimeString())
            ->get();

        $dueSoonInvoices = TagihanSupplier::with('supplier')
            ->where('status', '!=', 'lunas')
            ->whereBetween('jatuh_tempo', [$now->toDateTimeString(), $now->copy()->addDays(7)->toDateTimeString()])
            ->get();

        // 1. Revenue & Profit Stats based on filter
        $posInRange = POS::whereBetween('created_at', [$startDate, $endDate])->get();
        $revenue = $posInRange->sum('total_tagihan');
        $cashRevenue = $posInRange->where('status_pembayaran', 'lunas')->sum('total_tagihan');
        $creditRevenue = $posInRange->where('status_pembayaran', '!=', 'lunas')->sum('total_tagihan');

        // Estimate Net Profit (Revenue - COGS - Ops)
        // For simplicity in dashboard, we use a simplified calculation or just 0 for now if too complex, 
        // but let's try to get a rough estimate if possible.
        $netProfit = 0;
        if (count($posInRange) > 0) {
            $posIds = $posInRange->pluck('id');
            $cogs = \Modules\POS\Models\POSDetail::whereIn('pos_id', $posIds)
                ->with('product')
                ->get()
                ->sum(function($d) { return $d->qty * ($d->product->harga_beli ?? 0); });
            $ops = \Modules\OperationalItem\Models\ItemOperasional::whereBetween('created_at', [$startDate, $endDate])
                ->get()
                ->sum(function($i) { return $i->jumlah * $i->harga; });
            $netProfit = $revenue - $cogs - $ops;
        }

        $stats = [
            'pendapatan' => $revenue,
            'total_piutang' => POS::where('status_pembayaran', '!=', 'lunas')->sum(DB::raw('total_tagihan - jumlah_bayar')),
            'nilai_stok' => Product::sum(DB::raw('stok * harga_beli')),
            'low_stock_products' => Product::whereRaw('stok <= min_stok')->with('category')->paginate(5, ['*'], 'stock_page')->withQueryString(),
            'net_profit' => $netProfit,
            'cash_revenue' => $cashRevenue,
            'credit_revenue' => $creditRevenue,
        ];

        // --- CHART DATA (optimized: 1-2 queries via GROUP BY, bukan N*2 queries) ---
        $chartData = [];

        if ($filter == 'hari') {
            $rangeStart = $now->copy()->subDays(6)->startOfDay();
            $rangeEnd   = $now->copy()->endOfDay();
            $dateFormat = 'DATE(pos.created_at)';
        } elseif ($filter == 'minggu') {
            $rangeStart = $now->copy()->startOfWeek();
            $rangeEnd   = $now->copy()->endOfWeek()->endOfDay();
            $dateFormat = 'DATE(pos.created_at)';
        } else {
            $rangeStart = $now->copy()->startOfMonth();
            $rangeEnd   = $now->copy()->endOfMonth()->endOfDay();
            $dateFormat = 'DATE(pos.created_at)';
        }

        // 1 query untuk revenue per hari
        $revenueRows = DB::table('pos')
            ->selectRaw("DATE(created_at) as tgl, SUM(total_tagihan) as revenue")
            ->whereBetween('created_at', [$rangeStart, $rangeEnd])
            ->groupByRaw('DATE(created_at)')
            ->pluck('revenue', 'tgl');

        // 1 query untuk COGS per hari (JOIN ke produk untuk harga_beli)
        $cogsRows = DB::table('pos_detail as pd')
            ->join('pos', 'pos.id', '=', 'pd.pos_id')
            ->join('produk as p', 'p.id', '=', 'pd.produk_id')
            ->selectRaw("DATE(pos.created_at) as tgl, SUM(pd.qty * COALESCE(p.harga_beli, 0)) as cogs")
            ->whereBetween('pos.created_at', [$rangeStart, $rangeEnd])
            ->groupByRaw('DATE(pos.created_at)')
            ->pluck('cogs', 'tgl');

        // Bangun array chart dari tanggal-ke-tanggal
        $period = new \DatePeriod($rangeStart->toDateTime(), new \DateInterval('P1D'), $rangeEnd->toDateTime());
        foreach ($period as $day) {
            $dayKey = $day->format('Y-m-d');
            $rev    = (float) ($revenueRows[$dayKey] ?? 0);
            $cogs   = (float) ($cogsRows[$dayKey] ?? 0);

            if ($filter == 'hari') {
                $label = \Carbon\Carbon::parse($dayKey)->translatedFormat('d M');
            } elseif ($filter == 'minggu') {
                $label = \Carbon\Carbon::parse($dayKey)->translatedFormat('D');
            } else {
                $label = \Carbon\Carbon::parse($dayKey)->format('d');
            }

            $chartData[] = [
                'label'   => $label,
                'revenue' => $rev,
                'profit'  => $rev - $cogs,
            ];
        }

        return view('dashboard::index', compact('overdueInvoices', 'dueSoonInvoices', 'stats', 'filter', 'chartData'));
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
