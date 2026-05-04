@extends('layouts.app')

@section('title', 'Toko Bangunan - Stock Opname')
@section('header_title', 'Stock Opname')

@section('content')
<!-- Alert Messages -->
@if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span class="text-sm font-medium">{{ session('success') }}</span>
    </div>
@endif
@if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center gap-2">
        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <span class="text-sm font-medium">{{ session('error') }}</span>
    </div>
@endif

<!-- Stats Row -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm text-center">
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Total Produk</p>
        <h3 class="text-xl font-bold text-[#2563eb]">{{ $products->count() }}</h3>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm text-center text-green-600">
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Tersedia</p>
        <h3 class="text-xl font-bold">{{ $products->where('stok', '>', 0)->count() }}</h3>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm text-center text-red-500">
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Stok Habis</p>
        <h3 class="text-xl font-bold">{{ $products->where('stok', '<=', 0)->count() }}</h3>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm text-center text-orange-500">
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Menipis</p>
        <h3 class="text-xl font-bold">{{ $products->where('stok', '<=', 5)->where('stok', '>', 0)->count() }}</h3>
    </div>
</div>

<!-- Opname Section -->
<div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mb-6" x-data="{ 
    selectedProduct: null,
    stokFisik: 0,
    stokSistem: 0,
    selisih: 0,
    updateProduct(id, currentStok) {
        this.selectedProduct = id;
        this.stokSistem = currentStok;
        this.stokFisik = currentStok;
        this.calculate();
    },
    calculate() {
        this.selisih = this.stokFisik - this.stokSistem;
    }
}">
    <div class="p-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
        <h3 class="text-base font-bold text-slate-800">Catat Stock Opname</h3>
    </div>
    <div class="p-6">
        <form action="{{ route('stockopname.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            @csrf
            <div class="md:col-span-1">
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Pilih Produk</label>
                <select name="produk_id" required class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500" 
                        @change="let opt = $event.target.selectedOptions[0]; updateProduct(opt.value, opt.dataset.stok)">
                    <option value="">Pilih Produk...</option>
                    @foreach($products as $p)
                    <option value="{{ $p->id }}" data-stok="{{ $p->stok }}">{{ $p->nama }} (SKU: {{ $p->sku ?: '-' }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Stok Sistem</label>
                <input type="text" x-model="stokSistem" readonly class="w-full border border-slate-200 rounded-lg text-sm p-2.5 bg-slate-50 text-slate-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Stok Fisik <span class="text-red-500">*</span></label>
                <input type="number" name="stok_fisik" x-model="stokFisik" @input="calculate()" required class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <button type="submit" :disabled="!selectedProduct" class="w-full bg-[#0f172a] hover:bg-slate-800 text-white px-4 py-2.5 rounded-lg text-sm font-bold transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
                    Simpan Opname
                </button>
            </div>
            <div class="md:col-span-3">
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Keterangan / Alasan Selisih</label>
                <input type="text" name="keterangan" placeholder="Contoh: Barang rusak, salah hitung sebelumnya, dll" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="text-center">
                <div class="px-4 py-2 rounded-lg text-xs font-bold border uppercase tracking-widest" 
                     :class="selisih == 0 ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'">
                    Selisih: <span x-text="selisih"></span>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- List Produk Section -->
<div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
    <div class="p-4 border-b border-slate-200 bg-slate-50">
        <h3 class="text-base font-bold text-slate-800">Daftar Stok Produk Saat Ini</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">Produk</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs text-center">Stok Saat Ini</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">Satuan</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">Kategori</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($products as $p)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="p-4">
                        <div class="font-bold text-slate-800">{{ $p->nama }}</div>
                        <div class="text-[10px] text-slate-500">SKU: {{ $p->sku ?: '-' }}</div>
                    </td>
                    <td class="p-4 text-center font-black text-slate-800">{{ number_format($p->stok, 0) }}</td>
                    <td class="p-4 text-slate-500">{{ $p->unit }}</td>
                    <td class="p-4 text-slate-700 font-medium">{{ $p->category->nama ?? '-' }}</td>
                    <td class="p-4">
                        @if($p->stok <= 0)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 uppercase border border-red-200">Habis</span>
                        @elseif($p->stok <= 5)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-orange-100 text-orange-700 uppercase border border-orange-200">Menipis</span>
                        @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700 uppercase border border-green-200">Aman</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
