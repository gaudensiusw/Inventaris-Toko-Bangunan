@extends('layouts.app')

@section('title', 'Toko Bangunan - Transaksi Pembelian')
@section('header_title', 'Transaksi Pembelian')

@section('content')
<div x-data="pembelianManager()">
    <!-- Alert Messages -->
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Header Actions -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Riwayat Pembelian</h2>
            <p class="text-sm text-slate-500">Kelola transaksi kulakan & penambahan stok</p>
        </div>
        <button @click="addModalOpen = true; if(form.items.length === 0) addItem();" class="bg-[#0f172a] hover:bg-slate-800 text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow-sm transition-colors flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            Buat Pembelian Baru
        </button>
    </div>

    <!-- Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs">No. Transaksi</th>
                        <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs">Tanggal</th>
                        <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs">Supplier</th>
                        <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs">Total</th>
                        <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs">Status</th>
                        <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pembelians as $p)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="py-4 px-5">
                            <div class="font-bold text-slate-800">{{ $p->no_transaksi }}</div>
                            <div class="text-[10px] text-slate-500 uppercase mt-0.5">{{ $p->details->count() }} Item Barang</div>
                        </td>
                        <td class="py-4 px-5 text-slate-600">
                            {{ \Carbon\Carbon::parse($p->tgl_pembelian)->format('d M Y') }}
                        </td>
                        <td class="py-4 px-5 font-medium text-slate-700">
                            {{ $p->supplier->company_name ?? 'N/A' }}
                        </td>
                        <td class="py-4 px-5 font-bold text-slate-800">
                            Rp {{ number_format($p->total_pembelian, 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-5">
                            @if($p->status === 'selesai')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700 uppercase border border-green-200">Diterima</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 uppercase border border-amber-200 animate-pulse">Pending</span>
                            @endif
                        </td>
                        <td class="py-4 px-5 text-right space-x-1">
                            @if($p->status === 'pending')
                                <button type="button" @click="confirmReceive(@js($p))" class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg transition-colors inline-block" title="Terima Barang & Update Stok">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </button>
                            @endif
                            <button @click="openViewModal(@js($p))" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors inline-block" title="Lihat Detail">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </button>
                            @if(auth()->user()->role === 'owner')
                            <button @click="openDeleteModal(@js($p))" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors inline-block" title="Hapus Transaksi">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012-2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                <p class="text-sm font-medium">Belum ada riwayat pembelian.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-slate-100">
            {{ $pembelians->links() }}
        </div>
    </div>

    <!-- ADD MODAL (CART) -->
    <div x-show="addModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center" style="display: none;">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="addModalOpen = false" x-transition.opacity></div>
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-5xl relative z-10 m-4 overflow-hidden transform transition-all flex flex-col max-h-[90vh]" x-transition>
            
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50 flex-shrink-0">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Faktur Pembelian Baru</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Stok & MAC akan otomatis ter-update. Tagihan akan masuk ke Tagihan Supplier.</p>
                </div>
                <button @click="addModalOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-lg hover:bg-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form id="purchaseForm" action="{{ route('pembelian.store') }}" method="POST" class="flex flex-col overflow-hidden h-full">
                @csrf
                <div class="p-6 overflow-y-auto flex-1 custom-scrollbar" style="min-height: 400px;">

                    
                    <!-- Header Info -->
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6 bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Supplier <span class="text-red-500">*</span></label>
                            <select name="supplier_id" x-model="form.supplier_id" required class="w-full border border-slate-300 rounded-lg text-sm p-2 focus:ring-blue-500 focus:border-blue-500 bg-white font-bold">
                                <option value="">Pilih Supplier...</option>
                                @foreach($suppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Tgl Faktur <span class="text-red-500">*</span></label>
                            <input type="date" name="tgl_pembelian" x-model="form.tgl_pembelian" required class="w-full border border-slate-300 rounded-lg text-sm p-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Jatuh Tempo <span class="text-red-500">*</span></label>
                            <input type="date" name="jatuh_tempo" x-model="form.jatuh_tempo" required class="w-full border border-slate-300 rounded-lg text-sm p-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Status Barang <span class="text-red-500">*</span></label>
                            <select name="status" x-model="form.status" required class="w-full border border-slate-300 rounded-lg text-sm p-2 focus:ring-blue-500 focus:border-blue-500 bg-white font-bold" :class="form.status === 'selesai' ? 'text-green-600' : 'text-amber-600'">
                                <option value="selesai">Langsung Diterima</option>
                                <option value="pending">Pending (Belum Sampai)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Catatan</label>
                            <input type="text" name="catatan" x-model="form.catatan" placeholder="Opsional" class="w-full border border-slate-300 rounded-lg text-sm p-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                        </div>
                    </div>

                    <!-- Cart Details -->
                    <h4 class="text-xs font-black text-slate-700 uppercase tracking-widest mb-3 flex items-center justify-between">
                        Rincian Barang Masuk
                        <button type="button" @click="addItem()" class="text-blue-600 hover:text-blue-700 text-xs flex items-center gap-1 bg-blue-50 px-2 py-1 rounded">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Tambah Baris
                        </button>
                    </h4>

                    <div class="space-y-3 pb-40">

                        <template x-for="(item, index) in form.items" :key="index">
                            <div class="flex flex-wrap items-end gap-3 bg-white border border-slate-200 p-3 rounded-lg shadow-sm">
                                
                                <div class="flex-1 min-w-[250px]" x-data="{ searchOpen: false, search: '' }" @click.away="searchOpen = false">
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Produk</label>
                                    <div class="relative">
                                        <input type="hidden" :name="`items[${index}][produk_id]`" :value="item.produk_id" required>
                                        <div @click="searchOpen = !searchOpen; if(searchOpen) { $nextTick(() => { $refs.searchInput.focus(); }); }" class="w-full border border-slate-300 rounded text-sm p-2 bg-white cursor-pointer flex justify-between items-center" :class="item.produk_id ? 'text-slate-800 font-bold' : 'text-slate-500'">
                                            <span x-text="item.produk_id ? (products.find(p => p.id == item.produk_id)?.nama + ' (' + (products.find(p => p.id == item.produk_id)?.sku || products.find(p => p.id == item.produk_id)?.merk || '-') + ')') : '-- Ketik / Pilih Barang --'" class="truncate"></span>
                                            <svg class="w-4 h-4 flex-shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                        </div>
                                        <div x-show="searchOpen" class="absolute z-[100] left-0 right-0 mt-1 bg-white border border-slate-200 rounded-lg shadow-2xl overflow-hidden" style="display: none;">
                                            <div class="p-2 border-b border-slate-100 bg-slate-50">
                                                <input type="text" x-model="search" x-ref="searchInput" placeholder="Cari nama/sku/merk..." class="w-full text-sm p-2 border border-slate-300 rounded focus:ring-blue-500 focus:border-blue-500" @keydown.escape="searchOpen = false">
                                            </div>
                                            <div class="max-h-60 overflow-y-auto custom-scrollbar">
                                                <template x-for="p in products.filter(p => (!form.supplier_id || p.supplier_id == form.supplier_id) && (p.nama.toLowerCase().includes(search.toLowerCase()) || (p.sku && p.sku.toLowerCase().includes(search.toLowerCase())) || (p.merk && p.merk.toLowerCase().includes(search.toLowerCase()))))" :key="p.id">
                                                    <div @click="item.produk_id = p.id; updateProductUnits(index); searchOpen = false; search = ''" class="px-3 py-2 text-sm hover:bg-blue-50 cursor-pointer border-b border-slate-50 last:border-0">
                                                        <div class="font-bold text-slate-800" x-text="p.nama"></div>
                                                        <div class="flex justify-between items-center">
                                                            <div class="text-[10px] text-slate-500" x-text="p.sku || p.merk || '-'"></div>
                                                            <div class="text-[10px] font-bold text-blue-600">Rp <span x-text="formatNumber(p.harga_beli)"></span></div>
                                                        </div>
                                                    </div>
                                                </template>
                                                <div x-show="products.filter(p => (!form.supplier_id || p.supplier_id == form.supplier_id) && (p.nama.toLowerCase().includes(search.toLowerCase()) || (p.sku && p.sku.toLowerCase().includes(search.toLowerCase())) || (p.merk && p.merk.toLowerCase().includes(search.toLowerCase())))).length === 0" class="p-3 text-center text-xs text-slate-500">
                                                    <span x-show="form.supplier_id">Barang tidak ditemukan untuk supplier ini</span>
                                                    <span x-show="!form.supplier_id">Ketik nama barang...</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="w-28">
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Beli Dalam</label>
                                    <select :name="`items[${index}][satuan]`" x-model="item.satuan" @change="updateUnitIsi(index)" required class="w-full border border-slate-300 rounded text-sm p-2 focus:ring-blue-500 bg-white font-bold text-slate-700" :disabled="item.available_units.length === 0">
                                        <option value="" disabled x-show="item.available_units.length === 0">--</option>
                                        <template x-for="u in item.available_units">
                                            <option :value="u.nama" x-text="u.label"></option>
                                        </template>
                                    </select>
                                </div>

                                <div class="w-20">
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Qty</label>
                                    <input type="number" step="1" min="1" :name="`items[${index}][qty]`" x-model="item.qty" required class="w-full border border-slate-300 rounded text-sm p-2 text-center focus:ring-blue-500 font-bold">
                                </div>

                                <!-- Hidden isi_per_satuan -->
                                <input type="hidden" :name="`items[${index}][isi_per_satuan]`" :value="item.isi_per_satuan">

                                <div class="flex-1 min-w-[150px]">
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Total Harga Beli (Rp)</label>
                                    <input type="number" step="1" min="0" :name="`items[${index}][harga_total]`" x-model="item.harga_total" required class="w-full border border-slate-300 rounded text-sm p-2 focus:ring-blue-500 font-mono font-bold text-blue-600 bg-slate-50">
                                </div>

                                <div class="w-10 flex justify-center">
                                    <button type="button" @click="removeItem(index)" class="text-red-400 hover:text-red-600 p-2 rounded hover:bg-red-50 transition-colors" title="Hapus Baris">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                                
                                <div class="w-full text-[10px] text-slate-400 mt-1 pl-1">
                                    <span x-show="item.isi_per_satuan > 1">
                                        1 <span x-text="item.satuan"></span> = <span x-text="item.isi_per_satuan"></span> Satuan Utama. 
                                        Total masuk: <strong class="text-slate-600" x-text="(item.qty * item.isi_per_satuan)"></strong> Satuan Utama.
                                    </span>
                                    <span x-show="item.harga_total > 0 && item.qty > 0">
                                        Modal per Pcs: <strong class="text-green-600">Rp <span x-text="formatNumber(Math.round(item.harga_total / (item.qty * item.isi_per_satuan)))"></span></strong>
                                    </span>
                                </div>
                            </div>
                        </template>
                        <div x-show="form.items.length === 0" class="text-center py-6 text-slate-400 border-2 border-dashed border-slate-200 rounded-xl">
                            Keranjang kosong. Klik "Tambah Baris" untuk mulai memasukkan barang.
                        </div>
                    </div>

                </div>

                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-between items-center flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Grand Total</span>
                        <div class="text-2xl font-black text-slate-800 font-mono">
                            Rp <span x-text="formatNumber(grandTotal)"></span>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" @click="addModalOpen = false" class="px-5 py-2.5 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors shadow-sm">Batal</button>
                        <button type="button" @click="saveConfirmModal = true" :disabled="form.items.length === 0" class="px-6 py-2.5 bg-[#0f172a] disabled:bg-slate-400 hover:bg-slate-800 rounded-lg text-sm font-bold text-white shadow transition-colors flex items-center gap-2">
                            Simpan & Update MAC
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- CUSTOM CONFIRMATION MODAL -->
    <template x-if="saveConfirmModal">
        <div class="fixed inset-0 z-[150] flex items-center justify-center">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="saveConfirmModal = false"></div>
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm relative z-10 m-4 overflow-hidden transform transition-all border border-slate-200">
                <div class="p-8 text-center">
                    <div class="w-20 h-20 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 mb-2 tracking-tight">Konfirmasi Simpan</h3>
                    <p class="text-sm text-slate-500 leading-relaxed mb-8">
                        Apakah Anda yakin data pembelian ini sudah benar? <br>
                        <span class="font-bold text-slate-700" x-text="form.status === 'selesai' ? 'Stok dan Harga Modal akan langsung diperbarui.' : 'Transaksi akan dicatat sebagai Pending (Stok tidak berubah).'"></span>
                    </p>
                    <div class="flex flex-col gap-2">
                        <button type="button" @click="submitPurchaseForm()" class="w-full py-3 bg-[#0f172a] hover:bg-slate-800 rounded-xl text-sm font-black text-white shadow-lg transition-all transform active:scale-95">
                            Ya, Simpan Sekarang
                        </button>
                        <button type="button" @click="saveConfirmModal = false" class="w-full py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-50 transition-colors">
                            Periksa Kembali
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- CUSTOM RECEIVE MODAL -->
    <template x-if="receiveConfirmModal">
        <div class="fixed inset-0 z-[150] flex items-center justify-center">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="receiveConfirmModal = false"></div>
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm relative z-10 m-4 overflow-hidden transform transition-all border border-slate-200">
                <div class="p-8 text-center">
                    <div class="w-20 h-20 bg-green-50 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 mb-2 tracking-tight">Terima Barang</h3>
                    <p class="text-sm text-slate-500 leading-relaxed mb-8">
                        Konfirmasi penerimaan untuk <strong x-text="receiveData.no_transaksi"></strong>? <br>
                        <span class="font-bold text-slate-700">Stok dan Harga Modal akan segera diperbarui.</span>
                    </p>
                    <div class="flex flex-col gap-2">
                        <form :action="`/pembelian/${receiveData.id}/receive`" method="POST">
                            @csrf
                            <button type="submit" class="w-full py-3 bg-green-600 hover:bg-green-700 rounded-xl text-sm font-black text-white shadow-lg transition-all transform active:scale-95">
                                Ya, Terima Barang
                            </button>
                        </form>
                        <button type="button" @click="receiveConfirmModal = false" class="w-full py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-50 transition-colors">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- VIEW MODAL -->
    <div x-show="viewModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center" style="display: none;">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="viewModalOpen = false" x-transition.opacity></div>
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl relative z-10 m-4 overflow-hidden transform transition-all flex flex-col max-h-[85vh]" x-transition>
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Detail Pembelian <span x-text="viewData.no_transaksi" class="text-blue-600"></span></h3>
                </div>
                <button @click="viewModalOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-lg hover:bg-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="p-6 overflow-y-auto flex-1">
                <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
                    <div>
                        <p class="text-slate-500 font-bold mb-1">Supplier</p>
                        <p class="font-bold text-slate-800" x-text="viewData.supplier?.company_name"></p>
                    </div>
                    <div>
                        <p class="text-slate-500 font-bold mb-1">Tanggal</p>
                        <p class="font-bold text-slate-800" x-text="viewData.tgl_pembelian"></p>
                    </div>
                </div>
                <h4 class="text-xs font-black text-slate-700 uppercase tracking-widest mb-3">Item Dibeli</h4>
                <div class="border border-slate-200 rounded-lg overflow-hidden">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                            <tr>
                                <th class="p-3 font-bold">Produk</th>
                                <th class="p-3 font-bold text-center">Qty</th>
                                <th class="p-3 font-bold text-right">Harga Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="item in viewData.details" :key="item.id">
                                <tr>
                                    <td class="p-3">
                                        <div class="font-bold text-slate-800" x-text="item.product?.nama"></div>
                                        <div class="text-[10px] text-slate-500" x-text="`${item.qty} ${item.satuan} (Isi ${item.isi_per_satuan})`"></div>
                                    </td>
                                    <td class="p-3 text-center font-bold" x-text="item.qty"></td>
                                    <td class="p-3 text-right font-mono font-bold">Rp <span x-text="formatNumber(item.harga_total)"></span></td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot class="bg-slate-50 border-t border-slate-200">
                            <tr>
                                <td colspan="2" class="p-3 text-right font-black uppercase text-xs">Total Pembelian</td>
                                <td class="p-3 text-right font-mono font-black text-blue-600 text-base">Rp <span x-text="formatNumber(viewData.total_pembelian)"></span></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- DELETE MODAL -->
    <template x-if="deleteModalOpen">
        <div class="fixed inset-0 z-[110] flex items-center justify-center">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="deleteModalOpen = false"></div>
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md relative z-10 m-4 overflow-hidden transform transition-all">
                <div class="p-6 text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2">Hapus Transaksi?</h3>
                    <p class="text-sm text-slate-500 mb-6">
                        Menghapus <strong x-text="deleteData.no_transaksi"></strong> akan mengembalikan (reverse) stok barang dan menghapus tagihan terkait. Tindakan ini tidak bisa dibatalkan.
                    </p>
                    <div class="flex gap-3">
                        <button @click="deleteModalOpen = false" class="flex-1 px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors">Batal</button>
                        <form :action="`/pembelian/${deleteData.id}`" method="POST" class="flex-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 rounded-lg text-sm font-bold text-white shadow-sm transition-colors">Ya, Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
@push('scripts')
<script>
function pembelianManager() {
    return {
        addModalOpen: false, 
        viewModalOpen: false,
        deleteModalOpen: false,
        saveConfirmModal: false,
        receiveConfirmModal: false,
        viewData: {},
        deleteData: {},
        receiveData: {},
        products: @json($products),
        form: {
            supplier_id: "",
            tgl_pembelian: new Date().toISOString().split("T")[0],
            jatuh_tempo: new Date().toISOString().split("T")[0],
            status: "selesai",
            catatan: "",
            items: []
        },
        addItem() {
            this.form.items.push({
                produk_id: "",
                satuan: "",
                qty: 1,
                isi_per_satuan: 1,
                harga_total: 0,
                available_units: [],
                searchOpen: false,
                search: ''
            });
        },
        removeItem(index) {
            this.form.items.splice(index, 1);
        },
        updateProductUnits(index) {
            let pId = this.form.items[index].produk_id;
            let product = this.products.find(p => p.id == pId);
            if (product) {
                let baseUnitName = product.unit || "Pcs";
                let unitsMap = new Map();
                
                // Add base unit
                unitsMap.set(`${baseUnitName.toLowerCase()}_1`, { 
                    nama: baseUnitName, 
                    label: `${baseUnitName} (Isi 1)`, 
                    isi: 1 
                });

                if (product.units && product.units.length > 0) {
                    product.units.forEach(u => {
                        let isiNum = Number(u.isi);
                        let key = `${u.nama.toLowerCase()}_${isiNum}`;
                        unitsMap.set(key, { 
                            nama: u.nama, 
                            label: `${u.nama} (Isi ${isiNum})`, 
                            isi: isiNum 
                        });
                    });
                }
                
                let units = Array.from(unitsMap.values());
                this.form.items[index].available_units = units;
                this.form.items[index].satuan = units[0].nama;
                this.form.items[index].isi_per_satuan = units[0].isi;
                this.form.items[index].harga_total = product.harga_beli * 1;
            } else {
                this.form.items[index].available_units = [];
            }
        },
        updateUnitIsi(index) {
            let selectedUnitName = this.form.items[index].satuan;
            let unit = this.form.items[index].available_units.find(u => u.nama == selectedUnitName);
            if (unit) {
                this.form.items[index].isi_per_satuan = unit.isi;
            }
        },
        get grandTotal() {
            return this.form.items.reduce((total, item) => total + (Number(item.harga_total) || 0), 0);
        },
        openViewModal(pembelian) {
            this.viewData = pembelian;
            this.viewModalOpen = true;
        },
        openDeleteModal(pembelian) {
            this.deleteData = pembelian;
            this.deleteModalOpen = true;
        },
        confirmReceive(pembelian) {
            this.receiveData = pembelian;
            this.receiveConfirmModal = true;
        },
        submitPurchaseForm() {
            document.getElementById('purchaseForm').submit();
        },
        formatNumber(num) {
            return new Intl.NumberFormat("id-ID").format(num);
        }
    }
}
</script>
@endpush
@endsection
