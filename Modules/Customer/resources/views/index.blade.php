@extends('layouts.app')

@section('title', 'Toko Bangunan - Manajemen Pelanggan')
@section('header_title', 'Pelanggan')

@section('content')
<div x-data="{ 
    addModalOpen: false, 
    editModalOpen: false, 
    deleteModalOpen: false,
    payModalOpen: false,
    editForm: {},
    deleteForm: {},
    payForm: { id: null, total_tagihan: 0, jumlah_bayar: 0, sisa: 0, bayar: 0 },
    searchQuery: '',
    activeFilter: 'semua',
    
    openEditModal(customer) {
        this.editForm = JSON.parse(JSON.stringify(customer));
        this.editModalOpen = true;
    },
    openDeleteModal(customer) {
        this.deleteForm = customer;
        this.deleteModalOpen = true;
    },
    openPayModal(trx) {
        this.payForm = {
            id: trx.id,
            total_tagihan: trx.total_tagihan,
            jumlah_bayar: trx.jumlah_bayar,
            sisa: trx.total_tagihan - trx.jumlah_bayar,
            bayar: trx.total_tagihan - trx.jumlah_bayar
        };
        this.payModalOpen = true;
    }
}">
    <!-- Alert Messages -->
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Header & Stats Row -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Manajemen Pelanggan</h2>
        <p class="text-sm text-slate-500">
            @if(request('filter') === 'hutang')
                Kelola piutang dan transaksi kredit pelanggan
            @else
                Daftar dan kelola seluruh informasi profil pelanggan
            @endif
        </p>
    </div>

    @if(request('filter') === 'hutang')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-12 h-12 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 14h-2v-2h2v2zm0-4h-2V7h2v5z"/></svg>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Piutang</p>
            <h3 class="text-xl font-black text-blue-600">Rp {{ number_format($stats['total_piutang'], 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-12 h-12 text-red-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 14h-2v-2h2v2zm0-4h-2V7h2v5z"/></svg>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Jatuh Tempo</p>
            <h3 class="text-xl font-black text-red-500">Rp {{ number_format($stats['jatuh_tempo'], 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-orange-50 text-orange-600 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Belum Bayar</p>
                <h3 class="text-lg font-bold text-slate-800">{{ $stats['belum_bayar_count'] }} <span class="text-xs text-slate-400 font-normal">transaksi</span></h3>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pelanggan Aktif</p>
                <h3 class="text-lg font-bold text-slate-800">{{ $stats['aktif_count'] }} <span class="text-xs text-slate-400 font-normal">dengan piutang</span></h3>
            </div>
        </div>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Pelanggan</p>
            <h3 class="text-xl font-black text-blue-600">{{ $stats['total_pelanggan'] }} <span class="text-xs text-slate-400 font-normal">orang</span></h3>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Kategori Kontraktor</p>
                <h3 class="text-lg font-bold text-slate-800">{{ $stats['kontraktor_count'] }} <span class="text-xs text-slate-400 font-normal">pelanggan</span></h3>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Kategori Tukang</p>
                <h3 class="text-lg font-bold text-slate-800">{{ $stats['tukang_count'] }} <span class="text-xs text-slate-400 font-normal">pelanggan</span></h3>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Umum / Retail</p>
                <h3 class="text-lg font-bold text-slate-800">{{ $stats['umum_retail_count'] }} <span class="text-xs text-slate-400 font-normal">pelanggan</span></h3>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
        
        <!-- Left: Customer List -->
        <div class="xl:col-span-4 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col h-[700px]">
            <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h3 class="font-bold text-slate-800">Daftar Pelanggan {{ request('filter') === 'hutang' ? '(Hutang)' : '(Semua)' }}</h3>
                <button @click="addModalOpen = true" class="p-1.5 bg-[#0f172a] text-white rounded-lg hover:bg-slate-800 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </button>
            </div>
            <div class="p-4 border-b border-slate-100">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" x-model="searchQuery" placeholder="Cari nama, kode, atau telp..." class="w-full pl-9 pr-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 bg-slate-50">
                </div>
            </div>
            <div class="flex-1 overflow-y-auto divide-y divide-slate-100">
                @foreach($customers as $cust)
                <a href="{{ route('customer.index', ['id' => $cust->id, 'filter' => request('filter')]) }}" 
                   x-show="searchQuery === '' || 
                          '{{ strtolower($cust->nama) }}'.includes(searchQuery.toLowerCase()) || 
                          '{{ strtolower($cust->kode) }}'.includes(searchQuery.toLowerCase()) || 
                          '{{ $cust->telp }}'.includes(searchQuery)"
                   class="block p-4 transition-all hover:bg-blue-50/50 {{ ($selected_customer->id ?? null) == $cust->id ? 'bg-blue-50/80 ring-1 ring-inset ring-blue-100' : '' }}">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="font-bold text-sm text-slate-800">{{ $cust->nama }}</div>
                            <div class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">{{ $cust->kode ?: 'CUST-'.str_pad($cust->id,3,'0',STR_PAD_LEFT) }}</div>
                        </div>
                        @if($cust->total_hutang > $cust->total_dibayar && request('filter') === 'hutang')
                            <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse shadow-lg shadow-red-200"></div>
                        @endif
                    </div>
                    <div class="mt-2 flex items-center justify-between">
                        <div class="text-[10px] font-black px-2 py-0.5 bg-slate-100 text-slate-500 rounded uppercase tracking-widest">{{ $cust->kategori ?: 'Umum' }}</div>
                        @if(request('filter') === 'hutang')
                            <div class="text-xs font-black text-red-500">
                                Rp {{ number_format($cust->total_hutang - $cust->total_dibayar, 0, ',', '.') }}
                            </div>
                        @else
                            <div class="text-xs text-slate-500 font-medium">
                                {{ $cust->telp ?: '-' }}
                            </div>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
        </div>

        <!-- Right: Detail Panel -->
        <div class="xl:col-span-8 space-y-6">
            @if($selected_customer)
            <!-- Customer Detail Header -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
                <div class="flex flex-col md:flex-row justify-between gap-6 mb-8">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-1">
                            <h3 class="text-2xl font-black text-slate-800">{{ $selected_customer->nama }}</h3>
                            <span class="px-2 py-0.5 bg-green-100 text-green-700 text-[10px] font-black rounded-full uppercase tracking-widest">Aktif</span>
                        </div>
                        <p class="text-xs text-slate-400 font-bold uppercase mb-4">{{ $selected_customer->kode ?: 'CUST-'.str_pad($selected_customer->id,3,'0',STR_PAD_LEFT) }}</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-3 gap-x-6 text-sm">
                            <div class="flex items-center gap-3 text-slate-600">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <span>{{ $selected_customer->telp ?: '-' }}</span>
                            </div>
                            <div class="flex items-center gap-3 text-slate-600">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span>{{ $selected_customer->email ?: '-' }}</span>
                            </div>
                            <div class="md:col-span-2 flex items-start gap-3 text-slate-600">
                                <svg class="w-4 h-4 text-slate-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>{{ $selected_customer->alamat ?: '-' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2">
                        <button @click="openEditModal({{ $selected_customer->toJson() }})" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-50 transition-all flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Edit Profil
                        </button>
                        <button @click="openDeleteModal({{ $selected_customer->toJson() }})" class="px-4 py-2 bg-red-50 border border-red-100 text-red-600 text-sm font-bold rounded-xl hover:bg-red-100 transition-all flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            Hapus Pelanggan
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 pt-6 border-t border-slate-100">
                    @if(request('filter') === 'hutang')
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Piutang Berjalan</p>
                            <h4 class="text-xl font-black text-red-600">Rp {{ number_format($selected_customer->transactions->where('status_pembayaran', '!=', 'lunas')->sum(fn($t) => $t->total_tagihan - $t->jumlah_bayar), 0, ',', '.') }}</h4>
                        </div>
                    @else
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Belanja</p>
                            <h4 class="text-xl font-black text-blue-600">Rp {{ number_format($selected_customer->transactions->sum('total_tagihan'), 0, ',', '.') }}</h4>
                        </div>
                    @endif
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Limit Kredit</p>
                        <h4 class="text-xl font-black text-slate-800">Rp {{ number_format($selected_customer->limit_kredit, 0, ',', '.') }}</h4>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Tenor Bayar</p>
                        <h4 class="text-xl font-black text-slate-800">{{ $selected_customer->tenor_bayar ?: 30 }} <span class="text-xs text-slate-400 font-bold uppercase">Hari</span></h4>
                    </div>
                </div>
            </div>

            <!-- Transaction History -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden min-h-[400px]">
                <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <div class="flex items-center gap-4">
                        <h3 class="font-bold text-slate-800">Riwayat Transaksi ({{ $selected_customer->transactions->count() }})</h3>
                        <div class="flex bg-slate-200 rounded-lg p-0.5 text-[10px] font-black uppercase">
                            <button @click="activeFilter = 'semua'" :class="activeFilter === 'semua' ? 'bg-white shadow text-slate-800' : 'text-slate-500'" class="px-3 py-1 rounded-md">Semua</button>
                            <button @click="activeFilter = 'belum_bayar'" :class="activeFilter === 'belum_bayar' ? 'bg-white shadow text-slate-800' : 'text-slate-500'" class="px-3 py-1 rounded-md">Belum Lunas</button>
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($selected_customer->transactions as $trx)
                    <div x-show="activeFilter === 'semua' || (activeFilter === 'belum_bayar' && '{{ $trx->status_pembayaran }}' !== 'lunas')" 
                         class="p-6 hover:bg-slate-50 transition-all group">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-black text-sm text-slate-800">{{ $trx->no_transaksi }}</span>
                                    @php
                                        $badgeClass = [
                                            'lunas' => 'bg-green-100 text-green-700',
                                            'belum_bayar' => 'bg-red-100 text-red-700',
                                            'sebagian' => 'bg-orange-100 text-orange-700',
                                        ][$trx->status_pembayaran] ?? 'bg-slate-100 text-slate-600';
                                        $badgeLabel = [
                                            'lunas' => 'Lunas',
                                            'belum_bayar' => 'Belum Lunas',
                                            'sebagian' => 'Bayar Sebagian',
                                        ][$trx->status_pembayaran] ?? $trx->status_pembayaran;
                                    @endphp
                                    <span class="px-2 py-0.5 {{ $badgeClass }} text-[9px] font-black rounded-full uppercase tracking-widest">{{ $badgeLabel }}</span>
                                </div>
                                <p class="text-[11px] text-slate-400 font-bold uppercase tracking-widest">{{ \Carbon\Carbon::parse($trx->tgl_transaksi)->format('d M Y') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-black text-slate-800">Rp {{ number_format($trx->total_tagihan, 0, ',', '.') }}</p>
                                @if($trx->status_pembayaran !== 'lunas')
                                    <p class="text-xs font-bold text-red-500">Sisa: Rp {{ number_format($trx->total_tagihan - $trx->jumlah_bayar, 0, ',', '.') }}</p>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Item Details -->
                        <div class="bg-slate-50 rounded-xl p-3 mb-4 space-y-1">
                            @foreach($trx->details as $detail)
                            <div class="flex justify-between text-xs text-slate-600">
                                <span>{{ $detail->product->nama }} x {{ $detail->qty }}</span>
                                <span class="font-medium">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                            </div>
                            @endforeach
                            <div class="pt-2 mt-2 border-t border-slate-200 flex justify-between items-center">
                                <div class="flex items-center gap-2 text-[10px] font-bold {{ \Carbon\Carbon::parse($trx->jatuh_tempo)->isPast() && $trx->status_pembayaran !== 'lunas' ? 'text-red-500' : 'text-slate-400' }}">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    Jatuh tempo: {{ \Carbon\Carbon::parse($trx->jatuh_tempo)->format('d M Y') }}
                                </div>
                                @if($trx->status_pembayaran !== 'lunas')
                                    <button @click="openPayModal({{ $trx->toJson() }})" class="px-3 py-1.5 bg-blue-600 text-white text-[11px] font-black rounded-lg shadow-sm shadow-blue-100 hover:bg-blue-700 transition-all flex items-center gap-1.5">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                        Tambah Pembayaran
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="py-20 text-center flex flex-col items-center">
                        <svg class="w-12 h-12 text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <p class="text-sm font-bold text-slate-400">Belum ada riwayat transaksi</p>
                    </div>
                    @endforelse
                </div>
            </div>
            @else
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-20 flex flex-col items-center justify-center text-center">
                <div class="w-20 h-20 bg-blue-50 text-blue-300 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-800">Pilih Pelanggan</h3>
                <p class="text-slate-500 mt-2 max-w-xs">Silakan pilih pelanggan dari daftar di sebelah kiri untuk melihat rincian piutang dan riwayat transaksi.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- PAY MODAL -->
    <div x-show="payModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center" style="display: none;">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="payModalOpen = false" x-transition.opacity></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm relative z-10 m-4 overflow-hidden transform transition-all" x-transition>
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-lg font-bold text-slate-800">Cicil / Bayar Bon</h3>
                <button @click="payModalOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form :action="`/customers/pay/${payForm.id}`" method="POST">
                @csrf
                <div class="p-6 space-y-4">
                    <div class="p-3 bg-slate-50 rounded-xl space-y-1 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500 font-bold uppercase tracking-widest">Total Tagihan</span>
                            <span class="font-black text-slate-800" x-text="'Rp ' + new Number(payForm.total_tagihan).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 font-bold uppercase tracking-widest">Telah Dibayar</span>
                            <span class="font-black text-green-600" x-text="'Rp ' + new Number(payForm.jumlah_bayar).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="pt-1 mt-1 border-t border-slate-200 flex justify-between">
                            <span class="text-slate-800 font-black uppercase tracking-widest">Sisa Hutang</span>
                            <span class="font-black text-red-600" x-text="'Rp ' + new Number(payForm.sisa).toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Jumlah Pembayaran (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="jumlah_bayar" x-model="payForm.bayar" :max="payForm.sisa" required class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white font-black text-blue-600">
                        <p class="text-[10px] text-slate-400 mt-1 italic">* Masukkan jumlah yang dibayarkan pelanggan saat ini</p>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/50">
                    <button type="button" @click="payModalOpen = false" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-green-600 hover:bg-green-700 rounded-lg text-sm font-bold text-white shadow-lg shadow-green-100 transition-all">Simpan Pembayaran</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ADD MODAL -->
    <div x-show="addModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center" style="display: none;">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="addModalOpen = false" x-transition.opacity></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg relative z-10 m-4 overflow-hidden transform transition-all" x-transition>
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-lg font-bold text-slate-800">Tambah Pelanggan Baru</h3>
                <button @click="addModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form action="{{ route('customer.store') }}" method="POST">
                @csrf
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" required class="w-full border border-slate-300 rounded-lg text-sm p-2.5 bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Email</label>
                        <input type="email" name="email" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">No. Telp</label>
                        <input type="text" name="telp" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Kategori</label>
                        <select name="kategori" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 bg-white">
                            <option value="Umum">Umum</option>
                            <option value="Kontraktor">Kontraktor</option>
                            <option value="Tukang">Tukang</option>
                            <option value="Retail">Retail</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Limit Kredit (Rp)</label>
                        <input type="number" name="limit_kredit" value="0" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 bg-white font-bold text-blue-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Tenor Bayar (Hari)</label>
                        <input type="number" name="tenor_bayar" value="30" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 bg-white font-bold">
                    </div>
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Alamat</label>
                        <textarea name="alamat" rows="2" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 resize-none bg-white"></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/50">
                    <button type="button" @click="addModalOpen = false" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-[#0f172a] hover:bg-slate-800 rounded-lg text-sm font-bold text-white shadow-lg transition-all">Simpan Pelanggan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT MODAL -->
    <div x-show="editModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center" style="display: none;">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="editModalOpen = false" x-transition.opacity></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg relative z-10 m-4 overflow-hidden transform transition-all" x-transition>
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-lg font-bold text-slate-800">Ubah Data Pelanggan</h3>
                <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form :action="`/customers/${editForm.id}`" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" x-model="editForm.nama" required class="w-full border border-slate-300 rounded-lg text-sm p-2.5 bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Email</label>
                        <input type="email" name="email" x-model="editForm.email" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">No. Telp</label>
                        <input type="text" name="telp" x-model="editForm.telp" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Kategori</label>
                        <select name="kategori" x-model="editForm.kategori" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 bg-white">
                            <option value="Umum">Umum</option>
                            <option value="Kontraktor">Kontraktor</option>
                            <option value="Tukang">Tukang</option>
                            <option value="Retail">Retail</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Limit Kredit (Rp)</label>
                        <input type="number" name="limit_kredit" x-model="editForm.limit_kredit" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 bg-white font-bold text-blue-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Tenor Bayar (Hari)</label>
                        <input type="number" name="tenor_bayar" x-model="editForm.tenor_bayar" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 bg-white font-bold">
                    </div>
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Alamat</label>
                        <textarea name="alamat" x-model="editForm.alamat" rows="2" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 resize-none bg-white"></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/50">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg text-sm font-bold text-white shadow-lg shadow-blue-100 transition-all">Perbarui Pelanggan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- DELETE MODAL -->
    <div x-show="deleteModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center" style="display: none;">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="deleteModalOpen = false" x-transition.opacity></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm relative z-10 m-4 overflow-hidden transform transition-all" x-transition>
            <div class="p-6 text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-2">Hapus Pelanggan?</h3>
                <p class="text-sm text-slate-500">Anda yakin ingin menghapus <span class="font-bold text-slate-700" x-text="deleteForm.nama"></span>? Semua riwayat piutang akan tetap tersimpan di modul keuangan.</p>
            </div>
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex gap-3">
                <button type="button" @click="deleteModalOpen = false" class="flex-1 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                <form :action="`/customers/${deleteForm.id}`" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-2 bg-red-600 hover:bg-red-700 rounded-lg text-sm font-bold text-white shadow-lg shadow-red-100 transition-all">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
