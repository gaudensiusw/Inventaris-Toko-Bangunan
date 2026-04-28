@extends('layouts.app')

@section('title', 'Toko Bangunan - Karyawan')
@section('header_title', 'Manajemen Karyawan')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Manajemen Karyawan</h1>
            <p class="text-slate-600 mt-1">Kelola data karyawan, absensi, dan penggajian</p>
        </div>
        <!-- Only visible to Owner and Supervisor -->
        <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Karyawan
        </button>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-200 bg-white flex justify-between items-center">
            <h2 class="text-lg font-bold text-slate-900">Daftar Karyawan</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-5 font-semibold text-xs tracking-wider uppercase">No</th>
                        <th class="py-3 px-5 font-semibold text-xs tracking-wider uppercase">Nama Karyawan</th>
                        <th class="py-3 px-5 font-semibold text-xs tracking-wider uppercase">Posisi</th>
                        <th class="py-3 px-5 font-semibold text-xs tracking-wider uppercase">Tanggal Bergabung</th>
                        <th class="py-3 px-5 font-semibold text-xs tracking-wider uppercase">Status</th>
                        <th class="py-3 px-5 font-semibold text-xs tracking-wider uppercase text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($employees as $index => $emp)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-4 px-5 text-slate-500">{{ $index + 1 }}</td>
                        <td class="py-4 px-5">
                            <div class="font-bold text-slate-900">{{ $emp['name'] }}</div>
                        </td>
                        <td class="py-4 px-5 text-slate-700 font-medium">{{ $emp['role'] }}</td>
                        <td class="py-4 px-5 text-slate-600">{{ $emp['join_date'] }}</td>
                        <td class="py-4 px-5">
                            @if($emp['status'] == 'Aktif')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                    Aktif
                                </span>
                            @elseif($emp['status'] == 'Izin')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">
                                    Izin
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                    Alpa
                                </span>
                            @endif
                        </td>
                        <td class="py-4 px-5 text-right space-x-2">
                            <!-- Detail Action -->
                            <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Detail">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </button>
                            
                            <!-- Only visible to Owner and Supervisor -->
                            <!-- Edit Action -->
                            <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-500 hover:text-orange-600 hover:bg-orange-50 transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>
                            <!-- Delete Action -->
                            <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-500 hover:text-red-600 hover:bg-red-50 transition-colors" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
