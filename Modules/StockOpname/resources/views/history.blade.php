@extends('layouts.app')

@section('title', 'Riwayat Stock Opname')
@section('header_title', 'Riwayat Stock Opname')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <a href="{{ route('stockopname.index') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-800 font-bold transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Kembali ke Opname
    </a>
</div>

<div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
    <div class="p-4 border-b border-slate-200 bg-slate-50">
        <h3 class="text-base font-bold text-slate-800">Semua Catatan Stock Opname</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">Tanggal & Jam</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">Produk</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs text-center">Stok Sistem</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs text-center">Stok Fisik</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs text-center">Selisih</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($history as $h)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="p-4 text-slate-600 font-medium">{{ $h->created_at->format('d/m/Y H:i') }}</td>
                    <td class="p-4 font-bold text-slate-800">{{ $h->product->nama ?? 'Produk Terhapus' }}</td>
                    <td class="p-4 text-center text-slate-500 font-medium">{{ $h->stok_sistem }}</td>
                    <td class="p-4 text-center text-slate-800 font-black">{{ $h->stok_fisik }}</td>
                    <td class="p-4 text-center">
                        @if($h->selisih > 0)
                            <span class="text-green-600 font-bold">+{{ $h->selisih }}</span>
                        @elseif($h->selisih < 0)
                            <span class="text-red-600 font-bold">{{ $h->selisih }}</span>
                        @else
                            <span class="text-slate-400">0</span>
                        @endif
                    </td>
                    <td class="p-4 text-slate-500 italic">{{ $h->keterangan ?: '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-8 text-center text-slate-400 italic">Belum ada riwayat stock opname.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($history instanceof \Illuminate\Pagination\LengthAwarePaginator)
    <div class="p-4 border-t border-slate-100">
        {{ $history->links() }}
    </div>
    @endif
</div>
@endsection
