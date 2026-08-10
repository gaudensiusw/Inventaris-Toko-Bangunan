@extends('layouts.app')

@section('title', 'Toko Bangunan - Retur Penjualan')
@section('header_title', 'Retur Penjualan')

@section('content')
<div class="space-y-6">
    <!-- Header Row -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Retur Penjualan Barang</h2>
            <p class="text-sm text-slate-500">Cari nomor struk/transaksi, centang barang yang dikembalikan, dan tentukan kondisi barang.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('pos.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Ke Kasir POS
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span class="text-sm font-bold">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl flex items-center gap-2">
            <svg class="w-5 h-5 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="text-sm font-bold">{{ $errors->first() }}</span>
        </div>
    @endif

    <!-- Search Form -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <form action="{{ route('pos.retur.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <svg class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Ketik atau Scan Nomor Struk (misal: TRX-20260805...)" 
                       class="w-full pl-10 pr-4 py-3 border border-slate-300 rounded-xl text-sm font-bold text-slate-800 focus:ring-rose-500 focus:border-rose-500 bg-slate-50/50">
            </div>
            <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm rounded-xl shadow-md shadow-rose-100 transition-all flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                Cari Transaksi
            </button>
        </form>

        <!-- Quick Select Transactions -->
        @if(!$selected_trx && count($recent_transactions) > 0)
            <div class="mt-4 pt-4 border-t border-slate-100">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-2">Transaksi Terakhir:</span>
                <div class="flex flex-wrap gap-2">
                    @foreach($recent_transactions as $rt)
                        <a href="{{ route('pos.retur.index', ['search' => $rt->no_transaksi]) }}" 
                           class="px-3 py-1.5 bg-slate-100 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 border border-slate-200 rounded-lg text-xs font-bold text-slate-700 transition-all">
                            {{ $rt->no_transaksi }} ({{ $rt->pelanggan->nama ?? 'Umum' }})
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @if($selected_trx)
        @php
            $alreadyFullyRefunded = $selected_trx->details->isEmpty();
            $hasExistingRefunds = $selected_trx->refunds && $selected_trx->refunds->count() > 0;
        @endphp

        @if($alreadyFullyRefunded)
            {{-- Semua barang sudah diretur --}}
            <div class="bg-amber-50 border border-amber-300 rounded-2xl p-6 text-center">
                <svg class="w-12 h-12 text-amber-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                <h4 class="text-base font-bold text-amber-800 mb-1">Transaksi Sudah Diretur Penuh</h4>
                <p class="text-xs text-amber-700">Semua barang pada transaksi <strong>{{ $selected_trx->no_transaksi }}</strong> telah diretur. Tidak dapat melakukan retur ulang.</p>
                @if($hasExistingRefunds)
                    <a href="{{ route('pos.retur.receipt', $selected_trx->refunds->last()->id) }}" target="_blank"
                       class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-amber-600 text-white font-bold text-xs rounded-xl hover:bg-amber-700 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Lihat Struk Retur Terakhir
                    </a>
                @endif
            </div>
        @else
        {{-- Transaction Detail & Refund Form --}}
        <div class="bg-white rounded-2xl border border-rose-200 shadow-md overflow-hidden" x-data="{
            items: [
                @foreach($selected_trx->details as $d)
                {
                    detail_id: {{ $d->id }},
                    nama: @js($d->product ? $d->product->nama : 'Produk'),
                    selected: false,
                    qty_max: {{ $d->qty }},
                    qty_refund: 0,
                    harga: {{ floatval($d->harga_satuan ?: $d->harga) }},
                    kondisi: 'layak'
                },
                @endforeach
            ],
            alasan: 'Salah Beli / Tukar Ukuran',
            toggleItem(idx) {
                if (this.items[idx].selected) {
                    if (this.items[idx].qty_refund <= 0) this.items[idx].qty_refund = this.items[idx].qty_max;
                } else {
                    this.items[idx].qty_refund = 0;
                }
            },
            get grandTotalRefund() {
                return this.items.reduce((sum, i) => i.selected ? sum + (Number(i.qty_refund || 0) * Number(i.harga || 0)) : sum, 0);
            },
            get hasSelected() {
                return this.items.some(i => i.selected && Number(i.qty_refund) > 0);
            },
            get selectedSummary() {
                return this.items
                    .filter(i => i.selected && Number(i.qty_refund) > 0)
                    .map(i => `${i.nama} ×${i.qty_refund}`)
                    .join(', ');
            },
            showConfirm: false,
            isOperator: {{ auth()->user()->role === 'operator' ? 'true' : 'false' }},
            submitForm() {
                this.showConfirm = false;
                this.$nextTick(() => this.$refs.returForm.submit());
            }
        }">
            <!-- Header Info -->
            <div class="p-6 bg-rose-50/50 border-b border-rose-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-1 bg-rose-600 text-white font-black text-xs rounded-lg uppercase tracking-wider">Form Retur</span>
                        <h3 class="text-xl font-bold text-slate-800">{{ $selected_trx->no_transaksi }}</h3>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Tanggal: <strong class="text-slate-700">{{ \Carbon\Carbon::parse($selected_trx->tgl_transaksi)->format('d M Y H:i') }}</strong> | 
                        Pelanggan: <strong class="text-slate-700">{{ $selected_trx->pelanggan->nama ?? ($selected_trx->nama_pelanggan ?: 'Umum / Retail') }}</strong> | 
                        Metode: <strong class="text-slate-700">{{ $selected_trx->metode_pembayaran }}</strong>
                    </p>
                </div>
                <div class="text-right">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wide">Total Transaksi Asli:</span>
                    <p class="text-xl font-black text-slate-800">Rp {{ number_format($selected_trx->total_tagihan, 0, ',', '.') }}</p>
                </div>
            </div>

            <!-- Form -->
            <form x-ref="returForm" action="{{ route('pos.retur.process') }}" method="POST" class="p-6 space-y-6">
                @csrf
                <input type="hidden" name="pos_id" value="{{ $selected_trx->id }}">

                {{-- Riwayat Retur Sebelumnya (jika ada retur parsial) --}}
                @if($selected_trx->refunds && $selected_trx->refunds->count() > 0)
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                    <p class="text-xs font-bold text-amber-700 mb-2 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Transaksi ini sudah pernah diretur sebagian:
                    </p>
                    <div class="space-y-1">
                        @foreach($selected_trx->refunds->groupBy('no_refund') as $noRefund => $group)
                        <div class="flex items-center justify-between text-xs bg-white rounded-lg px-3 py-2 border border-amber-100">
                            <div>
                                <span class="font-black text-amber-800">{{ $noRefund ?: ('Retur #' . $loop->iteration) }}</span>
                                <span class="text-amber-600 ml-2">{{ \Carbon\Carbon::parse($group->first()->tgl_refund)->format('d M Y H:i') }}</span>
                                <span class="text-slate-500 ml-2">— {{ $group->count() }} barang</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-black text-rose-600">Rp {{ number_format($group->sum('nominal_refund'), 0, ',', '.') }}</span>
                                <a href="{{ route('pos.retur.receipt', $group->first()->id) }}" target="_blank"
                                   class="px-2 py-1 bg-amber-100 hover:bg-amber-200 text-amber-800 font-bold text-[10px] rounded border border-amber-200 transition-all">
                                    Lihat Struk
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <p class="text-[10px] text-amber-600 mt-2 font-medium">Qty barang di bawah sudah menunjukkan sisa yang belum diretur.</p>
                </div>
                @endif

                {{-- Panduan Pengisian --}}
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <p class="text-xs font-bold text-blue-700 mb-2 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Cara Melakukan Retur:
                    </p>
                    <ol class="text-xs text-blue-600 space-y-1 list-decimal list-inside">
                        <li>Pilih <strong>Alasan Retur</strong> dari dropdown di bawah</li>
                        <li>Centang (✓) pada kolom <strong>"Pilih"</strong> untuk barang yang akan diretur</li>
                        <li>Isi <strong>Qty Retur</strong> (jumlah barang yang dikembalikan, max sesuai kolom "Dibeli")</li>
                        <li>Pilih <strong>Kondisi Barang</strong>: apakah masih layak jual atau sudah rusak/cacat</li>
                        <li>Klik tombol <strong>"Proses & Cetak Struk Retur"</strong></li>
                    </ol>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-2">Alasan Retur / Pengembalian <span class="text-rose-500">*</span></label>
                    <select name="alasan" x-model="alasan" class="w-full md:w-1/2 border border-slate-300 rounded-xl text-sm p-3 font-semibold text-slate-800 bg-white">
                        <option value="Salah Beli / Tukar Ukuran">Salah Beli / Tukar Ukuran</option>
                        <option value="Barang Cacat / Rusak Pabrik">Barang Cacat / Rusak Pabrik</option>
                        <option value="Kelebihan Beli / Sisa Proyek">Kelebihan Beli / Sisa Proyek</option>
                        <option value="Retur Kesepakatan Khusus Toko">Retur Kesepakatan Khusus Toko</option>
                    </select>
                </div>

                <!-- Table Items -->
                <div class="overflow-x-auto border border-slate-200 rounded-xl">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-100 text-slate-600 uppercase font-black border-b border-slate-200">
                                <th class="py-3 px-4 text-center w-12">Pilih</th>
                                <th class="py-3 px-4">Nama Barang</th>
                                <th class="py-3 px-4 text-center w-24">Dibeli</th>
                                <th class="py-3 px-4 w-32">Qty Retur <span class="text-rose-500">*</span></th>
                                <th class="py-3 px-4 w-48">Kondisi Barang <span class="text-rose-500">*</span></th>
                                <th class="py-3 px-4 text-right w-36">Subtotal Refund</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(item, idx) in items" :key="idx">
                                <tr :class="item.selected ? 'bg-rose-50/40' : 'hover:bg-slate-50/60'">
                                    <td class="py-3 px-4 text-center">
                                        <input type="checkbox" x-model="item.selected" @change="toggleItem(idx)" 
                                               class="w-4 h-4 text-rose-600 rounded border-slate-300 focus:ring-rose-500 cursor-pointer">
                                    </td>
                                    <td class="py-3 px-4 font-bold text-slate-800" x-text="item.nama"></td>
                                    <td class="py-3 px-4 text-center font-bold text-slate-600" x-text="item.qty_max"></td>
                                    <td class="py-3 px-4">
                                        <input type="hidden" :name="`items[${idx}][detail_id]`" :value="item.detail_id">
                                        <input type="number" step="0.01" min="0" :max="item.qty_max" 
                                               :name="`items[${idx}][qty_refund]`" 
                                               x-model="item.qty_refund" :disabled="!item.selected"
                                               class="w-full border border-slate-300 rounded-lg text-xs p-2 text-center font-black text-rose-600 bg-white disabled:bg-slate-100 disabled:text-slate-400"
                                               :class="item.selected && Number(item.qty_refund) <= 0 ? 'border-rose-400 ring-1 ring-rose-300' : ''">
                                        <p x-show="item.selected && Number(item.qty_refund) <= 0" class="text-[10px] text-rose-500 font-bold mt-1">Isi qty!</p>
                                    </td>
                                    <td class="py-3 px-4">
                                        {{-- PENTING: kondisi TIDAK di-disabled agar selalu tersubmit --}}
                                        {{-- Secara visual dikontrol dengan opacity, bukan disabled --}}
                                        <select :name="`items[${idx}][kondisi]`" x-model="item.kondisi"
                                                :class="!item.selected ? 'opacity-40 pointer-events-none' : ''"
                                                class="w-full border border-slate-300 rounded-lg text-xs p-2 font-bold text-slate-700 bg-white">
                                            <option value="layak">✓ Masuk Stok Jual</option>
                                            <option value="rusak">✕ Cacat / Rusak (Jangan Tambah Stok)</option>
                                        </select>
                                    </td>
                                    <td class="py-3 px-4 text-right font-black text-rose-600" x-text="'Rp ' + new Number(item.selected ? (item.qty_refund * item.harga) : 0).toLocaleString('id-ID')"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Summary -->
                <div class="flex flex-col sm:flex-row justify-between items-center pt-4 border-t border-slate-200 gap-4">
                    <div>
                        @if($selected_trx->metode_pembayaran === 'Bon')
                            <p class="text-xs text-amber-600 font-bold bg-amber-50 px-3 py-1.5 rounded-lg border border-amber-200">
                                ℹ️ Transaksi Bon: Total refund akan <strong>memotong sisa hutang pelanggan</strong> pada transaksi ini.
                            </p>
                        @else
                            <p class="text-xs text-blue-600 font-bold bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-200">
                                ℹ️ Transaksi Cash: Serahkan uang kembalian tunai sebesar Total Refund kepada pelanggan.
                            </p>
                        @endif
                    </div>
                    <div class="flex items-center gap-6">
                        <div class="text-right">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Pengembalian:</span>
                            <span class="text-2xl font-black text-rose-600" x-text="'Rp ' + new Number(grandTotalRefund).toLocaleString('id-ID')"></span>
                        </div>
                        {{-- Tombol submit -> buka konfirmasi dulu --}}
                        <button type="button"
                                @click="if(hasSelected) showConfirm = true"
                                :disabled="!hasSelected"
                                :class="hasSelected ? 'bg-rose-600 hover:bg-rose-700 shadow-lg shadow-rose-100 cursor-pointer' : 'bg-slate-300 text-slate-500 cursor-not-allowed'"
                                class="px-6 py-3 text-white font-bold text-sm rounded-xl transition-all flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Proses & Cetak Struk Retur
                        </button>
                    </div>
                </div>

                {{-- Supervisor Fields: hanya tampil untuk operator, style display:none untuk mencegah flash --}}
                <div id="supervisor-fields" x-show="isOperator" style="display:none;" class="border-t border-dashed border-rose-200 pt-4 mt-2">
                    <p class="text-xs font-bold text-rose-600 mb-3 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Persetujuan Supervisor Diperlukan untuk Melakukan Retur
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Email Supervisor <span class="text-rose-500">*</span></label>
                            <input type="email" name="supervisor_email" placeholder="supervisor@toko.com"
                                   class="w-full border border-slate-300 rounded-lg text-xs p-2.5 font-semibold text-slate-800 bg-white focus:ring-rose-500 focus:border-rose-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Password Supervisor <span class="text-rose-500">*</span></label>
                            <input type="password" name="supervisor_password" placeholder="••••••••"
                                   class="w-full border border-slate-300 rounded-lg text-xs p-2.5 font-semibold text-slate-800 bg-white focus:ring-rose-500 focus:border-rose-500">
                        </div>
                    </div>
                </div>
            </form>

            {{-- Modal Konfirmasi Retur --}}
            <div x-show="showConfirm" class="fixed inset-0 z-[200] flex items-center justify-center" style="display:none;">
                <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="showConfirm = false"></div>
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden" x-transition>
                    <div class="bg-rose-600 px-6 py-4 flex items-center gap-3">
                        <svg class="w-6 h-6 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <h3 class="text-white font-black text-base">Konfirmasi Proses Retur</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <p class="text-sm text-slate-700">Anda akan memproses retur untuk barang berikut:</p>
                        <div class="bg-rose-50 border border-rose-100 rounded-xl p-3">
                            <p class="text-xs font-semibold text-rose-700" x-text="selectedSummary"></p>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-bold text-slate-600">Total Refund:</span>
                            <span class="text-xl font-black text-rose-600" x-text="'Rp ' + new Number(grandTotalRefund).toLocaleString('id-ID')"></span>
                        </div>
                        <p class="text-xs text-slate-500 bg-slate-50 p-3 rounded-lg border border-slate-200">
                            ⚠️ Tindakan ini <strong>tidak dapat dibatalkan</strong>. Stok barang kondisi layak akan dikembalikan ke inventaris.
                        </p>
                    </div>
                    <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50">
                        <button @click="showConfirm = false"
                                class="px-5 py-2 bg-white border border-slate-300 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all">
                            Batal
                        </button>
                        <button @click="submitForm()"
                                class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-bold shadow-md shadow-rose-100 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Ya, Proses Retur
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @elseif($search)
        <div class="bg-white p-12 rounded-2xl border border-slate-200 text-center">
            <svg class="w-16 h-16 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <h4 class="text-base font-bold text-slate-800 mb-1">Transaksi tidak ditemukan</h4>
            <p class="text-xs text-slate-500">Nomor struk "{{ $search }}" tidak terdaftar dalam sistem. Periksa kembali nomor pada lembar struk.</p>
        </div>
    @endif

    <!-- History Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-800">Riwayat Retur Penjualan</h3>
            <span class="text-xs font-bold text-slate-400">Total: {{ $recent_refunds->total() }} Log</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase font-bold border-b border-slate-200">
                        <th class="py-3 px-4">Tanggal & Waktu</th>
                        <th class="py-3 px-4">No. Transaksi</th>
                        <th class="py-3 px-4">Pelanggan</th>
                        <th class="py-3 px-4">Barang Diretur</th>
                        <th class="py-3 px-4">Alasan & Kondisi</th>
                        <th class="py-3 px-4 text-right">Nominal Refund</th>
                        <th class="py-3 px-4 text-center">Struk</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recent_refunds as $ref)
                        <tr class="hover:bg-slate-50/60">
                            <td class="py-3.5 px-4 font-semibold text-slate-500">
                                {{ \Carbon\Carbon::parse($ref->tgl_refund)->format('d M Y H:i') }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-800">
                                {{ $ref->no_transaksi }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-700">
                                {{ $ref->pos->pelanggan->nama ?? ($ref->pos->nama_pelanggan ?: 'Umum') }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-rose-700">
                                {{ $ref->nama_produk }} x {{ floatval($ref->qty_refund) }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 italic">
                                {{ $ref->alasan }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-black text-rose-600">
                                Rp {{ number_format($ref->nominal_refund, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('pos.retur.receipt', $ref->id) }}" target="_blank" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[10px] rounded-lg border border-slate-200 transition-all inline-flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    Cetak Struk
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400 font-bold">
                                Belum ada riwayat retur penjualan yang dicatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $recent_refunds->links() }}
        </div>
    </div>
</div>
@endsection
