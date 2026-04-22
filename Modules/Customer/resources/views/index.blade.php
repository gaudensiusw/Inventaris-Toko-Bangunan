@extends('layouts.app')

@section('title', 'Toko Bangunan - Pelanggan')
@section('header_title', 'Pelanggan')

@section('content')
<div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
    <div class="p-5 border-b border-slate-200 flex justify-between items-center bg-slate-50/50">
        <h3 class="text-lg font-bold text-slate-800">Daftar Pelanggan</h3>
        <button class="bg-[#0f172a] hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition-colors">
            + Tambah Pelanggan
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs">Nama</th>
                    <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs">Kategori</th>
                    <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs">Telepon</th>
                    <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs">Total Order</th>
                    <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs">Loyalty</th>
                    <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($customers as $cust)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="py-4 px-5">
                        <div class="font-bold text-slate-800">{{ $cust['name'] }}</div>
                    </td>
                    <td class="py-4 px-5 text-slate-700 font-medium">{{ $cust['category'] }}</td>
                    <td class="py-4 px-5 text-slate-600">{{ $cust['phone'] }}</td>
                    <td class="py-4 px-5 font-bold text-slate-800">{{ $cust['total_order'] }}x</td>
                    <td class="py-4 px-5">
                        @php
                            $colors = [
                                'Platinum' => 'bg-slate-900 text-white',
                                'Gold' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                'Silver' => 'bg-slate-100 text-slate-800 border-slate-200',
                                'Bronze' => 'bg-orange-100 text-orange-800 border-orange-200',
                            ];
                            $color = $colors[$cust['loyalty']] ?? 'bg-slate-50 text-slate-600';
                        @endphp
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black border uppercase tracking-widest {{ $color }}">
                            {{ $cust['loyalty'] }}
                        </span>
                    </td>
                    <td class="py-4 px-5 text-right">
                        <button class="text-blue-600 hover:text-blue-800 font-bold text-xs uppercase tracking-wider">Detail</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
