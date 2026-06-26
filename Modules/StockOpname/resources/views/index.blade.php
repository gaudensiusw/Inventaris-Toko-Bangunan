@extends('layouts.app')

@section('title', in_array(auth()->user()->role, ['owner', 'supervisor']) ? 'Toko Bangunan - Stock Opname' : 'Toko Bangunan - Pengajuan Cek Stok')
@section('header_title', in_array(auth()->user()->role, ['owner', 'supervisor']) ? 'Stock Opname' : 'Pengajuan Cek Stok Fisik')

@section('content')
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

<!-- Stats Row -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm text-center">
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Total Produk</p>
        <h3 class="text-xl font-bold text-[#2563eb]">{{ $stats['total'] }}</h3>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm text-center text-green-600">
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Tersedia</p>
        <h3 class="text-xl font-bold">{{ $stats['tersedia'] }}</h3>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm text-center text-red-500">
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Stok Habis</p>
        <h3 class="text-xl font-bold">{{ $stats['kosong'] }}</h3>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm text-center text-orange-500">
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Menipis</p>
        <h3 class="text-xl font-bold">{{ $stats['menipis'] }}</h3>
    </div>
</div>

<!-- Filter & Header Section -->
<div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mb-6">
    <div class="p-5 border-b border-slate-200 bg-slate-50/50 flex flex-wrap gap-4 items-center justify-between">
        <div>
            <h3 class="text-base font-bold text-slate-800">Filter Pencarian Barang</h3>
            <p class="text-[11px] text-slate-500 uppercase tracking-widest font-black mt-0.5">Saring daftar barang untuk audit yang lebih fokus</p>
        </div>
    </div>
    <div class="p-5">
        <form action="{{ route('stockopname.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Cari Nama Barang</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama barang..." 
                        class="w-full border border-slate-300 rounded-lg text-sm p-2.5 pl-10 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Kategori</label>
                <select name="category_id" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-sm font-bold transition-all shadow-sm flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    Terapkan Filter
                </button>
                <a href="{{ route('stockopname.index') }}" class="px-4 py-2.5 bg-slate-100 text-slate-600 rounded-lg text-sm font-bold hover:bg-slate-200 transition-colors">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Opname Section -->
<div x-data>
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mb-6">
        <!-- Persistent Bar -->
        <div x-show="$store.opname.count > 0" class="bg-indigo-600 px-5 py-3 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3" x-cloak>
            <div class="flex items-center gap-3 text-white">
                <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest">Antrean Audit Aktif</p>
                    <p class="text-[10px] text-indigo-100"><span x-text="$store.opname.count" class="font-black"></span> barang dalam antrean (Tersimpan di memori)</p>
                </div>
            </div>
            <div class="flex gap-2">
                <button @click="$store.opname.clear(); window.location.reload();" class="px-3 py-1.5 bg-white/10 hover:bg-white/20 text-white rounded text-[10px] font-bold uppercase transition-colors">Batalkan Semua</button>
                <button @click="$store.opname.showConfirm = true" class="px-4 py-1.5 bg-white text-indigo-600 rounded text-[10px] font-black uppercase shadow-lg transition-transform hover:scale-105 active:scale-95">Selesaikan & Simpan</button>
            </div>
        </div>

        <div class="p-5 border-b border-slate-200 bg-slate-50/50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h3 class="text-base font-bold text-slate-800">Daftar Audit Persediaan Barang</h3>
                </div>
                <p class="text-[11px] text-slate-500 uppercase tracking-widest font-black mt-0.5">Data yang Anda isi tersimpan otomatis di browser ini</p>
            </div>
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <a href="{{ route('stockopname.history') }}" class="px-5 py-2.5 rounded-lg text-sm font-bold bg-slate-200 text-slate-700 hover:bg-slate-300 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ in_array(auth()->user()->role, ['owner', 'supervisor']) ? 'Lihat Riwayat' : 'Status Pengajuan' }}
                </a>
                <button type="button" @click="$store.opname.showConfirm = true" 
                    :class="$store.opname.count > 0 ? 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-200' : 'bg-slate-300 cursor-not-allowed'"
                    :disabled="$store.opname.count === 0"
                    class="text-white px-6 py-2.5 rounded-lg text-sm font-black transition-all shadow-lg flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span x-text="'{{ in_array(auth()->user()->role, ['owner', 'supervisor']) ? 'Simpan Hasil Audit' : 'Kirim Pengajuan' }}' + ' (' + $store.opname.count + ')'"></span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="p-4 font-semibold uppercase tracking-wider text-xs">Produk</th>
                        <th class="p-4 font-semibold uppercase tracking-wider text-xs text-center">Stok Sistem</th>
                        <th class="p-4 font-semibold uppercase tracking-wider text-xs text-center w-32">Stok Fisik</th>
                        <th class="p-4 font-semibold uppercase tracking-wider text-xs text-center">Selisih</th>
                        <th class="p-4 font-semibold uppercase tracking-wider text-xs">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($products as $index => $p)
                    <tr class="hover:bg-blue-50/30 transition-colors" x-data="{ 
                        id: {{ $p->id }},
                        nama: {{ json_encode($p->nama) }},
                        sistem: {{ $p->stok }},
                        fisik: $store.opname.queue[{{ $p->id }}] ? $store.opname.queue[{{ $p->id }}].stok_fisik : '',
                        keterangan: $store.opname.queue[{{ $p->id }}] ? $store.opname.queue[{{ $p->id }}].keterangan : '',
                        
                        update() {
                            $store.opname.save(this.id, this.nama, this.fisik, this.keterangan, this.sistem);
                        },

                        get selisih() { 
                            if (this.fisik === '' || this.fisik === null) return '-';
                            return parseInt(this.fisik) - this.sistem;
                        }
                    }">
                        <td class="p-4">
                            <div class="font-bold text-slate-800">{{ $p->nama }}</div>
                            <div class="flex items-center gap-2 mt-1 mb-1">
                                <span class="text-[10px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded font-bold uppercase tracking-tighter">{{ $p->unit }}</span>
                                <span class="text-[10px] text-slate-400">{{ $p->category->nama ?? '-' }}</span>
                            </div>
                            @if($p->latestOpname)
                            <div class="text-[9px] text-slate-400 flex items-center gap-1 mt-1.5">
                                <svg class="w-3 h-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Diubah: {{ $p->latestOpname->created_at->diffForHumans() }} oleh <span class="font-bold text-slate-500">{{ $p->latestOpname->causer->name ?? 'Sistem' }}</span>
                            </div>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            <span class="font-black text-slate-500">{{ number_format($p->stok, 0) }}</span>
                        </td>
                        <td class="p-4">
                            <input type="number" x-model="fisik" @input="update()"
                                placeholder="Isi fisik..."
                                class="w-full border border-slate-300 rounded-lg text-sm p-2 text-center font-bold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-all bg-white">
                        </td>
                        <td class="p-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black border uppercase tracking-widest"
                                :class="selisih === '-' ? 'bg-slate-50 text-slate-400 border-slate-100' : (selisih == 0 ? 'bg-green-100 text-green-700 border-green-200' : 'bg-red-100 text-red-700 border-red-200')"
                                x-text="selisih">
                            </span>
                        </td>
                        <td class="p-4">
                            <input type="text" x-model="keterangan" @input="update()"
                                placeholder="Opsional..."
                                class="w-full border border-slate-200 rounded-lg text-xs p-2 focus:ring-1 focus:ring-blue-400 transition-all bg-slate-50/50">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/30 flex justify-between items-center">
            <div class="text-[10px] text-slate-400 uppercase font-bold tracking-widest">
                * Data tersimpan otomatis di antrean sementara
            </div>
            {{ $products->links() }}
        </div>
    </div>

    <!-- BULK CONFIRMATION MODAL -->
    <div x-show="$store.opname.showConfirm" class="fixed inset-0 z-[110] flex items-center justify-center" style="display: none;" x-cloak>
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="$store.opname.showConfirm = false" x-transition.opacity></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm relative z-10 m-4 overflow-hidden border border-slate-200 transform transition-all" x-transition.scale.95>
            <div class="p-8 text-center">
                <div class="w-20 h-20 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-6 ring-8 ring-indigo-50/50">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">{{ in_array(auth()->user()->role, ['owner', 'supervisor']) ? 'Simpan' : 'Kirim' }} <span x-text="$store.opname.count"></span> Hasil Audit?</h3>
                <p class="text-slate-500 leading-relaxed">Sistem akan menyinkronkan seluruh barang dalam antrean sesuai angka fisik yang dimasukkan.</p>
            </div>
            <div class="px-6 py-5 bg-slate-50 border-t border-slate-100 flex gap-3">
                <button type="button" @click="$store.opname.showConfirm = false" class="flex-1 py-3 bg-white border border-slate-300 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all shadow-sm">Batal</button>
                <button @click="$store.opname.submit()" class="flex-1 py-3 bg-[#0f172a] hover:bg-slate-800 rounded-xl text-sm font-bold text-white transition-all shadow-lg shadow-slate-900/20">Ya, {{ in_array(auth()->user()->role, ['owner', 'supervisor']) ? 'Sinkronkan' : 'Kirim' }}</button>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('opname', {
            queue: JSON.parse(localStorage.getItem('opname_cache') || '{}'),
            showConfirm: false,

            save(id, nama, stokFisik, keterangan, sistem) {
                if (stokFisik === '' || stokFisik === null) {
                    delete this.queue[id];
                } else {
                    this.queue[id] = { 
                        id: id, 
                        nama: nama, 
                        stok_fisik: stokFisik, 
                        keterangan: keterangan || '',
                        stok_sistem: sistem
                    };
                }
                this.queue = { ...this.queue };
                localStorage.setItem('opname_cache', JSON.stringify(this.queue));
            },

            get count() {
                return Object.keys(this.queue).length;
            },

            clear() {
                this.queue = {};
                localStorage.removeItem('opname_cache');
            },

            async submit() {
                if (this.count === 0) return;

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                
                Object.values(this.queue).forEach((item, index) => {
                    formData.append(`opname_data[${index}][id]`, item.id);
                    formData.append(`opname_data[${index}][stok_fisik]`, item.stok_fisik);
                    formData.append(`opname_data[${index}][keterangan]`, item.keterangan || '');
                });

                try {
                    const response = await fetch('{{ route('stockopname.store') }}', {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });

                    if (response.ok) {
                        this.clear();
                        window.location.reload();
                    } else {
                        alert('Gagal menyimpan. Pastikan data valid.');
                    }
                } catch (e) {
                    alert('Terjadi kesalahan koneksi.');
                }
            }
        });
    });

    window.addEventListener('beforeunload', function (e) {
        if (window.isLoggingOut) return;
        if (typeof Alpine !== 'undefined' && Alpine.store('opname').count > 0) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
</script>
@endpush
