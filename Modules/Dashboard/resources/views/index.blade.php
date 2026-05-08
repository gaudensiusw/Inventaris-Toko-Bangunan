@extends('layouts.app')

@section('title', 'Toko Bangunan - Dashboard')
@section('header_title', 'Dashboard')
@section('content')
<!-- Welcome Header & Tabs -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Selamat Datang, {{ auth()->user()->name }}!</h2>
        <p class="text-sm text-slate-500 mt-1">Berikut adalah ringkasan bisnis Anda hari ini.</p>
    </div>
    <div class="inline-flex bg-white rounded-lg p-1 border border-slate-200 shadow-sm">
        <button class="px-4 py-1.5 text-sm font-medium rounded-md text-slate-500 hover:text-slate-700 transition">Hari</button>
        <button class="px-4 py-1.5 text-sm font-medium rounded-md text-slate-500 hover:text-slate-700 transition">Minggu</button>
        <button class="px-4 py-1.5 text-sm font-medium rounded-md bg-[#2563eb] text-white shadow transition">Bulan</button>
    </div>
</div>

<!-- Alert Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <!-- Piutang Jatuh Tempo -->
    <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4 shadow-sm">
        <div class="flex items-center gap-2 mb-3">
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <h3 class="font-bold text-red-700 text-sm">Tagihan Jatuh Tempo ({{ count($overdueInvoices) }})</h3>
        </div>
        <div class="space-y-2">
            @foreach($overdueInvoices as $inv)
            <div class="flex flex-wrap items-center justify-between bg-white border border-red-100 rounded p-2 text-[13px]">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-slate-800">{{ $inv->supplier->company_name }}</span>
                    <span class="text-slate-400">|</span>
                    <span class="text-slate-500">{{ $inv->no_invoice }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="font-bold text-red-600">Rp {{ number_format($inv->total, 0, ',', '.') }}</span>
                    <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded text-xs font-semibold">{{ $inv->jatuh_tempo->diffInDays(now()) }} hari terlambat</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Jatuh Tempo Segera -->
    <div class="bg-orange-50 border-l-4 border-orange-400 rounded-lg p-4 shadow-sm">
        <div class="flex items-center gap-2 mb-3">
            <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <h3 class="font-bold text-orange-700 text-sm">Jatuh Tempo Segera ({{ count($dueSoonInvoices) }})</h3>
        </div>
        <div class="space-y-2">
            @foreach($dueSoonInvoices as $inv)
            <div class="flex flex-wrap items-center justify-between bg-white border border-orange-100 rounded p-2 text-[13px]">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-slate-800">{{ $inv->supplier->company_name }}</span>
                    <span class="text-slate-400">|</span>
                    <span class="text-slate-500">{{ $inv->no_invoice }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="bg-orange-100 text-orange-700 px-2 py-0.5 rounded text-xs font-semibold">{{ $inv->jatuh_tempo->diffInDays(now()) }} hari lagi</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @if(auth()->user()->role === 'owner')
    <!-- Pendapatan -->
    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1">Pendapatan (Hari Ini)</p>
        <h3 class="text-2xl font-bold text-green-600 mb-2">Rp {{ number_format($stats['pendapatan_hari_ini'] / 1000, 1) }}K</h3>
        <p class="text-xs text-slate-500">Total penerimaan kas</p>
    </div>
    @endif
    
    <!-- Piutang -->
    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1">Piutang (Kredit)</p>
        <h3 class="text-2xl font-bold text-orange-500 mb-2">Rp {{ number_format($stats['total_piutang'] / 1000000, 1) }}M</h3>
        <p class="text-xs text-slate-500">Invoice belum lunas</p>
    </div>
    
    <!-- Laba Bersih -->
    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1">Laba Bersih (Est)</p>
        <h3 class="text-2xl font-bold text-[#2563eb] mb-2">Rp 0.0M</h3>
        <p class="text-xs text-slate-500">Margin operasional</p>
    </div>
    
    @if(auth()->user()->role === 'owner')
    <!-- Nilai Stok -->
    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1">Nilai Stok</p>
        <h3 class="text-2xl font-bold text-slate-800 mb-2">Rp {{ number_format($stats['nilai_stok'] / 1000000, 1) }}M</h3>
        <p class="text-xs text-slate-500">{{ count($stats['low_stock_products']) }} produk <span class="text-red-500 font-medium">stok rendah</span></p>
    </div>
    @endif
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
        <h3 class="text-sm font-bold text-slate-800 mb-4">Tren Penjualan & Laba</h3>
        <div class="h-64 flex items-center justify-center bg-slate-50 rounded border border-dashed border-slate-200">
            <canvas id="trendChart"></canvas>
        </div>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
        <h3 class="text-sm font-bold text-slate-800 mb-4">Pendapatan: Cash vs Kredit</h3>
        <div class="h-64 flex items-center justify-center bg-slate-50 rounded border border-dashed border-slate-200">
            <canvas id="splitChart"></canvas>
        </div>
    </div>
</div>

<!-- Low Stock Banner -->
<div class="bg-red-50 rounded-xl border border-red-200 overflow-hidden shadow-sm">
    <div class="bg-red-500 px-4 py-2 flex items-center gap-2">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <span class="text-sm font-bold text-white tracking-wide">Peringatan Stok Rendah &mdash; {{ count($stats['low_stock_products']) }} produk di bawah stok minimum</span>
    </div>
    <div class="p-4 space-y-2">
        @foreach($stats['low_stock_products'] as $product)
        <div class="flex flex-wrap items-center justify-between bg-white rounded border border-red-100 p-3">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-slate-100 rounded flex items-center justify-center">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-slate-800">{{ $product->nama }}</h4>
                    <p class="text-xs text-slate-500 mt-0.5">SKU: {{ $product->sku ?? '-' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-slate-600 border-r border-slate-200 pr-4">{{ $product->unit }}</span>
                <span class="inline-flex py-1 px-3 rounded text-xs font-bold bg-red-500 text-white">{{ $product->stok }} / {{ $product->min_stok }}</span>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Stub out charts for visuals
    const ctx = document.getElementById('trendChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['1', '5', '10', '15', '20', '25', '30'],
            datasets: [{
                label: 'Penjualan',
                data: [12, 19, 3, 5, 2, 3, 10],
                borderColor: '#cfcfcf',
                tension: 0.4
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    const ctx2 = document.getElementById('splitChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Cash', 'Kredit'],
            datasets: [{
                data: [1, 5],
                backgroundColor: ['#cfcfcf', '#cbd5e1']
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });
</script>
@endpush
