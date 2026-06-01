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
            <tbody class="divide-y divide-slate-100" x-data="{ expandedTrx: null }">
                @forelse($transactions as $t)
                <tr class="hover:bg-slate-50 transition-colors cursor-pointer" @click="expandedTrx = (expandedTrx === {{ $t->id }} ? null : {{ $t->id }})">
                    <td class="p-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" 
                                 :class="expandedTrx === {{ $t->id }} ? 'rotate-90 text-blue-500' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                            </svg>
                            <div class="font-bold text-slate-800">{{ $t->no_transaksi }}</div>
                        </div>
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
                    <td class="p-4 text-center whitespace-nowrap" @click.stop>
                        <a href="{{ route('pos.receipt', $t->id) }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-bold text-xs flex items-center justify-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2-2H7a2 2 0 00-2 2v4m10 0h2"></path></svg>
                            Struk
                        </a>
                    </td>
                </tr>
                <!-- Collapsible Detail Row -->
                <tr x-show="expandedTrx === {{ $t->id }}" style="display: none;" class="bg-slate-50/40">
                    <td colspan="7" class="p-4 border-t border-b border-slate-100">
                        <div class="px-6 py-4 bg-white rounded-xl border border-slate-150 shadow-sm max-w-4xl mx-auto">
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                Rincian Barang Belanjaan
                            </div>
                            <div class="divide-y divide-slate-100 border border-slate-100 rounded-lg overflow-hidden">
                                <div class="grid grid-cols-12 text-[10px] font-bold text-slate-500 uppercase tracking-wider bg-slate-50/50 p-2.5">
                                    <div class="col-span-6">Nama Produk</div>
                                    <div class="col-span-2 text-center">Satuan</div>
                                    <div class="col-span-2 text-center">Qty</div>
                                    <div class="col-span-2 text-right">Subtotal</div>
                                </div>
                                @foreach($t->details as $d)
                                <div class="grid grid-cols-12 text-xs p-2.5 items-center text-slate-700 hover:bg-slate-50/30 transition-colors">
                                    <div class="col-span-6 font-semibold text-slate-800">
                                        {{ $d->product->nama ?? 'Produk Tidak Ditemukan' }}
                                    </div>
                                    <div class="col-span-2 text-center">
                                        <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide">
                                            {{ $d->satuan_nama ?: 'Pcs' }}
                                        </span>
                                    </div>
                                    <div class="col-span-2 text-center font-semibold text-slate-600">{{ number_format($d->qty, 0) }}</div>
                                    <div class="col-span-2 text-right font-black text-slate-800 font-mono">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</div>
                                </div>
                                @endforeach
                            </div>
                            
                            <!-- Additional Info & Summaries -->
                            <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap justify-between items-center gap-4 text-xs">
                                <div class="flex flex-wrap gap-x-6 gap-y-2 text-slate-500 font-medium">
                                    <div>
                                        <span class="font-bold text-slate-400 uppercase tracking-wider">Subtotal:</span>
                                        <span class="font-mono text-slate-700">Rp {{ number_format($t->subtotal, 0, ',', '.') }}</span>
                                    </div>
                                    @if($t->pajak > 0)
                                    <div>
                                        <span class="font-bold text-slate-400 uppercase tracking-wider">Pajak:</span>
                                        <span class="font-mono text-slate-700">Rp {{ number_format($t->pajak, 0, ',', '.') }}</span>
                                    </div>
                                    @endif
                                    @if($t->ongkos_kirim > 0)
                                    <div>
                                        <span class="font-bold text-slate-400 uppercase tracking-wider">Ongkir:</span>
                                        <span class="font-mono text-slate-700">Rp {{ number_format($t->ongkos_kirim, 0, ',', '.') }}</span>
                                    </div>
                                    @endif
                                </div>
                                @if($t->catatan)
                                <div class="w-full bg-slate-50 p-2.5 rounded-lg border border-slate-100 text-slate-600">
                                    <span class="font-bold text-slate-400 uppercase tracking-wider block mb-1">Catatan:</span>
                                    <span class="italic">{{ $t->catatan }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
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
