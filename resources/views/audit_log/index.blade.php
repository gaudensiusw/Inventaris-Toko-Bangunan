@extends('layouts.app')

@section('title', 'Audit Log - Toko Bangunan Rajawali')

@section('header_title', 'Audit Log')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Riwayat Aktivitas Sistem</h2>
            <p class="text-sm text-slate-500 mt-1">Daftar semua perubahan data (CRUD) yang dilakukan oleh user.</p>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-xs uppercase tracking-wider text-slate-500 font-bold">
                    <th class="px-6 py-4 whitespace-nowrap">Waktu</th>
                    <th class="px-6 py-4 whitespace-nowrap">Pelaku</th>
                    <th class="px-6 py-4 whitespace-nowrap">Modul</th>
                    <th class="px-6 py-4 whitespace-nowrap">Aksi</th>
                    <th class="px-6 py-4 whitespace-nowrap text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($logs as $log)
                <tr class="hover:bg-slate-50 transition-colors" id="log-row-{{ $log->id }}">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-semibold text-slate-800">{{ $log->created_at->format('d M Y') }}</div>
                        <div class="text-xs text-slate-500">{{ $log->created_at->format('H:i:s') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">
                                {{ $log->causer ? strtoupper(substr($log->causer->name, 0, 1)) : 'S' }}
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-slate-800">{{ $log->causer->name ?? 'System' }}</div>
                                @if($log->causer)
                                <div class="text-xs text-slate-500">{{ ucfirst($log->causer->role) }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700">
                            {{ $log->log_name }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $actionColor = 'text-slate-600 bg-slate-100';
                            if ($log->description === 'created') $actionColor = 'text-green-700 bg-green-50 border border-green-200';
                            if ($log->description === 'updated') $actionColor = 'text-blue-700 bg-blue-50 border border-blue-200';
                            if ($log->description === 'deleted') $actionColor = 'text-red-700 bg-red-50 border border-red-200';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold uppercase {{ $actionColor }}">
                            {{ $log->description }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button type="button" onclick='openDetailModal(@json($log->properties))' class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors tooltip" title="Lihat Detail">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </button>
                            @if(auth()->user()->role === 'owner')
                            <button type="button" onclick="deleteLog({{ $log->id }})" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors tooltip" title="Hapus Log">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            <p class="font-medium text-slate-600">Belum ada riwayat aktivitas</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-slate-200">
        {{ $logs->links() }}
    </div>
</div>

<!-- Modal Detail -->
<div id="modalDetail" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm overflow-y-auto">
    <div class="min-h-screen px-4 text-center">
        <div class="inline-block align-middle bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">
            <div class="px-6 py-5 border-b border-slate-200 flex justify-between items-center bg-slate-50">
                <h3 class="text-lg font-bold text-slate-800">Detail Perubahan Data</h3>
                <button type="button" onclick="document.getElementById('modalDetail').classList.add('hidden')" class="text-slate-400 hover:text-slate-500 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="detailContent">
                    <!-- Data will be injected here -->
                </div>
            </div>
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 text-right">
                <button type="button" onclick="document.getElementById('modalDetail').classList.add('hidden')" class="px-6 py-2.5 bg-white border border-slate-300 text-slate-700 font-semibold rounded-xl hover:bg-slate-50 transition-colors shadow-sm">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openDetailModal(properties) {
        let html = '';
        
        // Cek apakah ada data old dan attributes (new)
        const hasOld = properties.old !== undefined;
        const hasNew = properties.attributes !== undefined;
        
        if (hasOld) {
            html += `
            <div>
                <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-3 pb-2 border-b border-red-200 text-red-600 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Data Lama
                </h4>
                <div class="bg-red-50/50 rounded-xl p-4 border border-red-100">
                    <dl class="space-y-3">
                        ${Object.entries(properties.old).map(([key, value]) => `
                            <div>
                                <dt class="text-xs font-semibold text-slate-500 uppercase">${key}</dt>
                                <dd class="text-sm text-slate-800 mt-0.5 break-all font-mono bg-white px-2 py-1 rounded border border-slate-100">${value === null ? '<em>null</em>' : value}</dd>
                            </div>
                        `).join('')}
                    </dl>
                </div>
            </div>`;
        }

        if (hasNew) {
            html += `
            <div>
                <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-3 pb-2 border-b border-green-200 text-green-600 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Data Baru
                </h4>
                <div class="bg-green-50/50 rounded-xl p-4 border border-green-100">
                    <dl class="space-y-3">
                        ${Object.entries(properties.attributes).map(([key, value]) => `
                            <div>
                                <dt class="text-xs font-semibold text-slate-500 uppercase">${key}</dt>
                                <dd class="text-sm text-slate-800 mt-0.5 break-all font-mono bg-white px-2 py-1 rounded border border-slate-100">${value === null ? '<em>null</em>' : value}</dd>
                            </div>
                        `).join('')}
                    </dl>
                </div>
            </div>`;
        }
        
        // Fallback jika format log berbeda
        if (!hasOld && !hasNew && Object.keys(properties).length > 0) {
            html = `
            <div class="col-span-1 md:col-span-2">
                <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-3 pb-2 border-b border-slate-200">Properties</h4>
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                    <pre class="text-xs text-slate-700 whitespace-pre-wrap font-mono">${JSON.stringify(properties, null, 2)}</pre>
                </div>
            </div>`;
        }

        document.getElementById('detailContent').innerHTML = html || '<div class="col-span-2 text-center text-slate-500">Tidak ada detail data.</div>';
        document.getElementById('modalDetail').classList.remove('hidden');
    }

    @if(auth()->user()->role === 'owner')
    function deleteLog(id) {
        if (!confirm('Apakah Anda yakin ingin menghapus log ini secara permanen?')) return;

        fetch(`/audit-logs/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                // Remove row from table
                const row = document.getElementById(`log-row-${id}`);
                if (row) {
                    row.classList.add('bg-red-50', 'opacity-0');
                    setTimeout(() => row.remove(), 300);
                }
            } else {
                alert('Gagal menghapus log.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan.');
        });
    }
    @endif
</script>
@endpush
