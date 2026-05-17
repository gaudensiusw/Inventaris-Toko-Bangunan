@extends('layouts.app')

@section('title', 'Riwayat Stock Opname')
@section('header_title', 'Riwayat Stock Opname')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">{{ in_array(auth()->user()->role, ['owner', 'supervisor']) ? 'Riwayat Stok Opname' : 'Status Pengajuan Audit' }}</h2>
            <p class="text-slate-500 text-sm">{{ in_array(auth()->user()->role, ['owner', 'supervisor']) ? 'Log riwayat seluruh stok fisik yang berhasil disinkronisasi ke sistem' : 'Pantau status hasil audit yang telah Anda ajukan ke sistem' }}</p>
        </div>
        <a href="{{ route('stockopname.index') }}" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-bold transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Audit Baru
        </a>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 text-slate-500 border-b border-slate-200 uppercase tracking-widest text-[10px] font-black">
                    <tr>
                        <th class="p-4">Tanggal & Waktu</th>
                        <th class="p-4">Produk</th>
                        <th class="p-4 text-center">Stok Sistem</th>
                        <th class="p-4 text-center">Stok Fisik</th>
                        <th class="p-4 text-center">Selisih</th>
                        @if(!in_array(auth()->user()->role, ['owner', 'supervisor']))
                            <th class="p-4 text-center">Status</th>
                        @endif
                        <th class="p-4">Petugas</th>
                        <th class="p-4">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($history as $item)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="p-4">
                            <div class="font-bold text-slate-800">{{ $item->created_at->format('d M Y') }}</div>
                            <div class="text-[10px] text-slate-400">{{ $item->created_at->format('H:i:s') }}</div>
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-slate-800">{{ $item->product->nama ?? 'Produk Terhapus' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $item->product->sku ?? '-' }}</div>
                        </td>
                        <td class="p-4 text-center font-medium text-slate-600">
                            {{ number_format($item->stok_sistem) }}
                        </td>
                        <td class="p-4 text-center font-bold text-slate-800">
                            {{ number_format($item->stok_fisik) }}
                        </td>
                        <td class="p-4 text-center">
                            @if($item->selisih == 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-green-100 text-green-700 border border-green-200 uppercase">Match</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $item->selisih > 0 ? 'bg-blue-100 text-blue-700 border-blue-200' : 'bg-red-100 text-red-700 border-red-200' }} uppercase">
                                    {{ $item->selisih > 0 ? '+' : '' }}{{ $item->selisih }}
                                </span>
                            @endif
                        </td>
                        @if(!in_array(auth()->user()->role, ['owner', 'supervisor']))
                        <td class="p-4 text-center">
                            @if($item->status === 'pending')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-700 border border-amber-200 uppercase animate-pulse">Menunggu</span>
                            @elseif($item->status === 'approved')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-700 border border-emerald-200 uppercase">Selesai</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-slate-100 text-slate-500 border border-slate-200 uppercase tracking-tighter line-through">Ditolak</span>
                            @endif
                        </td>
                        @endif
                        <td class="p-4">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 bg-slate-200 rounded-full flex items-center justify-center text-[10px] font-bold text-slate-600 uppercase">
                                    {{ strtoupper(substr($item->causer->name ?? '?', 0, 1)) }}
                                </div>
                                <span class="font-medium text-slate-700">{{ $item->causer->name ?? 'Sistem' }}</span>
                            </div>
                        </td>
                        <td class="p-4 text-slate-500 italic text-xs">
                            {{ $item->keterangan ?: '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="p-12 text-center">
                            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <p class="text-slate-500 font-medium">Belum ada riwayat pengajuan audit</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($history->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $history->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
