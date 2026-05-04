@extends('layouts.app')

@section('title', 'Dashboard - Toko Bangunan IMS')
@section('header_title', 'Selamat Datang, ' . (auth()->user()->name ?? 'Admin') . '!')

@section('content')
<div class="space-y-6">
    <div>
        <p class="text-slate-600">Ringkasan inventori dan operasional toko hari ini.</p>
    </div>

    <!-- Inventory Stats -->
    <h3 class="text-lg font-bold text-slate-800">Inventori & Produk</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Produk -->
        <div class="rounded-xl border border-slate-200 bg-white text-slate-950 shadow-sm">
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium">Total Produk</h3>
                <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
            <div class="p-6 pt-0">
                <div class="text-2xl font-bold text-blue-600">{{ $stats['total_produk'] }}</div>
                <p class="text-xs text-slate-500 mt-1">Item tersedia di katalog</p>
            </div>
        </div>

        <!-- Stok Rendah -->
        <a href="{{ route('notification.index', ['category' => 'stok_rendah']) }}" class="rounded-xl border border-slate-200 bg-white text-slate-950 shadow-sm hover:border-red-300 transition-all group">
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium">Stok Rendah</h3>
                <svg class="h-4 w-4 text-red-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <div class="p-6 pt-0">
                <div class="text-2xl font-bold text-red-600">{{ $stats['stok_rendah'] }}</div>
                <p class="text-xs text-slate-500 mt-1">Perlu segera restock</p>
            </div>
        </a>

        <!-- Nilai Stok -->
        <div class="rounded-xl border border-slate-200 bg-white text-slate-950 shadow-sm">
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium">Estimasi Nilai Stok</h3>
                <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="p-6 pt-0">
                <div class="text-2xl font-bold text-green-600">Rp {{ number_format($stats['nilai_stok'] / 1000000, 1) }}M</div>
                <p class="text-xs text-slate-500 mt-1">Berdasarkan harga beli</p>
            </div>
        </div>

        <!-- Aset Operasional -->
        <div class="rounded-xl border border-slate-200 bg-white text-slate-950 shadow-sm">
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium">Aset Operasional</h3>
                <svg class="h-4 w-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            </div>
            <div class="p-6 pt-0">
                <div class="text-2xl font-bold text-purple-600">{{ $stats['total_aset_operasional'] }}</div>
                <p class="text-xs text-slate-500 mt-1">Item operasional tercatat</p>
            </div>
        </div>
    </div>

    <!-- Finance & Stakeholders -->
    <h3 class="text-lg font-bold text-slate-800 mt-8">Keuangan & Relasi</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Hutang -->
        <a href="{{ route('notification.index', ['category' => 'tagihan']) }}" class="rounded-xl border border-slate-200 bg-white text-slate-950 shadow-sm hover:border-orange-300 transition-all group">
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium">Total Hutang Supplier</h3>
                <svg class="h-4 w-4 text-orange-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
            </div>
            <div class="p-6 pt-0">
                <div class="text-2xl font-bold text-orange-600">Rp {{ number_format($stats['total_hutang'] / 1000000, 1) }}M</div>
                <p class="text-xs text-slate-500 mt-1">{{ $stats['hutang_jatuh_tempo'] }} jatuh tempo segera</p>
            </div>
        </a>

        <!-- Total Customer -->
        <div class="rounded-xl border border-slate-200 bg-white text-slate-950 shadow-sm">
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium">Total Customer</h3>
                <svg class="h-4 w-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <div class="p-6 pt-0">
                <div class="text-2xl font-bold text-blue-500">{{ $stats['total_customer'] }}</div>
                <p class="text-xs text-slate-500 mt-1">Pelanggan terdaftar</p>
            </div>
        </div>

        <!-- Total Supplier -->
        <div class="rounded-xl border border-slate-200 bg-white text-slate-950 shadow-sm">
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium">Total Supplier</h3>
                <svg class="h-4 w-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <div class="p-6 pt-0">
                <div class="text-2xl font-bold text-slate-600">{{ $stats['total_supplier'] }}</div>
                <p class="text-xs text-slate-500 mt-1">Mitra pemasok aktif</p>
            </div>
        </div>

        <!-- Shortcut Card -->
        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 text-slate-950 flex items-center justify-center p-6">
            <a href="{{ route('product.index') }}" class="text-sm font-bold text-slate-600 hover:text-[#2563eb] transition-colors flex items-center gap-2">
                Lihat Katalog Produk
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </a>
        </div>
    </div>
</div>
@endsection
