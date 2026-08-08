@extends('layouts.app')

@section('title', 'Riwayat Transaksi POS')
@section('header_title', 'Riwayat Transaksi')

@section('content')
<div class="space-y-4" x-data="posHistoryApp()">
    <!-- Header & Filter Bar -->
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4">
        <div class="flex items-center gap-3">
            <h3 class="text-base font-bold text-slate-800">Daftar Transaksi Penjualan</h3>
            <span class="text-xs bg-slate-100 text-slate-600 px-2.5 py-1 rounded-full font-bold">Total: {{ $transactions->total() }}</span>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <!-- Filter Status -->
            <div class="flex bg-slate-100 p-1 rounded-lg text-xs font-bold">
                <a href="{{ route('pos.history') }}" 
                   class="px-3 py-1.5 rounded-md transition-all {{ !request('status') ? 'bg-white shadow text-slate-800 font-black' : 'text-slate-500 hover:text-slate-800' }}">
                    Semua
                </a>
                <a href="{{ route('pos.history', ['status' => 'lunas']) }}" 
                   class="px-3 py-1.5 rounded-md transition-all {{ request('status') === 'lunas' ? 'bg-emerald-500 text-white shadow font-black' : 'text-slate-500 hover:text-slate-800' }}">
                    Lunas
                </a>
                <a href="{{ route('pos.history', ['status' => 'sebagian']) }}" 
                   class="px-3 py-1.5 rounded-md transition-all {{ request('status') === 'sebagian' ? 'bg-amber-500 text-white shadow font-black' : 'text-slate-500 hover:text-slate-800' }}">
                    Bayar Sebagian
                </a>
                <a href="{{ route('pos.history', ['status' => 'belum_bayar']) }}" 
                   class="px-3 py-1.5 rounded-md transition-all {{ request('status') === 'belum_bayar' ? 'bg-rose-500 text-white shadow font-black' : 'text-slate-500 hover:text-slate-800' }}">
                    Belum Lunas
                </a>
            </div>

            <a href="{{ route('pos.index') }}" class="text-center bg-[#2563eb] hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-xs font-bold transition-all shadow-sm flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Transaksi Baru
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span class="text-xs font-bold">{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl flex items-center gap-2">
            <svg class="w-5 h-5 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="text-xs font-bold">{{ $errors->first() }}</span>
        </div>
    @endif

    <!-- Search Input -->
    <div class="bg-white border border-slate-200 rounded-xl p-3 shadow-sm">
        <form action="{{ route('pos.history') }}" method="GET" class="flex items-center gap-2">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <div class="relative flex-1">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor transaksi atau nama pelanggan..." 
                       class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-lg text-xs font-semibold focus:ring-blue-500 focus:border-blue-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-lg transition-all">Cari</button>
            @if(request('search'))
                <a href="{{ route('pos.history', ['status' => request('status')]) }}" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold rounded-lg">Reset</a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="p-4 font-semibold uppercase tracking-wider text-xs">No. Transaksi</th>
                        <th class="p-4 font-semibold uppercase tracking-wider text-xs">Tanggal</th>
                        <th class="p-4 font-semibold uppercase tracking-wider text-xs">Pelanggan</th>
                        <th class="p-4 font-semibold uppercase tracking-wider text-xs text-right">Total</th>
                        <th class="p-4 font-semibold uppercase tracking-wider text-xs">Metode</th>
                        <th class="p-4 font-semibold uppercase tracking-wider text-xs">Status Pembayaran</th>
                        <th class="p-4 font-semibold uppercase tracking-wider text-xs text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
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
                        <td class="p-4 text-slate-600 font-medium">{{ \Carbon\Carbon::parse($t->tgl_transaksi)->format('d/m/Y H:i') }}</td>
                        <td class="p-4">
                            <div class="font-medium text-slate-700">{{ $t->pelanggan->nama ?? ($t->nama_pelanggan ?: 'Umum') }}</div>
                        </td>
                        <td class="p-4 text-right font-black text-slate-800">Rp {{ number_format($t->total_tagihan, 0, ',', '.') }}</td>
                        <td class="p-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $t->metode_pembayaran === 'Bon' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-blue-100 text-blue-700 border border-blue-200' }} uppercase">
                                {{ $t->metode_pembayaran }}
                            </span>
                        </td>
                        <td class="p-4">
                            @if($t->status_pembayaran === 'lunas')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-700 uppercase border border-emerald-300">
                                    ✓ Lunas
                                </span>
                            @elseif($t->status_pembayaran === 'sebagian')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-100 text-amber-700 uppercase border border-amber-300">
                                    ⏱️ Bayar Sebagian
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-100 text-rose-700 uppercase border border-rose-300">
                                    ✕ Belum Lunas
                                </span>
                            @endif
                        </td>
                        <td class="p-4 text-center whitespace-nowrap" @click.stop>
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" @click="openStatusModal({{ $t->id }})" class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold text-xs rounded border border-amber-200 flex items-center gap-1 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    Edit Status
                                </button>
                                <a href="{{ route('pos.retur.index', ['search' => $t->no_transaksi]) }}" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs rounded border border-rose-200 flex items-center gap-1 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"></path></svg>
                                    Retur
                                </a>
                                <a href="{{ route('pos.receipt', $t->id) }}" target="_blank" class="px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs rounded border border-blue-200 flex items-center gap-1 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    Struk
                                </a>
                            </div>
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
                                        <div class="col-span-2 text-center font-semibold text-slate-600">{{ fmod($d->qty, 1) !== 0.0 ? number_format($d->qty, 2, ',', '.') : number_format($d->qty, 0, ',', '.') }}</div>
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
                                        <div>
                                            <span class="font-bold text-slate-400 uppercase tracking-wider">Status Pembayaran:</span>
                                            <span class="font-bold {{ $t->status_pembayaran === 'lunas' ? 'text-emerald-600' : ($t->status_pembayaran === 'sebagian' ? 'text-amber-600' : 'text-rose-600') }} uppercase">
                                                {{ $t->status_pembayaran === 'lunas' ? 'Lunas' : ($t->status_pembayaran === 'sebagian' ? 'Bayar Sebagian' : 'Belum Lunas') }}
                                            </span>
                                        </div>
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

    <!-- UPDATE STATUS MODAL -->
    <div x-show="statusModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center" style="display: none;">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="statusModalOpen = false" x-transition.opacity></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm relative z-10 m-4 overflow-hidden transform transition-all" x-transition>
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Ubah Status Pembayaran</h3>
                    <p class="text-xs text-slate-500" x-text="selectedTrx.no_transaksi"></p>
                </div>
                <button @click="statusModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form :action="`/pos/status/${selectedTrx.id}`" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6 space-y-4">
                    <div class="p-3 bg-slate-50 rounded-xl space-y-1 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500 font-bold">Total Tagihan</span>
                            <span class="font-black text-slate-800" x-text="'Rp ' + new Number(selectedTrx.total_tagihan).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 font-bold">Telah Dibayar</span>
                            <span class="font-black text-emerald-600" x-text="'Rp ' + new Number(selectedTrx.jumlah_bayar).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="pt-1 mt-1 border-t border-slate-200 flex justify-between">
                            <span class="text-slate-800 font-bold">Sisa Tagihan</span>
                            <span class="font-black text-rose-600" x-text="'Rp ' + new Number(Math.max(0, selectedTrx.total_tagihan - selectedTrx.jumlah_bayar)).toLocaleString('id-ID')"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Status Pembayaran <span class="text-rose-500">*</span></label>
                        <select name="status_pembayaran" x-model="selectedTrx.status_pembayaran" class="w-full border border-slate-300 rounded-lg text-xs p-2.5 bg-white font-bold text-slate-800">
                            <option value="lunas">✓ Lunas (Bayar Penuh)</option>
                            <option value="sebagian">⏱️ Bayar Sebagian (Cicil)</option>
                            <option value="belum_bayar">✕ Belum Lunas (Bon Utuh)</option>
                        </select>
                    </div>

                    <div x-show="selectedTrx.status_pembayaran === 'sebagian'">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Jumlah Yang Dibayar (Rp)</label>
                        <input type="number" name="jumlah_bayar" x-model.number="selectedTrx.jumlah_bayar" min="0" :max="selectedTrx.total_tagihan" class="w-full border border-slate-300 rounded-lg text-xs p-2.5 bg-white font-black text-blue-600">
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/50">
                    <button type="button" @click="statusModalOpen = false" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-bold shadow-md shadow-emerald-100 transition-all">Simpan Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function posHistoryApp() {
    return {
        expandedTrx: null,
        statusModalOpen: false,
        selectedTrx: { id: null, no_transaksi: '', total_tagihan: 0, jumlah_bayar: 0, status_pembayaran: 'lunas' },
        transactions: @json($transactions->items()),
        openStatusModal(id) {
            const t = this.transactions.find(item => item.id == id);
            if (!t) return;
            this.selectedTrx = JSON.parse(JSON.stringify(t));
            this.statusModalOpen = true;
        }
    };
}
</script>
@endpush
@endsection
