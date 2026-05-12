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

    public function chartData(Request $request)
    {
        $filter = $request->query('filter', 'bulan'); // hari, minggu, bulan, tahun
        $now = \Carbon\Carbon::now();
        
        $labels = [];
        $revenueData = [];
        $profitData = [];

        // Determine date range and labels based on filter
        switch($filter) {
            case 'hari':
                $startDate = $now->copy()->startOfDay();
                $endDate = $now->copy()->endOfDay();
                for ($i = 0; $i <= 23; $i++) {
                    $labels[] = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
                    $revenueData[$i] = 0;
                    $profitData[$i] = 0;
                }
                $groupByFormat = 'H';
                break;
            case 'minggu':
                $startDate = $now->copy()->startOfWeek();
                $endDate = $now->copy()->endOfWeek();
                for ($i = 0; $i < 7; $i++) {
                    $day = $startDate->copy()->addDays($i);
                    $labels[] = $day->translatedFormat('D'); // Sen, Sel, etc.
                    $revenueData[$day->format('Y-m-d')] = 0;
                    $profitData[$day->format('Y-m-d')] = 0;
                }
                $groupByFormat = 'Y-m-d';
                break;
            case 'tahun':
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfYear();
                for ($i = 1; $i <= 12; $i++) {
                    $month = \Carbon\Carbon::createFromDate($now->year, $i, 1);
                    $labels[] = $month->translatedFormat('M'); // Jan, Feb, etc.
                    $revenueData[$month->format('Y-m')] = 0;
                    $profitData[$month->format('Y-m')] = 0;
                }
                $groupByFormat = 'Y-m';
                break;
            case 'bulan':
            default:
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                $daysInMonth = $now->daysInMonth;
                for ($i = 1; $i <= $daysInMonth; $i++) {
                    $labels[] = $i;
                    $dateKey = $now->copy()->setDay($i)->format('Y-m-d');
                    $revenueData[$dateKey] = 0;
                    $profitData[$dateKey] = 0;
                }
                $groupByFormat = 'Y-m-d';
                break;
        }

        // Get POS data
        if(class_exists(\Modules\POS\Models\POS::class) && class_exists(\Modules\POS\Models\POSDetail::class)) {
            $posList = \Modules\POS\Models\POS::with('details.product')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            foreach ($posList as $pos) {
                // Determine the group key
                if ($filter == 'hari') {
                    $key = (int)$pos->created_at->format('H');
                } else {
                    $key = $pos->created_at->format($groupByFormat);
                }

                if (isset($revenueData[$key])) {
                    $revenueData[$key] += $pos->total_tagihan;

                    // Calculate COGS
                    $cogs = $pos->details->sum(function($detail) {
                        return $detail->qty * ($detail->product->harga_beli ?? 0);
                    });
                    $profitData[$key] += ($pos->total_tagihan - $cogs);
                }
            }
        }

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Pendapatan',
                    'data' => array_values($revenueData),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)', // blue-500
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'tension' => 0.4,
                    'fill' => true
                ],
                [
                    'label' => 'Laba Kotor',
                    'data' => array_values($profitData),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)', // emerald-500
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 2,
                    'tension' => 0.4,
                    'fill' => true
                ]
            ]
        ]);
    }
}
