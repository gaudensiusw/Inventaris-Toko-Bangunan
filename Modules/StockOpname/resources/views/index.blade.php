@extends('layouts.app')

@section('title', 'Toko Bangunan - Stock Opname')
@section('header_title', 'Stock Opname')

@section('content')
<!-- Stats Row -->
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm text-center">
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Progress</p>
        <h3 class="text-xl font-bold text-[#2563eb]">0/6</h3>
        <p class="text-xs text-slate-500">(0%)</p>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm text-center">
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Sesuai</p>
        <h3 class="text-xl font-bold text-green-600">6 produk</h3>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm text-center">
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Selisih</p>
        <h3 class="text-xl font-bold text-red-500">0 produk</h3>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm text-center">
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Surplus</p>
        <h3 class="text-xl font-bold text-slate-800">0</h3>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm text-center">
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Kekurangan</p>
        <h3 class="text-xl font-bold text-slate-800">0</h3>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm text-center">
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1 line-clamp-1">Nilai Selisih</p>
        <h3 class="text-xl font-bold text-slate-800">Rp 0</h3>
    </div>
</div>

<!-- Filter Bar -->
<div class="bg-white border border-slate-200 rounded-xl shadow-sm mb-6 p-4 flex flex-wrap gap-4 items-center justify-between">
    <div class="relative w-full md:w-96">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
        <input type="text" placeholder="Cari produk (nama, SKU, barcode)..." class="block w-full pl-10 pr-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 bg-slate-50">
    </div>
    <div class="flex items-center gap-4">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" class="w-4 h-4 text-[#2563eb] rounded border-slate-300 focus:ring-blue-500">
            <span class="text-sm font-medium text-slate-700">Hanya Selisih</span>
        </label>
        <button class="bg-[#0f172a] hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
            Export
        </button>
    </div>
</div>

<!-- Opname Table -->
<div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">Produk</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">Stok Sistem</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">Stok Fisik</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">Selisih</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">Nilai Selisih</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">Status</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($opnames as $op)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="p-4 font-medium text-slate-800">{{ $op['product'] }}</td>
                    <td class="p-4 text-slate-500">{{ $op['system'] }}</td>
                    <td class="p-4">
                        <input type="number" value="{{ $op['physical'] }}" class="w-24 border border-slate-300 rounded px-2 py-1 text-sm focus:ring-blue-500 focus:border-blue-500 text-center">
                    </td>
                    <td class="p-4 font-medium {{ $op['diff'] != '0' ? 'text-red-500' : 'text-slate-500' }}">{{ $op['diff'] }}</td>
                    <td class="p-4 font-medium {{ $op['value_diff'] != 'Rp 0' ? 'text-red-500' : 'text-slate-500' }}">{{ $op['value_diff'] }}</td>
                    <td class="p-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Sesuai
                        </span>
                    </td>
                    <td class="p-4 text-center">
                        <button class="text-blue-500 hover:text-blue-700 font-bold text-xs uppercase tracking-wider">Simpan</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
