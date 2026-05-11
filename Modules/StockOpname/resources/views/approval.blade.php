@extends('layouts.app')

@section('content')
<div class="p-6" x-data="{ 
    showConfirm: false, 
    action: '', 
    targetId: null,
    targetName: '',
    confirmAction(id, name, type) {
        this.targetId = id;
        this.targetName = name;
        this.action = type;
        this.showConfirm = true;
    }
}">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Persetujuan Audit Stok</h2>
            <p class="text-slate-500 text-sm">Menunggu verifikasi untuk sinkronisasi ke gudang</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('stockopname.history') }}" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-sm font-bold hover:bg-slate-200 transition-colors">Lihat Riwayat</a>
            <a href="{{ route('stockopname.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition-all shadow-lg shadow-indigo-200">Audit Baru</a>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 border-b border-slate-200 uppercase tracking-widest text-[10px] font-black">
                    <tr>
                        <th class="p-4">Waktu Pengajuan</th>
                        <th class="p-4">Produk</th>
                        <th class="p-4 text-center">Sistem</th>
                        <th class="p-4 text-center">Fisik</th>
                        <th class="p-4 text-center">Selisih</th>
                        <th class="p-4">Petugas</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pending as $item)
                    <tr class="hover:bg-amber-50/30 transition-colors">
                        <td class="p-4">
                            <div class="font-bold text-slate-800">{{ $item->created_at->format('d M Y') }}</div>
                            <div class="text-[10px] text-slate-400">{{ $item->created_at->format('H:i:s') }} ({{ $item->created_at->diffForHumans() }})</div>
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-slate-800">{{ $item->product->nama ?? '-' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $item->product->sku ?? '-' }}</div>
                        </td>
                        <td class="p-4 text-center text-slate-500">{{ number_format($item->stok_sistem) }}</td>
                        <td class="p-4 text-center font-bold text-indigo-600 text-lg">{{ number_format($item->stok_fisik) }}</td>
                        <td class="p-4 text-center font-black {{ $item->selisih < 0 ? 'text-red-500' : 'text-blue-500' }}">
                            {{ $item->selisih > 0 ? '+' : '' }}{{ $item->selisih }}
                        </td>
                        <td class="p-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr($item->causer->name ?? '?', 0, 1)) }}
                                </div>
                                <span class="font-medium text-slate-700">{{ $item->causer->name ?? 'Sistem' }}</span>
                            </div>
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex justify-end gap-2">
                                <button @click="confirmAction({{ $item->id }}, '{{ addslashes($item->product->nama) }}', 'approve')" class="px-3 py-1.5 bg-emerald-100 text-emerald-700 rounded-lg text-[10px] font-black uppercase hover:bg-emerald-200 transition-colors">Setujui</button>
                                <button @click="confirmAction({{ $item->id }}, '{{ addslashes($item->product->nama) }}', 'reject')" class="px-3 py-1.5 bg-red-100 text-red-700 rounded-lg text-[10px] font-black uppercase hover:bg-red-200 transition-colors">Tolak</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-20 text-center">
                            <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100 shadow-inner">
                                <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <p class="text-slate-500 font-bold text-lg">Semua Audit Telah Selesai</p>
                            <p class="text-slate-400 text-sm">Tidak ada pengajuan yang menunggu persetujuan saat ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODERN CONFIRMATION MODAL -->
    <template x-if="showConfirm">
        <div class="fixed inset-0 z-[150] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showConfirm = false"></div>
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden border border-slate-200 animate-in zoom-in duration-200">
                <div class="p-8 text-center">
                    <div :class="action === 'approve' ? 'bg-emerald-50 text-emerald-600 ring-emerald-50/50' : 'bg-red-50 text-red-600 ring-red-50/50'" class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 ring-8">
                        <template x-if="action === 'approve'">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </template>
                        <template x-if="action === 'reject'">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </template>
                    </div>
                    <h3 class="text-xl font-black text-slate-900 mb-2" x-text="action === 'approve' ? 'Konfirmasi Persetujuan' : 'Konfirmasi Penolakan'"></h3>
                    <p class="text-slate-500 leading-relaxed">
                        Anda akan <span x-text="action === 'approve' ? 'menyetujui' : 'menolak'"></span> audit stok untuk <br>
                        <strong class="text-slate-800" x-text="targetName"></strong>. 
                        <span x-show="action === 'approve'" class="block mt-2 text-emerald-600 font-bold">Stok sistem akan diperbarui secara otomatis.</span>
                    </p>
                </div>
                <div class="px-6 py-5 bg-slate-50 border-t border-slate-100 flex gap-3">
                    <button @click="showConfirm = false" class="flex-1 py-3 bg-white border border-slate-300 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all shadow-sm">Batal</button>
                    
                    <form :action="'{{ url('stock-opname') }}/' + action + '/' + targetId" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" :class="action === 'approve' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-red-600 hover:bg-red-700'" class="w-full py-3 rounded-xl text-sm font-bold text-white transition-all shadow-lg shadow-indigo-900/20">
                            Ya, <span x-text="action === 'approve' ? 'Setujui' : 'Tolak'"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection
