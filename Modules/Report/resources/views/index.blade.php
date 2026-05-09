@extends('layouts.app')

@section('title', 'Toko Bangunan - Financial Reports')
@section('header_title', 'Financial Reports')

@section('content')
<div class="max-w-7xl mx-auto space-y-6" id="printableArea">
    
    <!-- Top Header & Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight">Financial Reports</h2>
            <p class="text-sm text-slate-500 font-medium">Comprehensive financial analysis and insights &mdash; <span class="text-blue-600 font-bold">{{ $label }}</span></p>
        </div>
        <div class="flex items-center gap-2 no-print">
            <button onclick="window.print()" class="bg-slate-800 text-white hover:bg-slate-900 px-4 py-2 rounded-lg text-sm font-bold shadow-md transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Export PDF
            </button>
            <button class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Export CSV
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 no-print">
        <div class="bg-white p-2 rounded-xl shadow-sm border border-slate-200 inline-flex">
            <a href="?filter=hari" class="px-4 py-1.5 text-sm font-bold rounded-lg transition-colors {{ $filter == 'hari' ? 'bg-slate-800 text-white' : 'text-slate-500 hover:bg-slate-100' }}">Hari</a>
            <a href="?filter=minggu" class="px-4 py-1.5 text-sm font-bold rounded-lg transition-colors {{ $filter == 'minggu' ? 'bg-slate-800 text-white' : 'text-slate-500 hover:bg-slate-100' }}">Minggu</a>
            <a href="?filter=bulan" class="px-4 py-1.5 text-sm font-bold rounded-lg transition-colors {{ $filter == 'bulan' ? 'bg-slate-800 text-white' : 'text-slate-500 hover:bg-slate-100' }}">Bulan</a>
            <a href="?filter=tahun&year={{ $selectedYear }}" class="px-4 py-1.5 text-sm font-bold rounded-lg transition-colors {{ $filter == 'tahun' ? 'bg-slate-800 text-white' : 'text-slate-500 hover:bg-slate-100' }}">Tahun</a>
        </div>

        @if($filter == 'tahun')
        <div class="bg-white p-2 rounded-xl shadow-sm border border-slate-200 inline-flex items-center">
            <span class="text-xs font-bold text-slate-500 mr-2 ml-2">Pilih Tahun:</span>
            <select onchange="window.location.href='?filter=tahun&year='+this.value" class="text-sm font-bold text-slate-800 border-none bg-slate-50 rounded-lg focus:ring-0 cursor-pointer px-3 py-1.5">
                @foreach($availableYears as $y)
                    <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        @endif
    </div>

    <!-- KPI Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 page-break-inside-avoid">
        <!-- Total Revenue -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <p class="text-xs font-black text-slate-500 uppercase tracking-widest">Total Revenue</p>
                <span class="text-green-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></span>
            </div>
            <h3 class="text-3xl font-black text-slate-800 mb-1">Rp {{ number_format($totalRevenue / 1000000, 2) }}M</h3>
            <p class="text-xs font-bold text-slate-400">Dari {{ $transactionCount }} transaksi</p>
        </div>

        <!-- COGS -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <p class="text-xs font-black text-slate-500 uppercase tracking-widest">COGS / HPP</p>
                <span class="text-red-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg></span>
            </div>
            <h3 class="text-3xl font-black text-slate-800 mb-1">Rp {{ number_format($cogs / 1000000, 2) }}M</h3>
            <p class="text-xs font-bold text-slate-400">Harga Pokok Penjualan</p>
        </div>

        <!-- Gross Profit -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <p class="text-xs font-black text-slate-500 uppercase tracking-widest">Gross Profit</p>
                <span class="text-blue-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg></span>
            </div>
            <h3 class="text-3xl font-black text-slate-800 mb-1">Rp {{ number_format($grossProfit / 1000000, 2) }}M</h3>
            <p class="text-xs font-bold text-slate-400">{{ number_format($grossMargin, 1) }}% margin kotor</p>
        </div>

        <!-- Inventory Value -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <p class="text-xs font-black text-slate-500 uppercase tracking-widest">Inventory Value</p>
                <span class="text-purple-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg></span>
            </div>
            <h3 class="text-3xl font-black text-slate-800 mb-1">Rp {{ number_format($inventoryValue / 1000000, 2) }}M</h3>
            <p class="text-xs font-bold text-slate-400">Nilai aset barang saat ini</p>
        </div>

        <!-- Operational Expenses -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <p class="text-xs font-black text-slate-500 uppercase tracking-widest">Operational Expenses</p>
                <span class="text-orange-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg></span>
            </div>
            <h3 class="text-3xl font-black text-red-500 mb-1">Rp {{ number_format($opsExpenses / 1000000, 2) }}M</h3>
            <p class="text-xs font-bold text-slate-400">Biaya operasional & lain-lain</p>
        </div>

        <!-- Net Profit -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm border-b-4 border-b-green-500">
            <div class="flex justify-between items-start mb-4">
                <p class="text-xs font-black text-slate-500 uppercase tracking-widest">Net Profit</p>
                <span class="text-green-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></span>
            </div>
            <h3 class="text-3xl font-black text-green-600 mb-1">Rp {{ number_format($netProfit / 1000000, 2) }}M</h3>
            <p class="text-xs font-bold text-slate-400">{{ number_format($netMargin, 1) }}% net margin (Laba Bersih)</p>
        </div>
    </div>

    <!-- Chart Row -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 page-break-inside-avoid">
        <h3 class="text-sm font-black text-slate-800 mb-1 uppercase tracking-widest">Profit & Loss Trend</h3>
        <p class="text-xs text-slate-500 mb-6 font-bold">Monthly revenue, COGS, and profit analysis (Last 12 months)</p>
        <div class="h-[300px] w-full relative">
            <canvas id="financialChart"></canvas>
        </div>
    </div>

    <!-- Product Profitability Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden page-break-inside-avoid">
        <div class="p-6 border-b border-slate-100">
            <h3 class="text-sm font-black text-slate-800 mb-1 uppercase tracking-widest">Product Profitability Analysis</h3>
            <p class="text-xs text-slate-500 font-bold">HPP (COGS) and profit margin by top products</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="py-4 px-6 font-bold text-[11px] uppercase tracking-wider">Product</th>
                        <th class="py-4 px-6 font-bold text-[11px] uppercase tracking-wider">SKU</th>
                        <th class="py-4 px-6 font-bold text-[11px] uppercase tracking-wider text-right">Qty Sold</th>
                        <th class="py-4 px-6 font-bold text-[11px] uppercase tracking-wider text-right">HPP</th>
                        <th class="py-4 px-6 font-bold text-[11px] uppercase tracking-wider text-right">Selling Price</th>
                        <th class="py-4 px-6 font-bold text-[11px] uppercase tracking-wider text-right">Revenue</th>
                        <th class="py-4 px-6 font-bold text-[11px] uppercase tracking-wider text-right">COGS</th>
                        <th class="py-4 px-6 font-bold text-[11px] uppercase tracking-wider text-right">Gross Profit</th>
                        <th class="py-4 px-6 font-bold text-[11px] uppercase tracking-wider text-center">Margin %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($productStats as $prod)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-3 px-6 font-bold text-slate-800">{{ $prod['name'] }}</td>
                        <td class="py-3 px-6 font-mono text-xs text-slate-500">{{ $prod['sku'] }}</td>
                        <td class="py-3 px-6 text-right font-medium text-slate-700">{{ $prod['qty_sold'] }}</td>
                        <td class="py-3 px-6 text-right font-mono text-xs text-slate-500">Rp {{ number_format($prod['hpp'], 0, ',', '.') }}</td>
                        <td class="py-3 px-6 text-right font-mono text-xs text-slate-500">Rp {{ number_format($prod['selling_price'], 0, ',', '.') }}</td>
                        <td class="py-3 px-6 text-right font-bold text-blue-600">Rp {{ number_format($prod['revenue'], 0, ',', '.') }}</td>
                        <td class="py-3 px-6 text-right font-bold text-red-500">Rp {{ number_format($prod['cogs'], 0, ',', '.') }}</td>
                        <td class="py-3 px-6 text-right font-bold text-green-600">Rp {{ number_format($prod['gross_profit'], 0, ',', '.') }}</td>
                        <td class="py-3 px-6 text-center">
                            <span class="bg-slate-800 text-white text-[10px] font-black px-2.5 py-1 rounded-full">{{ number_format($prod['margin'], 1) }}%</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-8 text-center text-slate-400 font-medium">Tidak ada data penjualan pada periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Operational Expenses Breakdown -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden page-break-inside-avoid">
        <div class="p-6 border-b border-slate-100">
            <h3 class="text-sm font-black text-slate-800 mb-1 uppercase tracking-widest">Operational Expenses Breakdown</h3>
            <p class="text-xs text-slate-500 font-bold">Biaya operasional toko (kantong kresek, struk, dll)</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="py-4 px-6 font-bold text-[11px] uppercase tracking-wider">Date</th>
                        <th class="py-4 px-6 font-bold text-[11px] uppercase tracking-wider">Item Name</th>
                        <th class="py-4 px-6 font-bold text-[11px] uppercase tracking-wider">Type</th>
                        <th class="py-4 px-6 font-bold text-[11px] uppercase tracking-wider text-right">Quantity</th>
                        <th class="py-4 px-6 font-bold text-[11px] uppercase tracking-wider text-right">Unit Price</th>
                        <th class="py-4 px-6 font-bold text-[11px] uppercase tracking-wider text-right">Total Amount</th>
                        <th class="py-4 px-6 font-bold text-[11px] uppercase tracking-wider">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($opsItems as $item)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-3 px-6 font-mono text-xs text-slate-500">{{ \Carbon\Carbon::parse($item->tanggal_pembelian ?? $item->created_at)->translatedFormat('d M Y') }}</td>
                        <td class="py-3 px-6 font-bold text-slate-800">{{ $item->nama }}</td>
                        <td class="py-3 px-6">
                            <span class="bg-slate-800 text-white text-[10px] font-black px-2.5 py-1 rounded-full">{{ $item->kategori ?? 'Operasional' }}</span>
                        </td>
                        <td class="py-3 px-6 text-right font-medium text-slate-700">{{ $item->jumlah }} {{ $item->satuan }}</td>
                        <td class="py-3 px-6 text-right font-mono text-xs text-slate-500">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                        <td class="py-3 px-6 text-right font-bold text-red-500">Rp {{ number_format($item->jumlah * $item->harga, 0, ',', '.') }}</td>
                        <td class="py-3 px-6 text-xs text-slate-500 italic">{{ $item->deskripsi ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400 font-medium">Tidak ada data operasional pada periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-50 border-t border-slate-200">
                    <tr>
                        <td colspan="5" class="py-4 px-6 text-right font-black text-slate-600 text-sm">Total Operational Expenses:</td>
                        <td class="py-4 px-6 text-right font-black text-red-600 text-lg">Rp {{ number_format($opsExpenses, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Bottom Invoices Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 page-break-inside-avoid">
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm border-b-4 border-b-green-500">
            <p class="text-xs font-black text-slate-500 uppercase tracking-widest mb-4">Paid Invoices</p>
            <h3 class="text-2xl font-black text-green-600 mb-1">Rp {{ number_format($invoiceStats['paid_amount'] / 1000000, 2) }}M</h3>
            <p class="text-xs font-bold text-slate-400">{{ $invoiceStats['paid_count'] }} invoices lunas</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm border-b-4 border-b-orange-500">
            <p class="text-xs font-black text-slate-500 uppercase tracking-widest mb-4">Pending Payments</p>
            <h3 class="text-2xl font-black text-orange-500 mb-1">Rp {{ number_format($invoiceStats['pending_amount'] / 1000000, 2) }}M</h3>
            <p class="text-xs font-bold text-slate-400">{{ $invoiceStats['pending_count'] }} invoices belum dibayar</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm border-b-4 border-b-blue-500">
            <p class="text-xs font-black text-slate-500 uppercase tracking-widest mb-4">Partial Payments</p>
            <h3 class="text-2xl font-black text-blue-600 mb-1">Rp {{ number_format($invoiceStats['partial_amount'] / 1000000, 2) }}M</h3>
            <p class="text-xs font-bold text-slate-400">{{ $invoiceStats['partial_count'] }} invoices cicilan</p>
        </div>
    </div>

</div>

<!-- CSS for Print -->
<style>
    @media print {
        body {
            background-color: white !important;
            color: black !important;
        }
        .no-print {
            display: none !important;
        }
        #printableArea {
            width: 100%;
            margin: 0;
            padding: 0;
            max-width: 100%;
        }
        .bg-white {
            box-shadow: none !important;
            border: 1px solid #e2e8f0 !important;
        }
        .page-break-inside-avoid {
            page-break-inside: avoid;
            margin-bottom: 20px;
        }
        /* Ensure charts print properly */
        canvas {
            max-width: 100% !important;
            height: auto !important;
        }
    }
</style>

<!-- Chart.js Injection -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const rawData = @json($chartData);
        
        // Reverse because we looped from 11 to 0 (oldest to newest)
        const labels = rawData.map(d => d.month).reverse();
        const revData = rawData.map(d => d.revenue / 1000000).reverse();
        const cogsData = rawData.map(d => d.cogs / 1000000).reverse();
        const profitData = rawData.map(d => d.profit / 1000000).reverse();

        const ctx = document.getElementById('financialChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Revenue (M)',
                        data: revData,
                        borderColor: '#22c55e', // green-500
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'COGS (M)',
                        data: cogsData,
                        borderColor: '#ef4444', // red-500
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        tension: 0.4,
                        borderDash: [5, 5]
                    },
                    {
                        label: 'Net Profit (M)',
                        data: profitData,
                        borderColor: '#3b82f6', // blue-500
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                family: 'ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial',
                                weight: 'bold',
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += 'Rp ' + context.parsed.y.toFixed(2) + 'M';
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [2, 2],
                            color: '#f1f5f9' // slate-100
                        },
                        ticks: {
                            callback: function(value) {
                                return value + 'M';
                            },
                            font: {
                                weight: 'bold'
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                weight: 'bold'
                            }
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
    });
</script>
@endsection
