@extends('layouts.app')

@section('title', 'Toko Bangunan - Manajemen Stok')
@section('header_title', 'Manajemen Stok')

@section('content')
<div x-data="{ 
    isSubmitting: false,
    modalOpen: false, 
    showConfirm: false,
    editModalOpen: false,
    modalType: 'in',
    selectedProduct: '',
    qty: 0,
    keterangan: '',
    editId: null,
    editKeterangan: '',
    openModal(type) {
        this.modalType = type;
        this.modalOpen = true;
    },
    openEditModal(id, currentKeterangan) {
        this.editId = id;
        this.editKeterangan = currentKeterangan || '';
        this.editModalOpen = true;
    }
}">
    <!-- Alert Messages -->
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Action Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div @click="openModal('in')" class="bg-white border border-slate-200 hover:border-green-300 hover:shadow-md cursor-pointer rounded-xl p-5 transition-all flex items-center gap-4 group">
            <div class="w-12 h-12 bg-green-50 text-green-500 rounded-full flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
            </div>
            <div>
                <h3 class="font-bold text-slate-800 text-lg">Barang Masuk (In)</h3>
                <p class="text-xs text-slate-500 mt-0.5">Catat penambahan stok barang</p>
            </div>
        </div>
        
        <div @click="openModal('out')" class="bg-white border border-slate-200 hover:border-red-300 hover:shadow-md cursor-pointer rounded-xl p-5 transition-all flex items-center gap-4 group">
            <div class="w-12 h-12 bg-red-50 text-red-500 rounded-full flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
            </div>
            <div>
                <h3 class="font-bold text-slate-800 text-lg">Barang Keluar (Out)</h3>
                <p class="text-xs text-slate-500 mt-0.5">Catat pengeluaran stok barang</p>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 bg-slate-50">
            <h3 class="text-base font-bold text-slate-800">Riwayat Pergerakan Stok (100 Terakhir)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-white text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="p-4 font-semibold uppercase tracking-wider text-xs">Waktu</th>
                        <th class="p-4 font-semibold uppercase tracking-wider text-xs">Tipe</th>
                        <th class="p-4 font-semibold uppercase tracking-wider text-xs">Produk</th>
                        <th class="p-4 font-semibold uppercase tracking-wider text-xs">Jumlah</th>
                        <th class="p-4 font-semibold uppercase tracking-wider text-xs">Keterangan</th>
                        <th class="p-4 font-semibold uppercase tracking-wider text-xs w-10 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $trx)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-slate-600 text-xs">{{ $trx->created_at->format('d M Y, H:i') }}</td>
                        <td class="p-4">
                            @if($trx->tipe == 'in')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700 uppercase border border-green-200">Masuk</span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 uppercase border border-red-200">Keluar</span>
                            @endif
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-slate-800">{{ $trx->product->nama ?? 'Produk Dihapus' }}</div>
                            <div class="text-[10px] text-slate-500">SKU: {{ $trx->product->sku ?? '-' }}</div>
                        </td>
                        <td class="p-4 font-black {{ $trx->tipe == 'in' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $trx->tipe == 'in' ? '+' : '-' }}{{ number_format($trx->qty, 2, ',', '.') }}
                        </td>
                        <td class="p-4 text-slate-500 text-xs italic">{{ $trx->keterangan ?: '-' }}</td>
                        <td class="p-4 text-center whitespace-nowrap">
                            <button @click="openEditModal({{ $trx->id }}, {{ json_encode($trx->keterangan) }})" class="text-slate-400 hover:text-blue-500 transition-colors p-1 rounded hover:bg-blue-50" title="Edit Catatan">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-12 text-center text-slate-500 italic">Belum ada riwayat transaksi stok</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Stock Modal -->
    <div x-show="modalOpen" class="fixed inset-0 z-[100] flex items-center justify-center" style="display: none;">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="modalOpen = false" x-transition.opacity></div>
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md relative z-10 m-4 overflow-hidden transform transition-all" x-transition>
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between" :class="modalType == 'in' ? 'bg-green-50/50' : 'bg-red-50/50'">
                <div>
                    <h3 class="text-lg font-bold text-slate-800" x-text="modalType == 'in' ? 'Barang Masuk (In)' : 'Barang Keluar (Out)'"></h3>
                    <p class="text-xs text-slate-500 mt-0.5" x-text="modalType == 'in' ? 'Tambah persediaan barang' : 'Catat pengeluaran barang'"></p>
                </div>
                <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-lg hover:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form action="{{ route('stockmanagement.store') }}" method="POST" @submit="if(isSubmitting) { $event.preventDefault(); return false; } isSubmitting = true;">
                @csrf
                <input type="hidden" name="tipe" :value="modalType">
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Pilih Produk <span class="text-red-500">*</span></label>
                        <select name="produk_id" required class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                            <option value="">Pilih Produk...</option>
                            @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->nama }} (Stok: {{ $p->stok }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Jumlah <span class="text-red-500">*</span></label>
                        <input type="number" name="qty" required min="0.01" step="0.01" placeholder="0" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Keterangan</label>
                        <textarea name="keterangan" placeholder="Contoh: Restock supplier, Pengambilan untuk toko, dll" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 resize-none h-20"></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors shadow-sm">Batal</button>
                    <button type="button" @click="showConfirm = true" class="px-5 py-2 rounded-lg text-sm font-bold text-white shadow transition-colors"
                            :class="modalType == 'in' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'">
                        Simpan <span x-text="modalType == 'in' ? 'Barang Masuk' : 'Barang Keluar'"></span>
                    </button>
                </div>

                <!-- DOUBLE CONFIRMATION MODAL -->
                <div x-show="showConfirm" class="fixed inset-0 z-[110] flex items-center justify-center" style="display: none;">
                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showConfirm = false"></div>
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm relative z-10 m-4 overflow-hidden border border-slate-200">
                        <div class="p-8 text-center">
                            <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 ring-8"
                                :class="modalType == 'in' ? 'bg-green-50 text-green-600 ring-green-50/50' : 'bg-red-50 text-red-600 ring-red-50/50'">
                                <template x-if="modalType == 'in'">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </template>
                                <template x-if="modalType == 'out'">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </template>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-2">Simpan Penyesuaian?</h3>
                            <p class="text-slate-500 leading-relaxed">Pastikan jumlah barang yang dimasukkan sudah sesuai dengan fisik di gudang.</p>
                        </div>
                        <div class="px-6 py-5 bg-slate-50 border-t border-slate-100 flex gap-3">
                            <button type="button" @click="showConfirm = false" :disabled="isSubmitting" class="flex-1 py-3 bg-white border border-slate-300 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all shadow-sm">Batal</button>
                            <button type="submit" :disabled="isSubmitting" class="flex-1 py-3 text-white rounded-xl text-sm font-bold transition-all shadow-lg"
                                :class="(modalType == 'in' ? 'bg-green-600 hover:bg-green-700 shadow-green-200 ' : 'bg-red-600 hover:bg-red-700 shadow-red-200 ') + (isSubmitting ? 'opacity-70 cursor-wait' : '')">
                                <span x-show="!isSubmitting">Ya, Simpan</span>
                                <span x-show="isSubmitting" style="display: none;">Menyimpan...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Note Modal -->
    <div x-show="editModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center" style="display: none;">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="editModalOpen = false" x-transition.opacity></div>
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm relative z-10 m-4 overflow-hidden transform transition-all" x-transition>
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-blue-50/50">
                <h3 class="text-base font-bold text-slate-800">Edit Catatan</h3>
                <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-lg hover:bg-slate-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form :action="'{{ url('stock-management/update-note') }}/' + editId" method="POST" @submit="if(isSubmitting) { $event.preventDefault(); return false; } isSubmitting = true;">
                @csrf
                @method('PUT')
                <div class="p-5">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Keterangan Baru</label>
                    <textarea name="keterangan" x-model="editKeterangan" placeholder="Masukkan keterangan..." class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 resize-none h-24"></textarea>
                </div>
                <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" @click="editModalOpen = false" :disabled="isSubmitting" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-xs font-bold text-slate-600 hover:bg-slate-50 transition-colors shadow-sm">Batal</button>
                    <button type="submit" :disabled="isSubmitting" :class="{'opacity-70 cursor-wait': isSubmitting}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-xs font-bold hover:bg-blue-700 transition-colors shadow">
                        <span x-show="!isSubmitting">Simpan Catatan</span>
                        <span x-show="isSubmitting" style="display: none;">Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
