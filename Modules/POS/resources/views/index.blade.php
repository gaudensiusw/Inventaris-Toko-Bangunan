@extends('layouts.app')

@section('title', 'Toko Bangunan - POS / Cashier')
@section('header_title', 'POS / Cashier')

@section('content')
<div x-data="posSystem()" x-init="init()" class="flex flex-col lg:flex-row gap-6 h-[calc(100vh-140px)]">

    <!-- ── LEFT PANEL: Product Grid ─────────────────────────── -->
    <div class="flex-1 flex flex-col min-h-0 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <!-- Search & Filter -->
        <div class="p-4 border-b border-slate-200 flex flex-wrap items-center gap-3 bg-slate-50">
            <div class="flex-1 min-w-[200px] relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" x-model="search" placeholder="Cari Barang..." class="block w-full pl-10 pr-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 bg-white placeholder-slate-400">
            </div>
            <select x-model="categoryFilter" class="border border-slate-300 rounded-lg text-sm py-2 px-3 bg-white text-slate-700 focus:ring-blue-500 focus:border-blue-500 min-w-[150px]">
                <option value="">Semua Kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->nama }}</option>
                @endforeach
            </select>
        </div>

        <!-- Grid -->
        <div class="flex-1 overflow-y-auto p-4 bg-slate-50">
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4">
                <template x-for="product in filteredProducts" :key="product.id">
                    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden hover:shadow-md transition-shadow flex flex-col group cursor-pointer" @click="addToCart(product)">
                        <div class="h-28 bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center border-b border-slate-100 group-hover:from-blue-50 group-hover:to-blue-100 transition-all">
                            <svg class="w-10 h-10 text-slate-300 group-hover:text-blue-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <div class="p-3 flex-1 flex flex-col">
                            <h3 class="text-sm font-bold text-slate-800 line-clamp-2 leading-tight mb-0.5" x-text="product.nama"></h3>
                            <template x-if="product.merk">
                                <p class="text-[10px] font-black text-blue-600 uppercase tracking-tighter mb-1" x-text="product.merk"></p>
                            </template>
                            <p class="text-[11px] text-slate-500 mb-2">Stok: <span class="font-bold" :class="product.stok <= 10 ? 'text-red-500' : 'text-slate-700'" x-text="product.stok + ' ' + product.unit"></span></p>
                            <div class="mt-auto flex items-center justify-between">
                                <p class="text-sm font-bold text-[#2563eb]" x-text="formatCurrency(product.harga_jual)"></p>
                                <div class="w-6 h-6 bg-green-500 group-hover:bg-green-600 rounded-full flex items-center justify-center transition-colors">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                <template x-if="filteredProducts.length === 0">
                    <div class="col-span-4 py-16 flex flex-col items-center text-slate-400">
                        <svg class="w-14 h-14 mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <p class="text-sm font-medium">Tidak ada produk ditemukan</p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- ── RIGHT PANEL: Order Summary ───────────────────────── -->
    <div class="w-full lg:w-[400px] xl:w-[440px] flex flex-col min-h-0 bg-white border border-slate-200 rounded-xl shadow-sm flex-shrink-0">

        <!-- Header Info -->
        <div class="p-4 border-b border-slate-200 bg-slate-50 space-y-2.5">
            <!-- Customer (free text) -->
            <div class="flex items-center gap-3">
                <label class="text-xs font-bold text-slate-500 uppercase w-24 flex-shrink-0">Pelanggan</label>
                <div class="relative flex-1" @click.away="showCustomerDropdown = false">
                    <input type="text" x-model="nama_pelanggan" 
                        @input="showCustomerDropdown = true"
                        @focus="showCustomerDropdown = true"
                        placeholder="Nama pelanggan..." 
                        class="w-full border border-slate-300 rounded-lg text-sm py-1.5 px-3 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    
                    <!-- Search Results Dropdown -->
                    <div x-show="showCustomerDropdown && filteredCustomersSearch.length > 0" 
                        class="absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden"
                        style="display: none;">
                        <template x-for="c in filteredCustomersSearch" :key="c.id">
                            <button @click="selectCustomer(c)" class="w-full text-left px-4 py-2 hover:bg-blue-50 transition-colors border-b border-slate-50 last:border-0">
                                <div class="font-bold text-xs text-slate-800" x-text="c.nama"></div>
                                <div class="text-[10px] text-slate-400" x-text="(c.kode || '') + ' • ' + (c.telp || '')"></div>
                            </button>
                        </template>
                    </div>

                    <template x-if="metode_pembayaran === 'Bon' && nama_pelanggan">
                        <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[9px] bg-amber-100 text-amber-700 font-bold px-1.5 py-0.5 rounded">AUTO-SAVE</span>
                    </template>
                </div>
            </div>
            <!-- Phone Number (Optional) -->
            <div class="flex items-center gap-3">
                <label class="text-xs font-bold text-slate-500 uppercase w-24 flex-shrink-0">No. Telp</label>
                <input type="text" x-model="telp_pelanggan" placeholder="08..." 
                    class="flex-1 border border-slate-300 rounded-lg text-sm py-1.5 px-3 focus:ring-blue-500 focus:border-blue-500 bg-white">
            </div>
            <!-- Jatuh Tempo (Only for Bon) -->
            <template x-if="metode_pembayaran === 'Bon'">
                <div class="flex items-center gap-3">
                    <label class="text-xs font-bold text-slate-500 uppercase w-24 flex-shrink-0">Jatuh Tempo</label>
                    <input type="date" x-model="jatuh_tempo" 
                        class="flex-1 border border-slate-300 rounded-lg text-sm py-1.5 px-3 focus:ring-blue-500 focus:border-blue-500 bg-white">
                </div>
            </template>
            <!-- Date & Time -->
            <div class="flex items-center gap-3">
                <label class="text-xs font-bold text-slate-500 uppercase w-24 flex-shrink-0">Waktu</label>
                <input type="text" :value="currentTime" class="flex-1 border border-slate-300 rounded-lg text-sm py-1.5 px-3 bg-slate-100 text-slate-700 font-mono text-xs" readonly>
            </div>
        </div>

        <!-- Cart Items -->
        <div class="flex-1 overflow-y-auto px-4 py-2">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 text-xs">
                        <th class="pb-2 font-semibold">Nama Barang</th>
                        <th class="pb-2 font-semibold text-center w-24">Jml</th>
                        <th class="pb-2 font-semibold text-right">Total</th>
                        <th class="pb-2 w-6"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <template x-for="(item, index) in cart" :key="item.produk_id">
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-2.5">
                                <div class="font-medium text-slate-800 text-xs leading-tight" x-text="item.nama"></div>
                                <div class="text-[10px] text-slate-400 mt-0.5" x-text="formatCurrency(item.harga) + ' / satuan'"></div>
                            </td>
                            <td class="py-2.5 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button @click="updateQty(index, -1)" class="w-5 h-5 flex items-center justify-center rounded-md bg-slate-100 text-slate-600 hover:bg-red-100 hover:text-red-600 text-xs font-bold transition-colors">−</button>
                                    <span class="w-7 text-center font-bold text-sm" x-text="item.qty"></span>
                                    <button @click="updateQty(index, 1)" class="w-5 h-5 flex items-center justify-center rounded-md bg-slate-100 text-slate-600 hover:bg-green-100 hover:text-green-600 text-xs font-bold transition-colors">+</button>
                                </div>
                            </td>
                            <td class="py-2.5 text-right font-bold text-slate-700 text-xs whitespace-nowrap" x-text="formatCurrency(item.qty * item.harga)"></td>
                            <td class="py-2.5 pl-2 text-right">
                                <button @click="removeFromCart(index)" class="text-slate-300 hover:text-red-500 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <template x-if="cart.length === 0">
                <div class="py-10 flex flex-col items-center text-slate-400">
                    <svg class="w-10 h-10 mb-2 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <p class="text-xs">Belum ada barang di keranjang</p>
                    <p class="text-[10px] text-slate-300 mt-1">Klik produk untuk menambahkan</p>
                </div>
            </template>
        </div>

        <!-- Options + Totals -->
        <div class="px-4 pt-3 pb-2 border-t border-slate-100 space-y-3 bg-slate-50/50">
            <!-- Catatan -->
            <textarea x-model="catatan" placeholder="Catatan pesanan..." rows="2" class="w-full border border-slate-300 rounded-lg text-xs p-2 focus:ring-blue-500 focus:border-blue-500 resize-none bg-white"></textarea>

            <!-- Pengiriman -->
            <div class="flex items-center justify-between">
                <label class="text-xs font-bold text-slate-500 uppercase">Pengiriman</label>
                <div class="flex bg-slate-200 rounded-lg p-0.5 gap-0.5">
                    <button @click="opsi_pengiriman = 'Ambil Sendiri'" :class="opsi_pengiriman === 'Ambil Sendiri' ? 'bg-white text-slate-800 shadow' : 'text-slate-500'" class="px-3 py-1 text-[11px] font-bold rounded-md transition-all">Ambil</button>
                    <button @click="opsi_pengiriman = 'Antar'" :class="opsi_pengiriman === 'Antar' ? 'bg-white text-slate-800 shadow' : 'text-slate-500'" class="px-3 py-1 text-[11px] font-bold rounded-md transition-all">Antar</button>
                </div>
            </div>

            <!-- Metode Pembayaran -->
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase block mb-1.5">Pembayaran</label>
                <div class="grid grid-cols-4 gap-1.5">
                    @foreach(['Cash','Card','E-Wallet','Bon'] as $m)
                    <button @click="metode_pembayaran = '{{ $m }}'"
                        :class="metode_pembayaran === '{{ $m }}' ? 'bg-slate-800 text-white border-slate-800 ring-2 ring-slate-800 ring-offset-1' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                        class="py-1.5 text-xs font-bold rounded-lg border transition-all">{{ $m }}</button>
                    @endforeach
                </div>
                <template x-if="metode_pembayaran === 'Bon'">
                    <p class="text-[10px] text-amber-600 mt-1.5 font-medium">⚠ Pembayaran Bon — nama pelanggan akan dicatat secara otomatis</p>
                </template>
            </div>

            <!-- Totals -->
            <div class="border-t border-dashed border-slate-300 pt-2 space-y-1">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Subtotal</span>
                    <span class="font-bold text-slate-700" x-text="formatCurrency(subtotal)"></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Pajak (0%)</span>
                    <span class="font-bold text-slate-700">Rp 0</span>
                </div>
                <div class="flex justify-between items-end pt-1">
                    <span class="text-base font-bold text-slate-800">Total Tagihan</span>
                    <span class="text-2xl font-black text-[#2563eb]" x-text="formatCurrency(total_tagihan)"></span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="p-3 bg-white border-t border-slate-200 flex gap-2">
            <button @click="clearCart()" class="flex-none py-2.5 px-3 rounded-lg text-xs font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
            <button @click="openCheckoutModal()" :disabled="cart.length === 0"
                :class="cart.length === 0 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-green-600 shadow-lg shadow-green-200'"
                class="flex-1 py-2.5 rounded-lg text-sm font-bold bg-green-500 text-white transition-all flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Checkout
                <span x-show="cart.length > 0" class="bg-white/20 text-white text-xs font-bold px-1.5 py-0.5 rounded-full" x-text="cart.length + ' item'"></span>
            </button>
        </div>
    </div>

    <!-- ── CHECKOUT CONFIRMATION MODAL ──────────────────────── -->
    <div x-show="checkoutModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center" style="display: none;">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="!loading && (checkoutModalOpen = false)" x-transition.opacity></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm relative z-10 m-4 overflow-hidden" x-transition>
            <div class="p-6">
                <!-- Icon -->
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-blue-200">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>

                <h3 class="text-lg font-bold text-slate-800 text-center mb-1">Konfirmasi Checkout</h3>
                <p class="text-sm text-slate-500 text-center mb-1">Total: <span class="font-black text-blue-600 text-base" x-text="formatCurrency(total_tagihan)"></span></p>
                <p class="text-xs text-slate-400 text-center mb-5">
                    <span x-text="cart.length + ' item • ' + metode_pembayaran + ' • ' + opsi_pengiriman"></span>
                </p>

                <p class="text-sm font-bold text-slate-700 text-center mb-3">Apakah ingin mencetak struk?</p>

                <div class="space-y-2">
                    <button @click="confirmCheckout(true)" :disabled="loading"
                        class="w-full py-3 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm font-bold text-white shadow shadow-blue-200 transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                        <svg x-show="!loading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span x-show="!loading">Ya, Cetak Struk</span>
                        <span x-show="loading">Memproses...</span>
                    </button>
                    <button @click="confirmCheckout(false)" :disabled="loading"
                        class="w-full py-3 bg-slate-800 hover:bg-slate-900 rounded-xl text-sm font-bold text-white transition-all disabled:opacity-50">
                        <span x-show="!loading">Proses Tanpa Struk</span>
                        <span x-show="loading">Memproses...</span>
                    </button>
                    <button @click="checkoutModalOpen = false" :disabled="loading"
                        class="w-full py-2.5 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl text-sm font-bold text-slate-600 transition-colors disabled:opacity-50">
                        Batal — Kembali ke Kasir
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function posSystem() {
    return {
        products: @json($products),
        cart: [],
        search: '',
        categoryFilter: '',
        nama_pelanggan: '',
        telp_pelanggan: '',
        jatuh_tempo: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        metode_pembayaran: 'Cash',
        opsi_pengiriman: 'Ambil Sendiri',
        catatan: '',
        currentTime: '',
        checkoutModalOpen: false,
        loading: false,

        init() {
            this.updateTime();
            setInterval(() => this.updateTime(), 1000);
        },

        updateTime() {
            const now = new Date();
            const d = now.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            const t = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
            this.currentTime = d + '  ' + t;
        },

        get filteredProducts() {
            return this.products.filter(p => {
                const matchSearch = p.nama.toLowerCase().includes(this.search.toLowerCase());
                const matchCat = this.categoryFilter === '' || p.kategori_id == this.categoryFilter;
                return matchSearch && matchCat;
            });
        },

        get subtotal() {
            return this.cart.reduce((sum, item) => sum + (item.qty * item.harga), 0);
        },

        get total_tagihan() {
            return this.subtotal;
        },

        addToCart(product) {
            const idx = this.cart.findIndex(i => i.produk_id === product.id);
            if (idx > -1) {
                if (this.cart[idx].qty < product.stok) {
                    this.cart[idx].qty++;
                } else {
                    alert(`Stok ${product.nama} hanya ${product.stok} ${product.unit}`);
                }
            } else {
                if (product.stok <= 0) {
                    alert('Stok produk ini habis');
                    return;
                }
                this.cart.push({ produk_id: product.id, nama: product.nama, qty: 1, harga: product.harga_jual });
            }
        },

        removeFromCart(idx) {
            this.cart.splice(idx, 1);
        },

        // Customer Search
        customers: @json($customers),
        showCustomerDropdown: false,
        get filteredCustomersSearch() {
            if (!this.nama_pelanggan) return [];
            return this.customers.filter(c => 
                c.nama.toLowerCase().includes(this.nama_pelanggan.toLowerCase()) || 
                (c.kode && c.kode.toLowerCase().includes(this.nama_pelanggan.toLowerCase()))
            ).slice(0, 5);
        },
        selectCustomer(c) {
            this.nama_pelanggan = c.nama;
            this.telp_pelanggan = c.telp || '';
            const tenor = c.tenor_bayar || 30;
            this.jatuh_tempo = new Date(Date.now() + tenor * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
            this.showCustomerDropdown = false;
        },

        clearCart() {
            if (this.cart.length === 0) return;
            if (confirm('Kosongkan semua item di keranjang?')) {
                this.cart = [];
                this.nama_pelanggan = '';
                this.catatan = '';
            }
        },

        updateQty(idx, delta) {
            const newQty = this.cart[idx].qty + delta;
            const product = this.products.find(p => p.id === this.cart[idx].produk_id);
            if (newQty <= 0) {
                this.removeFromCart(idx);
            } else if (newQty > product.stok) {
                alert(`Stok tidak mencukupi. Tersedia: ${product.stok} ${product.unit}`);
            } else {
                this.cart[idx].qty = newQty;
            }
        },

        formatCurrency(v) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v);
        },

        openCheckoutModal() {
            if (this.cart.length === 0) return;
            this.checkoutModalOpen = true;
        },

        async confirmCheckout(printReceipt) {
            this.loading = true;
            try {
                const resp = await fetch('{{ route('pos.store') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({
                        nama_pelanggan:   this.nama_pelanggan || null,
                        telp_pelanggan:   this.telp_pelanggan || null,
                        jatuh_tempo:      this.metode_pembayaran === 'Bon' ? this.jatuh_tempo : null,
                        items:            this.cart,
                        subtotal:         this.subtotal,
                        pajak:            0,
                        total_tagihan:    this.total_tagihan,
                        metode_pembayaran:this.metode_pembayaran,
                        opsi_pengiriman:  this.opsi_pengiriman,
                        catatan:          this.catatan,
                    })
                });

                const result = await resp.json();

                if (result.success) {
                    if (printReceipt) {
                        window.open(result.receipt_url, '_blank');
                    }
                    window.location.href = '{{ route('pos.index') }}';
                } else {
                    alert('Gagal: ' + result.message);
                    this.loading = false;
                    this.checkoutModalOpen = false;
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan sistem.');
                this.loading = false;
                this.checkoutModalOpen = false;
            }
        }
    }
}
</script>
@endsection
