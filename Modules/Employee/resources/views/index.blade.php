@extends('layouts.app')

@section('title', 'Toko Bangunan - Karyawan')
@section('header_title', 'Karyawan')

@section('content')
<div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
    <div class="p-5 border-b border-slate-200 flex justify-between items-center bg-slate-50/50">
        <h3 class="text-lg font-bold text-slate-800">Daftar Karyawan</h3>
        <button class="bg-[#0f172a] hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition-colors">
            + Tambah Karyawan
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs">Nama</th>
                    <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs">Jabatan</th>
                    <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs">Telepon</th>
                    <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs">Status</th>
                    <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($employees as $emp)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="py-4 px-5">
                        <div class="font-bold text-slate-800">{{ $emp['name'] }}</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">Gabung: {{ $emp['join_date'] }}</div>
                    </td>
                    <td class="py-4 px-5 text-slate-700 font-medium">{{ $emp['role'] }}</td>
                    <td class="py-4 px-5 text-slate-600">{{ $emp['phone'] }}</td>
                    <td class="py-4 px-5">
                        @if($emp['status'] == 'Aktif')
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-[10px] font-bold uppercase tracking-wider">Aktif</span>
                        @else
                        <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded-full text-[10px] font-bold uppercase tracking-wider">Cuti</span>
                        @endif
                    </td>
                    <td class="py-4 px-5 text-right">
                        <button class="text-blue-600 hover:text-blue-800 font-bold text-xs uppercase tracking-wider">Edit</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
