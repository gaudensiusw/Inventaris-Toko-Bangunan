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

    <!-- Filters -->
    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
        <form method="GET" action="{{ route('audit-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
            <!-- Search -->
            <div class="sm:col-span-2 md:col-span-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama pelaku..." class="w-full text-sm border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <!-- Filter Role -->
            <div>
                <select name="role" class="w-full text-sm border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Role</option>
                    <option value="owner" {{ request('role') == 'owner' ? 'selected' : '' }}>Owner</option>
                    <option value="supervisor" {{ request('role') == 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                    <option value="operator" {{ request('role') == 'operator' ? 'selected' : '' }}>Operator</option>
                </select>
            </div>
            
            <!-- Filter Modul -->
            <div>
                <select name="modul" class="w-full text-sm border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Modul</option>
                    <option value="User" {{ request('modul') == 'User' ? 'selected' : '' }}>User (Akun)</option>
                    <option value="Karyawan" {{ request('modul') == 'Karyawan' ? 'selected' : '' }}>Karyawan</option>
                    <option value="Absensi" {{ request('modul') == 'Absensi' ? 'selected' : '' }}>Absensi</option>
                    <option value="DataBarang" {{ request('modul') == 'DataBarang' ? 'selected' : '' }}>Data Barang</option>
                    <option value="StockOpname" {{ request('modul') == 'StockOpname' ? 'selected' : '' }}>Stock Opname</option>
                </select>
            </div>
            
            <!-- Filter Aksi -->
            <div>
                <select name="aksi" class="w-full text-sm border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Aksi</option>
                    <option value="created" {{ request('aksi') == 'created' ? 'selected' : '' }}>Created</option>
                    <option value="updated" {{ request('aksi') == 'updated' ? 'selected' : '' }}>Updated</option>
                    <option value="deleted" {{ request('aksi') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                </select>
            </div>
 
            <!-- Buttons -->
            <div class="sm:col-span-2 md:col-span-1 flex gap-2">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors shadow-sm text-sm">
                    Cari
                </button>
                <a href="{{ route('audit-logs.index') }}" class="w-full text-center bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-medium py-2 px-4 rounded-lg flex items-center justify-center transition-colors shadow-sm text-sm">
                    Reset
                </a>
            </div>
        </form>
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
                            if ($log->description === 'created' || $log->event === 'created') $actionColor = 'text-green-700 bg-green-50 border border-green-200';
                            if ($log->description === 'updated' || $log->event === 'updated') $actionColor = 'text-blue-700 bg-blue-50 border border-blue-200';
                            if ($log->description === 'deleted' || $log->event === 'deleted') $actionColor = 'text-red-700 bg-red-50 border border-red-200';
                            if ($log->event === 'GENERATED') $actionColor = 'text-purple-700 bg-purple-50 border border-purple-200';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold uppercase {{ $actionColor }}">
                            {{ $log->event ?? $log->description }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        @php
                            $subjectName = 'ID: ' . $log->subject_id;
                            if ($log->subject) {
                                if ($log->subject instanceof \Modules\Employee\Models\Absensi) {
                                    $subjectName = 'Absensi - ' . ($log->subject->karyawan->nama ?? 'Karyawan ID: ' . $log->subject->karyawan_id);
                                } else {
                                    $subjectName = $log->subject->nama ?? $log->subject->name ?? $log->subject->kode_karyawan ?? $log->subject->nama_jabatan ?? 'ID: ' . $log->subject_id;
                                }
                            }
                        @endphp
                        @php
                            // Derive a human-readable module name from log_name or subject_type
                            $rawModule = $log->log_name ?? '';
                            if (empty($rawModule) && $log->subject_type) {
                                $parts = explode('\\', $log->subject_type);
                                $rawModule = end($parts);
                            }
                            $moduleLabels = [
                                'Absensi'       => 'Absensi',
                                'Employee'      => 'Data Karyawan',
                                'Karyawan'      => 'Data Karyawan',
                                'User'          => 'Manajemen Akun',
                                'DataBarang'    => 'Data Barang',
                                'Product'       => 'Data Barang',   // backward compat
                                'StockOpname'   => 'Stock Opname',
                                'Stock'         => 'Stock Opname',  // backward compat
                                'POS'           => 'Transaksi POS',
                                'Pembelian'     => 'Pembelian',
                                'Supplier'      => 'Supplier',
                                'Customer'      => 'Customer',
                            ];
                            $moduleName = $moduleLabels[$rawModule] ?? $rawModule;
                        @endphp
                        <div class="flex items-center justify-end gap-2">
                            <button type="button" onclick="openDetailModal({{ json_encode($log->properties) }}, {{ json_encode($log->event ?? $log->description) }}, {{ json_encode($subjectName) }}, {{ json_encode($log->description) }}, {{ json_encode($moduleName) }})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors tooltip" title="Lihat Detail">
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
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all max-w-2xl w-full">
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

<!-- Modal Konfirmasi Hapus Log -->
<div id="modalDeleteConfirm" class="fixed inset-0 z-[60] hidden" aria-modal="true" role="dialog">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300 opacity-0" id="deleteBackdrop"></div>
    <!-- Panel -->
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div id="deleteModalPanel" class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md transform scale-90 opacity-0 transition-all duration-300">
            <!-- Header -->
            <div class="px-6 pt-6 pb-4">
                <div class="flex items-start gap-4">
                    <!-- Warning icon -->
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-slate-800">Hapus Log Ini?</h3>
                        <p class="mt-1 text-sm text-slate-500">Tindakan ini akan menghapus entri audit log secara <span class="font-semibold text-red-600">permanen</span> dan tidak dapat dibatalkan.</p>
                    </div>
                </div>
            </div>
            <!-- Divider -->
            <div class="border-t border-slate-100"></div>
            <!-- Warning detail -->
            <div class="px-6 py-3 bg-red-50 border-b border-red-100">
                <p class="text-xs text-red-700 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                    Log yang dihapus tidak akan muncul lagi di riwayat aktivitas sistem.
                </p>
            </div>
            <!-- Footer -->
            <div class="px-6 py-4 flex justify-end gap-3">
                <button
                    id="deleteCancelBtn"
                    type="button"
                    onclick="closeDeleteModal()"
                    class="px-5 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-slate-300"
                >
                    Batal
                </button>
                <button
                    id="deleteConfirmBtn"
                    type="button"
                    onclick="confirmDeleteLog()"
                    class="px-5 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-xl hover:bg-red-700 active:bg-red-800 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-red-400 flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Ya, Hapus Permanen
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Format currency IDR helper
    const formatRp = (angka) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
    };

    // Employee ID to name mapping from backend
    const karyawanMap = @json($karyawanMap);

    function openDetailModal(properties, eventName, subjectName, description = '', moduleName = '') {
        let html = '';
        
        // Identity Header
        let headerBg = 'bg-blue-50 border-blue-100';
        let headerTextClass = 'text-blue-600';
        let headerIconBg = 'bg-blue-200 text-blue-700';
        
        if (eventName === 'GENERATED' || eventName === 'updated') {
            headerBg = 'bg-blue-50 border-blue-100';
            headerTextClass = 'text-blue-600';
            headerIconBg = 'bg-blue-200 text-blue-700';
        }
        if (eventName === 'created') {
            headerBg = 'bg-green-50 border-green-100';
            headerTextClass = 'text-green-600';
            headerIconBg = 'bg-green-200 text-green-700';
        }
        if (eventName === 'deleted') {
            headerBg = 'bg-red-50 border-red-100';
            headerTextClass = 'text-red-600';
            headerIconBg = 'bg-red-200 text-red-700';
        }

        // Module badge (if available)
        const moduleBadge = moduleName
            ? `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700 border border-indigo-200">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                ${moduleName}
              </span>`
            : '';

        html += `
        <div class="col-span-1 md:col-span-2 mb-4">
            <div class="${headerBg} rounded-lg p-3 border flex items-start gap-3">
                <div class="w-10 h-10 rounded-full ${headerIconBg} flex-shrink-0 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs ${headerTextClass} font-semibold uppercase tracking-wider">Target Data (Subject)</p>
                    <p class="text-sm font-bold text-slate-800">${subjectName}</p>
                    ${moduleName ? `<div class="mt-1.5 flex items-center gap-1.5">
                        <span class="text-xs text-slate-500">Modul:</span>
                        ${moduleBadge}
                    </div>` : ''}
                </div>
            </div>
        </div>
        `;
        
        const hasOld = properties.old !== undefined;
        const hasNew = properties.attributes !== undefined;
        const ignoreFields = ['id', 'created_at', 'updated_at', 'remember_token', 'password'];

        const formatValue = (key, val) => {
            if (val === null || val === undefined || val === '') return '<span class="text-slate-400 italic">- (Kosong)</span>';
            
            // Format Employee Relational ID to Name
            if (key === 'karyawan_id') {
                const name = karyawanMap[val] ?? `Karyawan ID: ${val}`;
                return `<span class="text-slate-800 font-semibold">${name}</span>`;
            }
            
            // Format Potongan / JSON
            if (key === 'keterangan_potongan' || (typeof val === 'string' && val.startsWith('[') && val.endsWith(']'))) {
                try {
                    const parsed = typeof val === 'string' ? JSON.parse(val) : val;
                    if (Array.isArray(parsed)) {
                        if (parsed.length === 0) return '<span class="text-slate-400 italic">- (Kosong)</span>';
                        return parsed.map(item => {
                            return `<div class="text-slate-800 font-medium">${item.keterangan || 'Potongan'}: ${formatRp(item.nominal || 0)}</div>`;
                        }).join('');
                    }
                } catch (e) {
                    // Fall back
                }
            }

            if (key === 'aktif') {
                if (val == 1 || val === true || val === 'aktif' || val === '1') return '<span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-green-100 text-green-700 border border-green-200">Aktif</span>';
                return '<span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-600 border border-slate-200">Nonaktif</span>';
            }

            if (key === 'status') {
                if (val == 1 || val === true || val === 'aktif' || val === '1') return '<span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-green-100 text-green-700 border border-green-200">Aktif</span>';
                if (val === 'nonaktif' || val == 0 || val === false || val === '0') return '<span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-600 border border-slate-200">Nonaktif</span>';
                
                let badgeClass = 'bg-slate-100 text-slate-700';
                if (val === 'hadir') badgeClass = 'bg-green-100 text-green-800 border border-green-200';
                if (val === 'izin') badgeClass = 'bg-yellow-100 text-yellow-800 border border-yellow-200';
                if (val === 'sakit') badgeClass = 'bg-orange-100 text-orange-800 border border-orange-200';
                if (val === 'alpha') badgeClass = 'bg-red-100 text-red-800 border border-red-200';
                return `<span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase ${badgeClass}">${val}</span>`;
            }

            if (typeof val === 'object') return `<span class="text-slate-800 font-mono text-xs">${JSON.stringify(val)}</span>`;
            return `<span class="text-slate-800 break-words">${val}</span>`;
        };

        if (eventName === 'GENERATED') {
            html += `
            <div class="col-span-1 md:col-span-2">
                <div class="bg-purple-50 rounded-xl p-5 border border-purple-100 flex flex-col gap-2">
                    <h4 class="text-sm font-bold text-purple-900 flex items-center gap-1.5">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Aktivitas Slip Gaji & Payroll
                    </h4>
                    <p class="text-sm text-purple-800 font-semibold mt-1">${description}</p>
                </div>
            </div>
            `;
        } else if (eventName === 'updated' && hasNew) {
            html += `
            <div class="col-span-1 md:col-span-2">
                <div class="border border-slate-200 rounded-lg overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider w-1/4">Kolom / Field</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider w-3/8">Data Lama (Old)</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider w-3/8">Data Baru (New)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
            `;
            
            Object.entries(properties.attributes).forEach(([key, newVal]) => {
                if (ignoreFields.includes(key)) return;
                
                const oldVal = (hasOld && properties.old[key] !== undefined) ? properties.old[key] : null;
                
                // Only show changes
                if (oldVal !== newVal) {
                    html += `
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase bg-slate-50 border-r border-slate-100">${key.replace(/_/g, ' ')}</td>
                                <td class="px-4 py-3 text-sm">${formatValue(key, oldVal)}</td>
                                <td class="px-4 py-3 text-sm bg-blue-50/30 border-l border-blue-50">${formatValue(key, newVal)}</td>
                            </tr>
                    `;
                }
            });

            html += `
                        </tbody>
                    </table>
                </div>
            </div>`;
        } else if (eventName === 'created' && hasNew) {
            html += `
            <div class="col-span-1 md:col-span-2">
                <div class="border border-slate-200 rounded-lg overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider w-1/3">Kolom / Field</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider w-2/3">Data Baru</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
            `;
            
            Object.entries(properties.attributes).forEach(([key, newVal]) => {
                if (ignoreFields.includes(key)) return;
                
                html += `
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase bg-slate-50 border-r border-slate-100">${key.replace(/_/g, ' ')}</td>
                                <td class="px-4 py-3 text-sm">${formatValue(key, newVal)}</td>
                            </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            </div>`;
        } else if (eventName === 'deleted' && hasOld) {
            html += `
            <div class="col-span-1 md:col-span-2">
                <div class="border border-slate-200 rounded-lg overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider w-1/3">Kolom / Field</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider w-2/3">Data Dihapus</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
            `;
            
            Object.entries(properties.old).forEach(([key, oldVal]) => {
                if (ignoreFields.includes(key)) return;
                
                html += `
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase bg-slate-50 border-r border-slate-100">${key.replace(/_/g, ' ')}</td>
                                <td class="px-4 py-3 text-sm bg-red-50/30">${formatValue(key, oldVal)}</td>
                            </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            </div>`;
        } else {
            // Fallback
            html += `
            <div class="col-span-1 md:col-span-2">
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                    <pre class="text-xs text-slate-700 whitespace-pre-wrap font-mono">${JSON.stringify(properties, null, 2)}</pre>
                </div>
            </div>`;
        }

        document.getElementById('detailContent').innerHTML = html;
        document.getElementById('modalDetail').classList.remove('hidden');
    }

    @if(auth()->user()->role === 'owner')
    let _pendingDeleteId = null;

    function deleteLog(id) {
        _pendingDeleteId = id;
        openDeleteModal();
    }

    function openDeleteModal() {
        const modal = document.getElementById('modalDeleteConfirm');
        const backdrop = document.getElementById('deleteBackdrop');
        const panel = document.getElementById('deleteModalPanel');
        modal.classList.remove('hidden');
        // Trigger reflow then animate in
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                backdrop.classList.remove('opacity-0');
                panel.classList.remove('scale-90', 'opacity-0');
                panel.classList.add('scale-100', 'opacity-100');
            });
        });
        // Trap focus on confirm button
        setTimeout(() => document.getElementById('deleteConfirmBtn').focus(), 200);
    }

    function closeDeleteModal() {
        const modal = document.getElementById('modalDeleteConfirm');
        const backdrop = document.getElementById('deleteBackdrop');
        const panel = document.getElementById('deleteModalPanel');
        backdrop.classList.add('opacity-0');
        panel.classList.remove('scale-100', 'opacity-100');
        panel.classList.add('scale-90', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            _pendingDeleteId = null;
        }, 250);
    }

    async function confirmDeleteLog() {
        if (!_pendingDeleteId) return;

        const confirmBtn = document.getElementById('deleteConfirmBtn');
        const cancelBtn  = document.getElementById('deleteCancelBtn');

        // Show loading state
        confirmBtn.disabled = true;
        cancelBtn.disabled  = true;
        confirmBtn.innerHTML = `
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            Menghapus...
        `;

        try {
            const response = await fetch(`{{ url('/audit-logs') }}/${_pendingDeleteId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();

            closeDeleteModal();

            if (data.success) {
                // Fade & remove table row
                const row = document.getElementById(`log-row-${_pendingDeleteId}`);
                if (row) {
                    row.style.transition = 'opacity 0.3s ease, background-color 0.3s ease';
                    row.style.backgroundColor = '#fef2f2';
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 350);
                }
            } else {
                // Show inline error toast
                showToast('error', data.message || 'Gagal menghapus log.');
            }
        } catch (err) {
            closeDeleteModal();
            showToast('error', 'Terjadi kesalahan jaringan.');
        } finally {
            confirmBtn.disabled = false;
            cancelBtn.disabled  = false;
            confirmBtn.innerHTML = `
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Ya, Hapus Permanen
            `;
        }
    }

    // Close on backdrop click
    document.getElementById('modalDeleteConfirm').addEventListener('click', function(e) {
        if (e.target === this || e.target.id === 'deleteBackdrop') closeDeleteModal();
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !document.getElementById('modalDeleteConfirm').classList.contains('hidden')) {
            closeDeleteModal();
        }
    });

    // Simple toast notification
    function showToast(type, message) {
        const bg = type === 'error' ? 'bg-red-600' : 'bg-green-600';
        const toast = document.createElement('div');
        toast.className = `fixed bottom-6 right-6 z-[70] ${bg} text-white text-sm font-semibold px-5 py-3 rounded-xl shadow-lg flex items-center gap-2 transition-all duration-300`;
        toast.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>${message}`;
        document.body.appendChild(toast);
        setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 3000);
    }
    @endif
</script>
@endpush
