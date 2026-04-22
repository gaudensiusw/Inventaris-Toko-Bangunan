@extends('layouts.app')

@section('title', 'Toko Bangunan - POS / Cashier')
@section('header_title', 'POS / Cashier')

@section('content')
<div class="flex flex-col lg:flex-row gap-6 h-[calc(100vh-140px)]">
    <!-- Left panel: Product grid -->
    <div class="flex-1 flex flex-col min-h-0 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <!-- Search & Filter Bar -->
        <div class="p-4 border-b border-slate-200 flex flex-wrap items-center gap-3 bg-slate-50">
            <div class="flex-1 min-w-[200px] relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" placeholder="Cari Barang..." class="block w-full pl-10 pr-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 bg-white placeholder-slate-400">
            </div>
            <select class="border border-slate-300 rounded-lg text-sm py-2 px-3 bg-white text-slate-700 focus:ring-blue-500 focus:border-blue-500 min-w-[150px]">
                <option>Semua Kategori</option>
                <option>Material Dasar</option>
                <option>Pipa & Plumbing</option>
                <option>Cat & Pelapis</option>
            </select>
            <button class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                Scan Barang
            </button>
        </div>

        <!-- Grid -->
        <div class="flex-1 overflow-y-auto p-4 custom-scrollbar bg-slate-50">
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($products as $product)
                <div class="bg-white border border-slate-200 rounded-xl overflow-hidden hover:shadow-md transition-shadow flex flex-col h-full group">
                    <div class="h-32 bg-slate-100 flex items-center justify-center border-b border-slate-100 group-hover:bg-slate-200 transition-colors">
                        <span class="text-slate-400 text-xs font-medium uppercase tracking-widest flex flex-col items-center gap-2">
                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            No Image
                        </span>
                    </div>
                    <div class="p-3 flex-1 flex flex-col">
                        <h3 class="text-sm font-bold text-slate-800 line-clamp-2 leading-tight mb-1" title="{{ $product['name'] }}">{{ $product['name'] }}</h3>
                        <p class="text-[11px] text-slate-500 mb-2">Stok: <span class="font-bold text-slate-700">{{ $product['stock'] }}</span> {{ $product['unit'] }}</p>
                        <div class="mt-auto">
                            <p class="text-[10px] text-slate-400 line-through">{{ $product['original_price'] }}/{{ $product['unit'] }}</p>
                            <div class="flex items-center justify-between mt-0.5">
                                <p class="text-sm font-bold text-[#2563eb]">{{ $product['price'] }}</p>
                            </div>
                        </div>
                        <button class="mt-3 w-full bg-green-500 hover:bg-green-600 text-white py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-1 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Add
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Right panel: Order summary -->
    <div class="w-full lg:w-[400px] xl:w-[450px] flex flex-col min-h-0 bg-white border border-slate-200 rounded-xl shadow-sm flex-shrink-0">
        <!-- Order Header -->
        <div class="p-4 border-b border-slate-200 bg-slate-50 space-y-3">
            <div class="flex items-center justify-between">
                <label class="text-xs font-bold text-slate-500 uppercase">Pelanggan:</label>
                <div class="flex gap-2">
                    <input type="text" placeholder="Pilih/Ketik..." class="border border-slate-300 rounded text-sm py-1 px-2 w-32 focus:ring-blue-500 focus:border-blue-500">
                    <button class="bg-pink-500 hover:bg-pink-600 text-white text-xs font-bold px-2 py-1 rounded transition-colors">Belum Semua</button>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <label class="text-xs font-bold text-slate-500 uppercase">Tanggal Transaksi:</label>
                <input type="text" value="17 Mar 2026" class="border border-slate-300 rounded text-sm py-1 px-2 w-32 text-center bg-slate-100 text-slate-700 font-medium" readonly>
            </div>
        </div>

        <!-- Order Items -->
        <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500">
                        <th class="pb-2 font-semibold">No</th>
                        <th class="pb-2 font-semibold">Nama Barang</th>
                        <th class="pb-2 font-semibold text-center">Jml</th>
                        <th class="pb-2 font-semibold text-right">Harga</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Empty State -->
                    <tr>
                        <td colspan="4" class="py-8 text-center text-slate-400">
                            <div class="flex flex-col items-center">
                                <svg class="w-10 h-10 mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                <p class="text-sm">Belum ada barang di keranjang</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Order Options -->
        <div class="p-4 border-t border-slate-200 bg-slate-50 space-y-4">
            <div>
                <textarea placeholder="Catatan Pesanan..." class="w-full border border-slate-300 rounded-lg text-sm p-2 focus:ring-blue-500 focus:border-blue-500 resize-none h-16"></textarea>
            </div>
            
            <div class="flex items-center justify-between">
                <label class="text-xs font-bold text-slate-500 uppercase">Opsi Pengiriman:</label>
                <div class="flex bg-slate-200 rounded-lg p-1">
                    <button class="px-4 py-1 text-xs font-bold rounded-md bg-slate-800 text-white shadow">Ambil Sendiri</button>
                    <button class="px-4 py-1 text-xs font-bold rounded-md text-slate-600 hover:text-slate-800">Antar</button>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Metode Pembayaran:</label>
                <div class="grid grid-cols-4 gap-2">
                    <button class="py-2 text-xs font-bold rounded-lg border border-slate-800 bg-slate-800 text-white">Cash</button>
                    <button class="py-2 text-xs font-bold rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100">Card</button>
                    <button class="py-2 text-xs font-bold rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100">E-Wallet</button>
                    <button class="py-2 text-xs font-bold rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100">Bon</button>
                </div>
            </div>

            <!-- Totals -->
            <div class="pt-2 border-t border-slate-200 border-dashed">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-sm text-slate-500 font-medium">Subtotal</span>
                    <span class="text-sm font-bold text-slate-800">Rp 0</span>
                </div>
                <div class="flex justify-between items-center mb-3">
                    <span class="text-sm text-slate-500 font-medium">Pajak (0%)</span>
                    <span class="text-sm font-bold text-slate-800">Rp 0</span>
                </div>
                <div class="flex justify-between items-end">
                    <span class="text-lg font-bold text-slate-800">Total Tagihan</span>
                    <span class="text-2xl font-black text-[#2563eb]">Rp 0</span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="p-4 bg-white border-t border-slate-200 grid grid-cols-3 gap-2">
            <button class="py-3 px-2 rounded-lg text-xs font-bold uppercase tracking-wider bg-pink-50 text-pink-600 border border-pink-200 hover:bg-pink-100 transition-colors text-center">
                Hold Order
            </button>
            <button class="py-3 px-2 rounded-lg text-xs font-bold uppercase tracking-wider bg-white text-slate-600 border border-slate-300 hover:bg-slate-50 transition-colors text-center">
                Print Quote
            </button>
            <button class="py-3 px-2 rounded-lg text-xs font-bold uppercase tracking-wider bg-green-500 text-white shadow hover:bg-green-600 transition-colors text-center">
                Checkout
            </button>
        </div>
    </div>
</div>
@endsection
