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
        $pos = [];
        if(class_exists(\Modules\POS\Models\POS::class)) {
            $pos = \Modules\POS\Models\POS::whereBetween('created_at', [$startDate, $endDate])->get();
        }
        $totalRevenue = count($pos) > 0 ? $pos->sum('total_tagihan') : 0;
        $transactionCount = count($pos);

        // 2. COGS (HPP)
        $posDetails = [];
        if(class_exists(\Modules\POS\Models\POSDetail::class)) {
            $posDetails = \Modules\POS\Models\POSDetail::with('product')
                ->whereHas('pos', function($q) use ($startDate, $endDate) {
                    $q->whereBetween('created_at', [$startDate, $endDate]);
                })->get();
        }
            
        $cogs = count($posDetails) > 0 ? $posDetails->sum(function($detail) {
            return $detail->qty * ($detail->product->harga_beli ?? 0);
        }) : 0;

        // 3. Gross Profit
        $grossProfit = $totalRevenue - $cogs;
        $grossMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;

        // 4. Inventory Value
        $inventoryValue = 0;
        if(class_exists(\Modules\Product\Models\Product::class)) {
            $inventoryValue = \Modules\Product\Models\Product::all()->sum(function($prod) {
                return $prod->stok * $prod->harga_beli;
            });
        }

        // 5. Operational Expenses
        $opsItems = [];
        if(class_exists(\Modules\OperationalItem\Models\ItemOperasional::class)) {
            $opsItems = \Modules\OperationalItem\Models\ItemOperasional::whereBetween('created_at', [$startDate, $endDate])->get();
        }
        $opsExpenses = count($opsItems) > 0 ? $opsItems->sum(function($item) {
            return $item->jumlah * $item->harga;
        }) : 0;

        // 6. Net Profit
        $netProfit = $grossProfit - $opsExpenses;
        $netMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        // --- CHART DATA (Last 12 Months) ---
        $chartData = [];
        for ($i = 11; $i >= 0; $i--) {
            $monthStart = \Carbon\Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = \Carbon\Carbon::now()->subMonths($i)->endOfMonth();
            
            $mRev = class_exists(\Modules\POS\Models\POS::class) ? \Modules\POS\Models\POS::whereBetween('created_at', [$monthStart, $monthEnd])->sum('total_tagihan') : 0;
            $mDetails = class_exists(\Modules\POS\Models\POSDetail::class) ? \Modules\POS\Models\POSDetail::with('product')->whereHas('pos', function($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('created_at', [$monthStart, $monthEnd]);
            })->get() : [];
            $mCogs = count($mDetails) > 0 ? $mDetails->sum(function($detail) {
                return $detail->qty * ($detail->product->harga_beli ?? 0);
            }) : 0;
            
            $chartData[] = [
                'month' => $monthStart->translatedFormat('M Y'),
                'revenue' => $mRev,
                'cogs' => $mCogs,
                'profit' => $mRev - $mCogs
            ];
        }

        // --- PRODUCT PROFITABILITY ---
        $productStats = [];
        if(count($posDetails) > 0) {
            foreach ($posDetails as $detail) {
                $pid = $detail->produk_id;
                if(!isset($productStats[$pid])) {
                    $productStats[$pid] = [
                        'name' => $detail->product->nama ?? 'Unknown',
                        'sku' => $detail->product->sku ?? '-',
                        'qty_sold' => 0,
                        'hpp' => $detail->product->harga_beli ?? 0,
                        'selling_price' => $detail->product->harga_jual ?? 0,
                        'revenue' => 0,
                        'cogs' => 0
                    ];
                }
                $productStats[$pid]['qty_sold'] += $detail->qty;
                $productStats[$pid]['revenue'] += $detail->subtotal;
                $productStats[$pid]['cogs'] += $detail->qty * ($detail->product->harga_beli ?? 0);
            }
            
            foreach ($productStats as &$stat) {
                $stat['gross_profit'] = $stat['revenue'] - $stat['cogs'];
                $stat['margin'] = $stat['revenue'] > 0 ? ($stat['gross_profit'] / $stat['revenue']) * 100 : 0;
            }
            usort($productStats, function($a, $b) {
                return $b['revenue'] <=> $a['revenue'];
            });
            $productStats = array_slice($productStats, 0, 10);
        }

        // --- INVOICES SUMMARY ---
        $invoiceStats = [
            'paid_amount' => 0, 'paid_count' => 0,
            'pending_amount' => 0, 'pending_count' => 0,
            'partial_amount' => 0, 'partial_count' => 0
        ];
        if(class_exists(\Modules\TagihanSupplier\Models\TagihanSupplier::class)) {
            $paidInvoices = \Modules\TagihanSupplier\Models\TagihanSupplier::where('status', 'lunas')->whereBetween('updated_at', [$startDate, $endDate])->get();
            $pendingInvoices = \Modules\TagihanSupplier\Models\TagihanSupplier::where('status', 'belum_lunas')->whereBetween('created_at', [$startDate, $endDate])->get();
            $partialInvoices = \Modules\TagihanSupplier\Models\TagihanSupplier::where('status', 'sebagian')->whereBetween('created_at', [$startDate, $endDate])->get();
            
            $invoiceStats = [
                'paid_amount' => $paidInvoices->sum('total'),
                'paid_count' => $paidInvoices->count(),
                'pending_amount' => $pendingInvoices->sum('total'), // Simplified due to no jumlah_dibayar
                'pending_count' => $pendingInvoices->count(),
                'partial_amount' => $partialInvoices->sum('total') / 2, // Dummy calculation for partial
                'partial_count' => $partialInvoices->count()
            ];
        }

        return view('report::index', compact(
            'filter', 'label', 'totalRevenue', 'transactionCount', 'cogs', 
            'grossProfit', 'grossMargin', 'inventoryValue', 'opsExpenses', 
            'netProfit', 'netMargin', 'chartData', 'productStats', 'opsItems', 'invoiceStats',
            'selectedYear', 'availableYears'
        ));
    }
}
