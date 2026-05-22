@extends('layouts.app')

@section('title', 'Operator Dashboard - Toko Bangunan Rajawali')
@section('header_title', 'Dashboard Operator')

@section('content')
<div class="space-y-6">
    <!-- Welcome Header -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-slate-800">Halo, {{ auth()->user()->name }}!</h2>
                <p class="text-sm text-slate-500">Semangat bekerja hari ini. Yuk cek performa jualanmu.</p>
            </div>
        </div>
        <a href="{{ url('/pos') }}" class="w-full md:w-auto inline-flex items-center justify-center gap-2 bg-[#2563eb] hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold transition-all shadow-lg shadow-blue-200 group">
            <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            Buka Kasir / POS
        </a>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Sales Today -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-24 h-24 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path></svg>
            </div>
            <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Total Penjualanmu Hari Ini</p>
            <h3 class="text-3xl font-black text-green-600 mt-2">Rp {{ number_format($stats['sales_today'] ?? 0, 0, ',', '.') }}</h3>
            <div class="mt-4 flex items-center gap-2 text-xs text-slate-500">
                <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-bold">LIVE</span>
                <span>Data transaksi real-time</span>
            </div>
        </div>

        <!-- Transaction Count -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-24 h-24 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path></svg>
            </div>
            <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Jumlah Transaksi</p>
            <h3 class="text-3xl font-black text-blue-600 mt-2">{{ $stats['trx_count'] ?? 0 }} <span class="text-lg font-normal text-slate-400">Transaksi</span></h3>
            <div class="mt-4 flex items-center gap-2 text-xs text-slate-500">
                <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-bold">HARI INI</span>
                <span>Terus tingkatkan performamu!</span>
            </div>
        </div>
    </div>

    <!-- Recent Transactions Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                10 Transaksi Terakhirmu
            </h3>
            <a href="{{ route('pos.history') }}" class="text-sm text-blue-600 font-bold hover:underline">Lihat Semua</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-4">No. Transaksi</th>
                        <th class="px-6 py-4">Pelanggan</th>
                        <th class="px-6 py-4">Total Tagihan</th>
                        <th class="px-6 py-4">Metode</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentTransactions as $trx)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-mono text-xs font-bold text-slate-700">{{ $trx->no_transaksi }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-slate-800">{{ $trx->nama_pelanggan }}</span>
                                <span class="text-[10px] text-slate-500">{{ $trx->pelanggan?->kode ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm font-black text-slate-800">
                            Rp {{ number_format($trx->total_tagihan, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-[10px] font-bold {{ $trx->metode_pembayaran == 'Cash' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                                {{ $trx->metode_pembayaran }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded text-[10px] font-bold {{ $trx->status_pembayaran == 'lunas' ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $trx->status_pembayaran == 'lunas' ? 'bg-blue-500' : 'bg-red-500' }}"></span>
                                {{ strtoupper($trx->status_pembayaran) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-500 font-medium">
                            {{ $trx->created_at->diffForHumans() }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            <svg class="w-12 h-12 mx-auto mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Belum ada transaksi hari ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
