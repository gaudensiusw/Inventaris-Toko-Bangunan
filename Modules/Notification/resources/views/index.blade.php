@extends('layouts.app')

@section('title', 'Toko Bangunan - Notifikasi')
@section('header_title', 'Notifikasi')

@section('content')
<div x-data="notificationComponent" class="max-w-6xl mx-auto space-y-8">

    <div class="mb-4">
        <h3 class="text-lg font-bold text-slate-800">Filter Notifikasi</h3>
        <p class="text-sm text-slate-500">Klik kotak di bawah untuk menyaring notifikasi berdasarkan kategori.</p>
    </div>

    <!-- Category Boxes -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Semua -->
        <button @click="setCategory('all')" 
            :class="activeCategory === 'all' ? 'ring-2 ring-slate-500 bg-slate-50 border-slate-300' : 'bg-white hover:bg-slate-50 border-slate-200'"
            class="p-4 rounded-2xl border shadow-sm transition-all text-left group relative overflow-hidden">
            <div x-show="activeCategory === 'all'" class="absolute top-2 right-2">
                <div class="w-4 h-4 bg-slate-500 text-white rounded-full flex items-center justify-center">
                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"></path></svg>
                </div>
            </div>
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-slate-100 text-slate-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                </div>
                <span class="text-xl font-black text-slate-800" x-text="notifications.length"></span>
            </div>
            <h4 class="font-bold text-slate-700 text-sm">Semua</h4>
            <p class="text-[10px] text-slate-500 mt-1" x-text="activeCategory === 'all' ? 'Filter aktif' : 'Lihat semua'"></p>
        </button>

        <!-- Stok Rendah -->
        <button @click="setCategory('stok_rendah')" 
            :class="activeCategory === 'stok_rendah' ? 'ring-2 ring-red-500 bg-red-50 border-red-300' : 'bg-white hover:bg-red-50 border-red-200'"
            class="p-4 rounded-2xl border shadow-sm transition-all text-left group relative overflow-hidden">
            <div x-show="activeCategory === 'stok_rendah'" class="absolute top-2 right-2">
                <div class="w-4 h-4 bg-red-500 text-white rounded-full flex items-center justify-center">
                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"></path></svg>
                </div>
            </div>
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-red-100 text-red-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <span class="text-xl font-black text-red-700" x-text="notifications.filter(n => n.category === 'stok_rendah').length"></span>
            </div>
            <h4 class="font-bold text-slate-700 text-sm">Stok Rendah</h4>
            <p class="text-[10px] text-slate-500 mt-1" x-text="activeCategory === 'stok_rendah' ? 'Filter aktif' : 'Lihat detail'"></p>
        </button>

        <!-- Tagihan -->
        <button @click="setCategory('tagihan')" 
            :class="activeCategory === 'tagihan' ? 'ring-2 ring-orange-500 bg-orange-50 border-orange-300' : 'bg-white hover:bg-orange-50 border-orange-200'"
            class="p-4 rounded-2xl border shadow-sm transition-all text-left group relative overflow-hidden">
            <div x-show="activeCategory === 'tagihan'" class="absolute top-2 right-2">
                <div class="w-4 h-4 bg-orange-500 text-white rounded-full flex items-center justify-center">
                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"></path></svg>
                </div>
            </div>
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-orange-100 text-orange-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <span class="text-xl font-black text-orange-700" x-text="notifications.filter(n => n.category === 'tagihan').length"></span>
            </div>
            <h4 class="font-bold text-slate-700 text-sm">Tagihan</h4>
            <p class="text-[10px] text-slate-500 mt-1" x-text="activeCategory === 'tagihan' ? 'Filter aktif' : 'Lihat jatuh tempo'"></p>
        </button>

        <!-- Sistem -->
        <button @click="setCategory('sistem')" 
            :class="activeCategory === 'sistem' ? 'ring-2 ring-blue-500 bg-blue-50 border-blue-300' : 'bg-white hover:bg-blue-50 border-blue-200'"
            class="p-4 rounded-2xl border shadow-sm transition-all text-left group relative overflow-hidden">
            <div x-show="activeCategory === 'sistem'" class="absolute top-2 right-2">
                <div class="w-4 h-4 bg-blue-500 text-white rounded-full flex items-center justify-center">
                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"></path></svg>
                </div>
            </div>
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <span class="text-xl font-black text-blue-700" x-text="notifications.filter(n => n.category === 'sistem').length"></span>
            </div>
            <h4 class="font-bold text-slate-700 text-sm">Sistem</h4>
            <p class="text-[10px] text-slate-500 mt-1" x-text="activeCategory === 'sistem' ? 'Filter aktif' : 'Lihat pembaruan'"></p>
        </button>

        <!-- Penjualan -->
        <button @click="setCategory('penjualan')" 
            :class="activeCategory === 'penjualan' ? 'ring-2 ring-green-500 bg-green-50 border-green-300' : 'bg-white hover:bg-green-50 border-green-200'"
            class="p-4 rounded-2xl border shadow-sm transition-all text-left group relative overflow-hidden">
            <div x-show="activeCategory === 'penjualan'" class="absolute top-2 right-2">
                <div class="w-4 h-4 bg-green-500 text-white rounded-full flex items-center justify-center">
                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"></path></svg>
                </div>
            </div>
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-green-100 text-green-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                </div>
                <span class="text-xl font-black text-green-700" x-text="notifications.filter(n => n.category === 'penjualan').length"></span>
            </div>
            <h4 class="font-bold text-slate-700 text-sm">Penjualan</h4>
            <p class="text-[10px] text-slate-500 mt-1" x-text="activeCategory === 'penjualan' ? 'Filter aktif' : 'Lihat transaksi'"></p>
        </button>
    </div>

    <!-- Notification List -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
            <h3 class="font-bold text-slate-800">Daftar Notifikasi</h3>
            <button @click="markAllAsRead" 
                class="text-sm font-bold text-blue-600 hover:text-blue-700 transition-colors">Tandai semua dibaca</button>
        </div>

        <div class="divide-y divide-slate-50">
            <template x-for="(notif, index) in filteredNotifications" :key="index">
                <div class="p-8 hover:bg-slate-50/50 transition-all flex gap-6 group">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0"
                        :class="{
                            'bg-red-50 text-red-600': notif.category === 'stok_rendah',
                            'bg-orange-50 text-orange-600': notif.category === 'tagihan',
                            'bg-blue-50 text-blue-600': notif.category === 'sistem',
                            'bg-green-50 text-green-600': notif.category === 'penjualan'
                        }">
                        <svg x-show="notif.category === 'stok_rendah'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        <svg x-show="notif.category === 'tagihan'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <svg x-show="notif.category === 'sistem'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <svg x-show="notif.category === 'penjualan'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-center mb-1.5">
                            <span class="font-black text-xs uppercase tracking-widest" 
                                :class="{
                                    'text-red-600': notif.category === 'stok_rendah',
                                    'text-orange-600': notif.category === 'tagihan',
                                    'text-blue-600': notif.category === 'sistem',
                                    'text-green-600': notif.category === 'penjualan'
                                }"
                                x-text="notif.type"></span>
                            <span class="text-[11px] text-slate-400 font-bold" x-text="notif.time"></span>
                        </div>
                        <p class="text-[15px] text-slate-700 leading-relaxed font-medium" x-text="notif.message"></p>
                        <div class="mt-3 flex items-center gap-2">
                            <button @click="markAsRead(index)" x-show="notif.unread" class="text-xs font-bold text-slate-500 hover:text-[#2563eb] transition-colors">Tandai dibaca</button>
                            <span x-show="notif.unread" class="w-1 h-1 bg-slate-300 rounded-full"></span>
                            <button @click="deleteNotification(index)" class="text-xs font-bold text-slate-500 hover:text-red-500 transition-colors">Hapus</button>
                        </div>
                    </div>
                    <div x-show="notif.unread" class="w-2.5 h-2.5 bg-blue-500 rounded-full mt-2 ring-4 ring-blue-100"></div>
                </div>
            </template>

            <!-- Empty State -->
            <div x-show="filteredNotifications.length === 0" class="py-20 text-center">
                <div class="w-20 h-20 bg-slate-100 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0l-8 8-8-8"></path></svg>
                </div>
                <h4 class="text-lg font-bold text-slate-800">Tidak ada notifikasi</h4>
                <p class="text-sm text-slate-500 mt-1">Kategori ini tidak memiliki notifikasi saat ini.</p>
            </div>
        </div>
        
        <div x-show="filteredNotifications.length > 0" class="p-6 border-t border-slate-50 bg-slate-50/10 text-center">
            <button class="px-6 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-bold hover:bg-slate-50 transition-all shadow-sm">
                Muat lebih banyak...
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('notificationComponent', () => ({
            activeCategory: new URLSearchParams(window.location.search).get('category') || 'all',
            notifications: [],
            deletedNotifs: JSON.parse(localStorage.getItem('deletedNotifs') || '[]'),
            readNotifs: JSON.parse(localStorage.getItem('readNotifs') || '[]'),
            
            init() {
                const rawNotifs = @json($notifications);
                this.notifications = rawNotifs.filter(n => !this.deletedNotifs.includes(n.message)).map(n => {
                    if (this.readNotifs.includes(n.message)) {
                        n.unread = false;
                    }
                    return n;
                });
            },

            setCategory(cat) {
                this.activeCategory = cat;
                const url = new URL(window.location);
                url.searchParams.set('category', cat);
                window.history.pushState({}, '', url);
            },

            get filteredNotifications() {
                if (this.activeCategory === 'all') return this.notifications;
                return this.notifications.filter(n => n.category === this.activeCategory);
            },

            markAsRead(index) {
                const notif = this.filteredNotifications[index];
                notif.unread = false;
                if (!this.readNotifs.includes(notif.message)) {
                    this.readNotifs.push(notif.message);
                    localStorage.setItem('readNotifs', JSON.stringify(this.readNotifs));
                }
            },

            deleteNotification(index) {
                const notif = this.filteredNotifications[index];
                this.deletedNotifs.push(notif.message);
                localStorage.setItem('deletedNotifs', JSON.stringify(this.deletedNotifs));
                this.notifications = this.notifications.filter(n => n.message !== notif.message);
            },

            markAllAsRead() {
                fetch("{{ route('notification.markAllRead') }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Content-Type": "application/json",
                        "Accept": "application/json"
                    }
                }).then(() => {
                    this.notifications.forEach(n => {
                        n.unread = false;
                        if (!this.readNotifs.includes(n.message)) {
                            this.readNotifs.push(n.message);
                        }
                    });
                    localStorage.setItem('readNotifs', JSON.stringify(this.readNotifs));
                    window.location.reload();
                });
            }
        }));
    });
</script>
@endpush
