@extends('layouts.app')

@section('title', 'Toko Bangunan - Notifikasi')
@section('header_title', 'Notifikasi')

@section('content')
<div x-data='{ 
    activeCategory: new URLSearchParams(window.location.search).get("category") || "all",
    setCategory(cat) {
        this.activeCategory = cat;
        const url = new URL(window.location);
        url.searchParams.set("category", cat);
        window.history.pushState({}, "", url);
    },
    notifications: [],
    deletedNotifs: JSON.parse(localStorage.getItem("deletedNotifs") || "[]"),
    readNotifs: JSON.parse(localStorage.getItem("readNotifs") || "[]"),
    
    init() {
        const rawNotifs = @json($notifications);
        this.notifications = rawNotifs.filter(n => !this.deletedNotifs.includes(n.message)).map(n => {
            if (this.readNotifs.includes(n.message)) {
                n.unread = false;
            }
            return n;
        });
    },

    get filteredNotifications() {
        var cat = this.activeCategory;
        if (cat === "all") return this.notifications;
        return this.notifications.filter(n => n.category === cat);
    },

    markAsRead(index) {
        const notif = this.filteredNotifications[index];
        notif.unread = false;
        if (!this.readNotifs.includes(notif.message)) {
            this.readNotifs.push(notif.message);
            localStorage.setItem("readNotifs", JSON.stringify(this.readNotifs));
        }
    },

    deleteNotification(index) {
        const notif = this.filteredNotifications[index];
        this.deletedNotifs.push(notif.message);
        localStorage.setItem("deletedNotifs", JSON.stringify(this.deletedNotifs));
        this.notifications = this.notifications.filter(n => n.message !== notif.message);
    },

    markAllAsRead() {
        this.notifications.forEach(n => {
            n.unread = false;
            if (!this.readNotifs.includes(n.message)) {
                this.readNotifs.push(n.message);
            }
        });
        localStorage.setItem("readNotifs", JSON.stringify(this.readNotifs));
    }
}' class="max-w-6xl mx-auto space-y-8">

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
            :class="activeCategory === 'stok_rendah' ? 'ring-2 ring-red-500 bg-red-50 border-red-300' : 'bg-white hover:bg-slate-50 border-slate-200'"
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
                <span class="text-xl font-black text-slate-800" x-text="notifications.filter(n => n.category === 'stok_rendah').length"></span>
            </div>
            <h4 class="font-bold text-slate-700 text-sm">Stok Rendah</h4>
            <p class="text-[10px] text-slate-500 mt-1" x-text="activeCategory === 'stok_rendah' ? 'Filter aktif' : 'Klik utk filter'"></p>
        </button>

        <!-- Tagihan -->
        <button @click="setCategory('tagihan')" 
            :class="activeCategory === 'tagihan' ? 'ring-2 ring-orange-500 bg-orange-50 border-orange-300' : 'bg-white hover:bg-slate-50 border-slate-200'"
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
                <span class="text-xl font-black text-slate-800" x-text="notifications.filter(n => n.category === 'tagihan').length"></span>
            </div>
            <h4 class="font-bold text-slate-700 text-sm">Tagihan</h4>
            <p class="text-[10px] text-slate-500 mt-1" x-text="activeCategory === 'tagihan' ? 'Filter aktif' : 'Klik utk filter'"></p>
        </button>

        <!-- Sistem -->
        <button @click="setCategory('sistem')" 
            :class="activeCategory === 'sistem' ? 'ring-2 ring-blue-500 bg-blue-50 border-blue-300' : 'bg-white hover:bg-slate-50 border-slate-200'"
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
                <span class="text-xl font-black text-slate-800" x-text="notifications.filter(n => n.category === 'sistem').length"></span>
            </div>
            <h4 class="font-bold text-slate-700 text-sm">Sistem</h4>
            <p class="text-[10px] text-slate-500 mt-1" x-text="activeCategory === 'sistem' ? 'Filter aktif' : 'Klik utk filter'"></p>
        </button>

        <!-- Penjualan -->
        <button @click="setCategory('penjualan')" 
            :class="activeCategory === 'penjualan' ? 'ring-2 ring-green-500 bg-green-50 border-green-300' : 'bg-white hover:bg-slate-50 border-slate-200'"
            class="p-4 rounded-2xl border shadow-sm transition-all text-left group relative overflow-hidden">
            <div x-show="activeCategory === 'penjualan'" class="absolute top-2 right-2">
                <div class="w-4 h-4 bg-green-500 text-white rounded-full flex items-center justify-center">
                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"></path></svg>
                </div>
            </div>
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-green-100 text-green-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
                <span class="text-xl font-black text-slate-800" x-text="notifications.filter(n => n.category === 'penjualan').length"></span>
            </div>
            <h4 class="font-bold text-slate-700 text-sm">Penjualan</h4>
            <p class="text-[10px] text-slate-500 mt-1" x-text="activeCategory === 'penjualan' ? 'Filter aktif' : 'Klik utk filter'"></p>
        </button>
    </div>

    <!-- Notification List Container -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden border-t-4 transition-all duration-500"
        :class="{
            'border-t-slate-500': activeCategory === 'all',
            'border-t-red-500 shadow-red-100': activeCategory === 'stok_rendah',
            'border-t-orange-500 shadow-orange-100': activeCategory === 'tagihan',
            'border-t-blue-500 shadow-blue-100': activeCategory === 'sistem',
            'border-t-green-500 shadow-green-100': activeCategory === 'penjualan'
        }">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <div class="flex items-center gap-3">
                <h3 class="text-lg font-bold text-slate-800">Daftar Notifikasi</h3>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full animate-pulse"
                        :class="{
                            'bg-slate-400': activeCategory === 'all',
                            'bg-red-500': activeCategory === 'stok_rendah',
                            'bg-orange-500': activeCategory === 'tagihan',
                            'bg-blue-500': activeCategory === 'sistem',
                            'bg-green-500': activeCategory === 'penjualan'
                        }"></span>
                    <span class="text-[11px] font-black uppercase tracking-[0.2em]" 
                        :class="{
                            'text-slate-500': activeCategory === 'all',
                            'text-red-600': activeCategory === 'stok_rendah',
                            'text-orange-600': activeCategory === 'tagihan',
                            'text-blue-600': activeCategory === 'sistem',
                            'text-green-600': activeCategory === 'penjualan'
                        }"
                        x-text="activeCategory === 'all' ? 'Semua Riwayat' : activeCategory.replace('_', ' ') + ' Terfilter'"></span>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <button @click="activeCategory = 'all'" x-show="activeCategory !== 'all'" class="text-slate-500 hover:text-slate-700 text-sm font-bold flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    Reset Filter
                </button>
                <button @click="markAllAsRead()" class="text-[#2563eb] hover:text-blue-800 text-sm font-bold">Tandai semua dibaca</button>
            </div>
        </div>

        <div class="divide-y divide-slate-100 min-h-[300px]">
            <template x-for="(notif, index) in filteredNotifications" :key="index">
                <div class="p-6 hover:bg-slate-50/80 transition-all flex items-start gap-5" 
                    :class="notif.unread ? 'bg-blue-50/30' : ''">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-sm"
                        :class="{
                            'bg-red-100 text-red-600': notif.category === 'stok_rendah',
                            'bg-orange-100 text-orange-600': notif.category === 'tagihan',
                            'bg-blue-100 text-blue-600': notif.category === 'sistem',
                            'bg-green-100 text-green-600': notif.category === 'penjualan'
                        }">
                        <template x-if="notif.category === 'stok_rendah'">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </template>
                        <template x-if="notif.category === 'tagihan'">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </template>
                        <template x-if="notif.category === 'sistem'">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </template>
                        <template x-if="notif.category === 'penjualan'">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        </template>
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
                <button @click="activeCategory = 'all'" class="mt-4 text-[#2563eb] font-bold text-sm">Lihat Semua Notifikasi</button>
            </div>
        </div>
        
        <div class="p-6 text-center border-t border-slate-100 bg-slate-50/50">
            <button class="bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 px-6 py-2 rounded-xl text-sm font-bold shadow-sm transition-all">Muat lebih banyak...</button>
        </div>
    </div>
</div>
@endsection
