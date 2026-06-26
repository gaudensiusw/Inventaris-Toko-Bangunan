<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'bulan'); // hari, minggu, bulan, tahun
        $now = \Carbon\Carbon::now();
        $selectedYear = $request->query('year', $now->format('Y'));
        $availableYears = [2024, 2025, 2026, 2027, 2028];

        switch($filter) {
            case 'hari':
                $startDate = $now->copy()->startOfDay();
                $endDate = $now->copy()->endOfDay();
                $label = "Hari Ini, " . $now->translatedFormat('d M Y');
                break;
            case 'minggu':
                $startDate = $now->copy()->startOfWeek();
                $endDate = $now->copy()->endOfWeek();
                $label = "Minggu Ini (" . $startDate->translatedFormat('d M') . " - " . $endDate->translatedFormat('d M Y') . ")";
                break;
            case 'tahun':
                $targetDate = \Carbon\Carbon::createFromDate($selectedYear, 1, 1);
                $startDate = $targetDate->copy()->startOfYear();
                $endDate = $targetDate->copy()->endOfYear();
                $label = "Tahun " . $targetDate->format('Y');
                break;
            case 'bulan':
            default:
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                $label = "Bulan " . $now->translatedFormat('F Y');
                break;
        }

        // 1. Total Revenue
        $totalRevenue = 0;
        $transactionCount = 0;
        if(class_exists(\Modules\POS\Models\POS::class)) {
            $posQuery = \Modules\POS\Models\POS::whereBetween('created_at', [$startDate, $endDate]);
            $totalRevenue = $posQuery->sum('total_tagihan');
            $transactionCount = $posQuery->count();
        }

        // 2. COGS (HPP)
        $cogs = 0;
        if(class_exists(\Modules\POS\Models\POSDetail::class)) {
            $cogs = \Illuminate\Support\Facades\DB::table('pos_detail')
                ->join('pos', 'pos.id', '=', 'pos_detail.pos_id')
                ->join('produk', 'produk.id', '=', 'pos_detail.produk_id')
                ->whereBetween('pos.created_at', [$startDate, $endDate])
                ->sum(\Illuminate\Support\Facades\DB::raw('pos_detail.qty * COALESCE(produk.harga_beli, 0)'));
        }

        // 3. Gross Profit
        $grossProfit = $totalRevenue - $cogs;
        $grossMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;

        // 4. Inventory Value
        $inventoryValue = 0;
        if(class_exists(\Modules\Product\Models\Product::class)) {
            $inventoryValue = \Modules\Product\Models\Product::sum(\Illuminate\Support\Facades\DB::raw('stok * COALESCE(harga_beli, 0)'));
        }

        // 5. Operational Expenses
        $opsExpenses = 0;
        if(class_exists(\Modules\OperationalItem\Models\ItemOperasional::class)) {
            $opsExpenses = \Modules\OperationalItem\Models\ItemOperasional::whereBetween('created_at', [$startDate, $endDate])
                ->sum(\Illuminate\Support\Facades\DB::raw('jumlah * harga'));
        }

        // 6. Net Profit
        $netProfit = $grossProfit - $opsExpenses;
        $netMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        // --- CHART DATA ---
        $chartData = [];
        $fetchStats = function($start, $end, $label) {
            $mRev = class_exists(\Modules\POS\Models\POS::class) ? \Modules\POS\Models\POS::whereBetween('created_at', [$start, $end])->sum('total_tagihan') : 0;
            $mCogs = \Illuminate\Support\Facades\DB::table('pos_detail')
                ->join('pos', 'pos.id', '=', 'pos_detail.pos_id')
                ->join('produk', 'produk.id', '=', 'pos_detail.produk_id')
                ->whereBetween('pos.created_at', [$start, $end])
                ->sum(\Illuminate\Support\Facades\DB::raw('pos_detail.qty * COALESCE(produk.harga_beli, 0)'));
            
            return [
                'label' => $label,
                'revenue' => (float)$mRev,
                'cogs' => (float)$mCogs,
                'profit' => (float)($mRev - $mCogs)
            ];
        };

        if ($filter == 'hari') {
            // Last 7 days to show a trend for "Daily"
            for ($i = 0; $i <= 6; $i++) {
                $dStart = $now->copy()->subDays(6 - $i)->startOfDay();
                $dEnd = $dStart->copy()->endOfDay();
                $chartData[] = $fetchStats($dStart, $dEnd, $dStart->translatedFormat('d M'));
            }
        } elseif ($filter == 'minggu') {
            // 7 Days of this week
            $wStart = $now->copy()->startOfWeek();
            for ($i = 0; $i < 7; $i++) {
                $dStart = $wStart->copy()->addDays($i)->startOfDay();
                $dEnd = $dStart->copy()->endOfDay();
                $chartData[] = $fetchStats($dStart, $dEnd, $dStart->translatedFormat('D'));
            }
        } elseif ($filter == 'bulan') {
            // Days of this month
            $daysInMonth = $now->daysInMonth;
            $mStart = $now->copy()->startOfMonth();
            for ($i = 0; $i < $daysInMonth; $i++) {
                $dStart = $mStart->copy()->addDays($i)->startOfDay();
                $dEnd = $dStart->copy()->endOfDay();
                $chartData[] = $fetchStats($dStart, $dEnd, $dStart->format('d'));
            }
        } else { // tahun
            // 12 Months of selected year
            for ($i = 1; $i <= 12; $i++) {
                $mStart = \Carbon\Carbon::createFromDate($selectedYear, $i, 1)->startOfMonth();
                $mEnd = $mStart->copy()->endOfMonth();
                $chartData[] = $fetchStats($mStart, $mEnd, $mStart->translatedFormat('M'));
            }
        }

        // --- PRODUCT PROFITABILITY ---
        $productStats = [];
        if(class_exists(\Modules\POS\Models\POSDetail::class)) {
            $bestSelling = \Illuminate\Support\Facades\DB::table('pos_detail')
                ->join('pos', 'pos.id', '=', 'pos_detail.pos_id')
                ->join('produk', 'produk.id', '=', 'pos_detail.produk_id')
                ->whereBetween('pos.created_at', [$startDate, $endDate])
                ->select(
                    'produk.id',
                    'produk.nama',
                    'produk.sku',
                    'produk.harga_beli',
                    'produk.harga_jual',
                    \Illuminate\Support\Facades\DB::raw('SUM(pos_detail.qty) as qty_sold'),
                    \Illuminate\Support\Facades\DB::raw('SUM(pos_detail.subtotal) as revenue'),
                    \Illuminate\Support\Facades\DB::raw('SUM(pos_detail.qty * COALESCE(produk.harga_beli, 0)) as cogs')
                )
                ->groupBy('produk.id', 'produk.nama', 'produk.sku', 'produk.harga_beli', 'produk.harga_jual')
                ->orderByDesc('revenue')
                ->limit(10)
                ->get();

            foreach ($bestSelling as $item) {
                $revenue = (float) $item->revenue;
                $cogs = (float) $item->cogs;
                $gross_profit = $revenue - $cogs;
                $productStats[] = [
                    'name' => $item->nama,
                    'sku' => $item->sku ?? '-',
                    'qty_sold' => (int) $item->qty_sold,
                    'hpp' => (float) $item->harga_beli,
                    'selling_price' => (float) $item->harga_jual,
                    'revenue' => $revenue,
                    'cogs' => $cogs,
                    'gross_profit' => $gross_profit,
                    'margin' => $revenue > 0 ? ($gross_profit / $revenue) * 100 : 0,
                ];
            }
        }

        // --- INVOICES SUMMARY ---
        $invoiceStats = [
            'paid_amount' => 0, 'paid_count' => 0,
            'pending_amount' => 0, 'pending_count' => 0,
            'partial_amount' => 0, 'partial_count' => 0
        ];
        if(class_exists(\Modules\TagihanSupplier\Models\TagihanSupplier::class)) {
            $paidQuery = \Modules\TagihanSupplier\Models\TagihanSupplier::where('status', 'lunas')->whereBetween('updated_at', [$startDate, $endDate]);
            $pendingQuery = \Modules\TagihanSupplier\Models\TagihanSupplier::where('status', 'belum_lunas')->whereBetween('created_at', [$startDate, $endDate]);
            $partialQuery = \Modules\TagihanSupplier\Models\TagihanSupplier::where('status', 'sebagian')->whereBetween('created_at', [$startDate, $endDate]);
            
            $invoiceStats = [
                'paid_amount' => (float)$paidQuery->sum('total'),
                'paid_count' => $paidQuery->count(),
                'pending_amount' => (float)$pendingQuery->sum('total'), // Simplified due to no jumlah_dibayar
                'pending_count' => $pendingQuery->count(),
                'partial_amount' => (float)$partialQuery->sum('total') / 2, // Dummy calculation for partial
                'partial_count' => $partialQuery->count()
            ];
        }

        // opsItems is passed to the view, we need to fetch only what's necessary or limit it to avoid memory issues on huge ops records.
        // If the view displays it, let's just fetch it but limit to a reasonable number to avoid crashing.
        $opsItems = [];
        if(class_exists(\Modules\OperationalItem\Models\ItemOperasional::class)) {
            $opsItems = \Modules\OperationalItem\Models\ItemOperasional::whereBetween('created_at', [$startDate, $endDate])
                ->latest()->limit(50)->get();
        }

        return view('report::index', compact(
            'filter', 'label', 'totalRevenue', 'transactionCount', 'cogs', 
            'grossProfit', 'grossMargin', 'inventoryValue', 'opsExpenses', 
            'netProfit', 'netMargin', 'chartData', 'productStats', 'opsItems', 'invoiceStats',
            'selectedYear', 'availableYears'
        ));
    }
}
