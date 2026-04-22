@extends('layouts.app')

@section('title', 'Toko Bangunan - Barang Operasional')
@section('header_title', 'Barang Operasional')

@section('content')
<div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
    <div class="p-5 border-b border-slate-200 flex justify-between items-center bg-slate-50/50">
        <h3 class="text-lg font-bold text-slate-800">Inventaris Operasional</h3>
        <button class="bg-[#0f172a] hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition-colors">
            + Tambah Inventaris
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs">Nama Barang</th>
                    <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs">Kategori</th>
                    <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs">Jumlah</th>
                    <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs">Kondisi</th>
                    <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs">Cek Terakhir</th>
                    <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($items as $item)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="py-4 px-5">
                        <div class="font-bold text-slate-800">{{ $item['name'] }}</div>
                    </td>
                    <td class="py-4 px-5 text-slate-700 font-medium">{{ $item['category'] }}</td>
                    <td class="py-4 px-5 font-bold text-slate-800">{{ $item['stock'] }} unit</td>
                    <td class="py-4 px-5">
                        @if($item['condition'] == 'Baik')
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-[10px] font-bold uppercase tracking-wider border border-green-200">Sangat Baik</span>
                        @else
                        <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-[10px] font-bold uppercase tracking-wider border border-red-200">Butuh Perbaikan</span>
                        @endif
                    </td>
                    <td class="py-4 px-5 text-slate-500">{{ $item['last_check'] }}</td>
                    <td class="py-4 px-5 text-right">
                        <button class="text-blue-600 hover:text-blue-800 font-bold text-xs uppercase tracking-wider">Update Status</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
