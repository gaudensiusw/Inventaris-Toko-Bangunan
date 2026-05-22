@extends('layouts.app')

@section('title', 'Riwayat Transaksi POS')
@section('header_title', 'Riwayat Transaksi')

@section('content')
<div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
    <div class="p-4 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">
        <h3 class="text-base font-bold text-slate-800">Daftar Transaksi Penjualan</h3>
        <a href="{{ route('pos.index') }}" class="text-center bg-[#2563eb] hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition-all shadow-sm">
            + Transaksi Baru
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">No. Transaksi</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">Tanggal</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">Pelanggan</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs text-right">Total</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">Metode</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">Status</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($transactions as $t)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="p-4">
                        <div class="font-bold text-slate-800">{{ $t->no_transaksi }}</div>
                    </td>
                    <td class="p-4 text-slate-600 font-medium">{{ $t->tgl_transaksi->format('d/m/Y H:i') }}</td>
                    <td class="p-4">
                        <div class="font-medium text-slate-700">{{ $t->nama_pelanggan ?: 'Umum' }}</div>
                    </td>
                    <td class="p-4 text-right font-black text-slate-800">Rp {{ number_format($t->total_tagihan, 0, ',', '.') }}</td>
                    <td class="p-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $t->metode_pembayaran === 'Tunai' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }} uppercase">
                            {{ $t->metode_pembayaran }}
                        </span>
                    </td>
                    <td class="p-4">
                        @if($t->status_pembayaran === 'lunas')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700 uppercase border border-green-200">Lunas</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 uppercase border border-red-200">Belum Bayar</span>
                        @endif
                    </td>
                    <td class="p-4 text-center whitespace-nowrap">
                        <a href="{{ route('pos.receipt', $t->id) }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-bold text-xs flex items-center justify-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2-2H7a2 2 0 00-2 2v4m10 0h2"></path></svg>
                            Struk
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="p-8 text-center text-slate-400 italic">Belum ada data transaksi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-slate-100">
        {{ $transactions->links() }}
    </div>
</div>
@endsection
