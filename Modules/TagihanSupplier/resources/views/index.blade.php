@extends('layouts.app')

@section('title', 'Toko Bangunan - Tagihan Supplier')
@section('header_title', 'Tagihan Supplier')

@section('content')
<div x-data='{ 
    isSubmitting: false,
    addModalOpen: false, 
    editModalOpen: false, 
    deleteModalOpen: false,
    editForm: {},
    deleteForm: {},
    showConfirmSave: false,
    showConfirmEdit: false,
    openEditModal(bill) {
        this.editForm = JSON.parse(JSON.stringify(bill));
        this.editModalOpen = true;
    },
    openDeleteModal(bill) {
        this.deleteForm = bill;
        this.deleteModalOpen = true;
    },
    // Calendar Logic
    currentMonth: new Date().getMonth(),
    currentYear: new Date().getFullYear(),
    get monthName() { 
        return new Intl.DateTimeFormat("id-ID", { month: "long" }).format(new Date(this.currentYear, this.currentMonth, 1)); 
    },
    daysInMonth() {
        const days = [];
        const firstDay = new Date(this.currentYear, this.currentMonth, 1).getDay();
        const numDays = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
        
        let padding = firstDay === 0 ? 6 : firstDay - 1;
        for (let i = 0; i < padding; i++) days.push(null);
        
        for (let i = 1; i <= numDays; i++) {
            const dateStr = `${this.currentYear}-${String(this.currentMonth + 1).padStart(2, "0")}-${String(i).padStart(2, "0")}`;
            days.push({ day: i, date: dateStr });
        }
        return days;
    },
    prevMonth() { 
        if (this.currentMonth === 0) { this.currentMonth = 11; this.currentYear--; }
        else { this.currentMonth--; }
    },
    nextMonth() { 
        if (this.currentMonth === 11) { this.currentMonth = 0; this.currentYear++; }
        else { this.currentMonth++; }
    },
    billsByDate: @json($bills->groupBy(function($item) { return \Carbon\Carbon::parse($item->jatuh_tempo)->format("Y-m-d"); })->map->count()) || {},
    selectedDate: null,
    selectedBills: [],
    billsData: @json($bills->groupBy(function($item) { return \Carbon\Carbon::parse($item->jatuh_tempo)->format("Y-m-d"); })) || {},
    selectDate(dateStr) {
        this.selectedDate = dateStr;
        this.selectedBills = this.billsData[dateStr] || [];
    }
}'>
    <!-- Alert Messages -->
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Stats Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-50 text-[#2563eb] rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1">Total Hutang</p>
                <h3 class="text-xl font-bold text-slate-800">Rp {{ number_format($bills->where('status', '!=', 'lunas')->sum('total'), 0, ',', '.') }}</h3>
            </div>
        </div>
        
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-red-50 text-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1">Jatuh Tempo (7 Hari)</p>
                <h3 class="text-xl font-bold text-slate-800">Rp {{ number_format($bills->where('status', '!=', 'lunas')->where('jatuh_tempo', '<=', now()->addDays(7))->sum('total'), 0, ',', '.') }}</h3>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-orange-50 text-orange-500 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1">Tagihan Belum Bayar</p>
                <h3 class="text-xl font-bold text-slate-800">{{ $bills->where('status', 'belum_bayar')->count() }} Tagihan</h3>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-green-50 text-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1">Tagihan Lunas</p>
                <h3 class="text-xl font-bold text-slate-800">{{ $bills->where('status', 'lunas')->count() }} Tagihan</h3>
            </div>
        </div>
    </div>

    <!-- Calendar Card -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm mb-6 overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800">Kalender Jatuh Tempo</h3>
                    <p class="text-[11px] text-slate-500 uppercase tracking-widest font-black" x-text="`${monthName} ${year}`"></p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button @click="prevMonth()" class="p-2 hover:bg-slate-200 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </button>
                <button @click="nextMonth()" class="p-2 hover:bg-slate-200 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
            </div>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-7 gap-px bg-slate-100 border border-slate-100 rounded-lg overflow-hidden">
                <!-- Day names -->
                <template x-for="day in ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']">
                    <div class="bg-slate-50 py-2 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest" x-text="day"></div>
                </template>
                
                <!-- Calendar days -->
                <template x-for="dayObj in daysInMonth()">
                    <div @click="dayObj && selectDate(dayObj.date)" 
                        class="bg-white min-h-[48px] sm:min-h-[60px] p-1 sm:p-2 relative group hover:bg-blue-50/50 transition-all cursor-pointer"
                        :class="{ 'bg-blue-50 ring-1 ring-inset ring-blue-300 z-10': selectedDate === dayObj?.date }">
                        <template x-if="dayObj">
                            <div class="flex flex-col h-full">
                                <span class="text-xs sm:text-sm font-bold" 
                                    :class="dayObj.date === new Date().toISOString().split('T')[0] ? 'text-blue-600 underline decoration-2 underline-offset-4' : 'text-slate-700'"
                                    x-text="dayObj.day"></span>
                                
                                <div class="mt-auto flex flex-wrap gap-1">
                                    <template x-if="billsByDate[dayObj.date]">
                                        <div class="flex items-center gap-1">
                                            <div class="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-red-500 rounded-full animate-bounce"></div>
                                            <span class="text-[8px] sm:text-[9px] font-black text-red-600" x-text="`${billsByDate[dayObj.date]}`"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Selected Date Details -->
            <div x-show="selectedDate" x-transition class="mt-4 p-4 bg-slate-50 border border-slate-200 rounded-xl">
                <div class="flex justify-between items-center mb-3">
                    <h4 class="text-sm font-bold text-slate-800">Tagihan Jatuh Tempo: <span x-text="new Date(selectedDate).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })"></span></h4>
                    <button @click="selectedDate = null" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="space-y-2">
                    <template x-for="bill in selectedBills" :key="bill.id">
                        <div class="bg-white p-3 rounded-lg border border-slate-200 flex justify-between items-center shadow-sm">
                            <div>
                                <p class="text-xs font-bold text-slate-800" x-text="bill.supplier?.company_name || 'N/A'"></p>
                                <p class="text-[10px] text-slate-500" x-text="bill.no_invoice"></p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-black text-red-600" x-text="'Rp ' + new Number(bill.total).toLocaleString('id-ID')"></p>
                                <span class="text-[9px] uppercase font-bold px-1.5 py-0.5 rounded-full" 
                                    :class="bill.status === 'lunas' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                    x-text="bill.status.replace('_', ' ')"></span>
                            </div>
                        </div>
                    </template>
                    <template x-if="selectedBills.length === 0">
                        <p class="text-xs text-slate-500 italic">Tidak ada tagihan jatuh tempo pada tanggal ini.</p>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Bill Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex justify-between items-center bg-slate-50/50">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Daftar Tagihan Supplier</h3>
                <p class="text-xs text-slate-500 mt-0.5">Kelola hutang dan pembayaran ke supplier</p>
            </div>
            <button @click="addModalOpen = true" class="bg-[#0f172a] hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Tagihan
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-3 font-semibold uppercase tracking-wider text-xs">Supplier & Invoice</th>
                        <th class="py-3 px-3 font-semibold uppercase tracking-wider text-xs hidden xl:table-cell">Tgl Invoice</th>
                        <th class="py-3 px-3 font-semibold uppercase tracking-wider text-xs">Jatuh Tempo</th>
                        <th class="py-3 px-3 font-semibold uppercase tracking-wider text-xs">Total</th>
                        <th class="py-3 px-3 font-semibold uppercase tracking-wider text-xs">Status</th>
                        <th class="py-3 px-3 font-semibold uppercase tracking-wider text-xs text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($bills as $bill)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="py-4 px-3 whitespace-normal">
                            <div class="font-bold text-slate-800 leading-tight">{{ $bill->supplier->company_name ?? 'N/A' }}</div>
                            <div class="text-[10px] text-slate-500 uppercase mt-0.5">{{ $bill->no_invoice }}</div>
                            <div class="text-[11px] text-slate-500 mt-0.5 xl:hidden">Tgl: {{ \Carbon\Carbon::parse($bill->tgl_invoice)->format('d M Y') }}</div>
                        </td>
                        <td class="py-4 px-3 text-slate-600 hidden xl:table-cell">
                            {{ \Carbon\Carbon::parse($bill->tgl_invoice)->format('d M Y') }}
                        </td>
                        <td class="py-4 px-3">
                            <div class="font-medium {{ \Carbon\Carbon::parse($bill->jatuh_tempo)->isPast() && $bill->status != 'lunas' ? 'text-red-600' : 'text-slate-600' }}">
                                {{ \Carbon\Carbon::parse($bill->jatuh_tempo)->format('d M Y') }}
                            </div>
                            @if(\Carbon\Carbon::parse($bill->jatuh_tempo)->isPast() && $bill->status != 'lunas')
                                <div class="text-[10px] text-red-500 font-bold uppercase">Terlambat</div>
                            @endif
                        </td>
                        <td class="py-4 px-3 font-bold text-slate-800">
                            Rp {{ number_format($bill->total, 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-3">
                            @php
                                $statusColors = [
                                    'belum_bayar' => 'bg-red-100 text-red-700 border-red-200',
                                    'cicilan' => 'bg-orange-100 text-orange-700 border-orange-200',
                                    'lunas' => 'bg-green-100 text-green-700 border-green-200',
                                ];
                                $statusLabels = [
                                    'belum_bayar' => 'Belum Bayar',
                                    'cicilan' => 'Cicilan',
                                    'lunas' => 'Lunas',
                                ];
                                $color = $statusColors[$bill->status] ?? 'bg-slate-100 text-slate-600';
                                $label = $statusLabels[$bill->status] ?? $bill->status;
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black border uppercase tracking-widest {{ $color }}">
                                {{ $label }}
                            </span>
                        </td>
                        <td class="py-4 px-3 text-right space-x-1 whitespace-nowrap">
                            <button @click="openEditModal({{ $bill->toJson() }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors inline-block">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>
                            <button @click="openDeleteModal({{ $bill->toJson() }})" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition-colors inline-block">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <p class="text-sm font-medium">Belum ada tagihan supplier</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ADD MODAL -->
    <div x-show="addModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center" style="display: none;">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="addModalOpen = false" x-transition.opacity></div>
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl relative z-10 m-4 overflow-hidden transform transition-all" x-transition>
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Tambah Tagihan Supplier</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Catat tagihan baru dari supplier</p>
                </div>
                <button @click="addModalOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-lg hover:bg-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form action="{{ route('tagihansupplier.store') }}" method="POST" @submit="if(isSubmitting) { $event.preventDefault(); return false; } isSubmitting = true;">
                @csrf
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-[70vh] overflow-y-auto">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Supplier <span class="text-red-500">*</span></label>
                        <select name="supplier_id" required class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                            <option value="">Pilih Supplier...</option>
                            @foreach($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">No. Invoice <span class="text-red-500">*</span></label>
                        <input type="text" name="no_invoice" required placeholder="Contoh: INV/2024/001" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Tanggal Invoice <span class="text-red-500">*</span></label>
                        <input type="date" name="tgl_invoice" value="{{ date('Y-m-d') }}" required class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Jatuh Tempo <span class="text-red-500">*</span></label>
                        <input type="date" name="jatuh_tempo" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Total Tagihan (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" name="total" required placeholder="0" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Status <span class="text-red-500">*</span></label>
                        <select name="status" required class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                            <option value="belum_bayar">Belum Bayar</option>
                            <option value="cicilan">Cicilan</option>
                            <option value="lunas">Lunas</option>
                        </select>
                    </div>
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Catatan</label>
                        <textarea name="catatan" rows="2" placeholder="Keterangan tambahan..." class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 resize-none bg-white"></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/50">
                    <button type="button" @click="addModalOpen = false" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors shadow-sm">Batal</button>
                    <button type="button" @click="showConfirmSave = true" class="px-5 py-2 bg-[#0f172a] hover:bg-slate-800 rounded-lg text-sm font-bold text-white shadow transition-colors">Simpan Tagihan</button>
                </div>

                <!-- DOUBLE CONFIRMATION MODAL (ADD) -->
                <div x-show="showConfirmSave" class="fixed inset-0 z-[110] flex items-center justify-center" style="display: none;">
                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showConfirmSave = false"></div>
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm relative z-10 m-4 overflow-hidden border border-slate-200">
                        <div class="p-8 text-center">
                            <div class="w-20 h-20 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-6 ring-8 ring-blue-50/50">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-2">Simpan Tagihan Baru?</h3>
                            <p class="text-slate-500 leading-relaxed">Apakah Anda yakin data tagihan ini sudah benar? Hutang akan otomatis tercatat ke sistem.</p>
                        </div>
                        <div class="px-6 py-5 bg-slate-50 border-t border-slate-100 flex gap-3">
                            <button type="button" @click="showConfirmSave = false" :disabled="isSubmitting" class="flex-1 py-3 bg-white border border-slate-300 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all shadow-sm">Periksa Lagi</button>
                            <button type="submit" :disabled="isSubmitting" :class="{'opacity-70 cursor-wait': isSubmitting}" class="flex-1 py-3 bg-[#0f172a] hover:bg-slate-800 rounded-xl text-sm font-bold text-white transition-all shadow-lg shadow-slate-900/20">
                                <span x-show="!isSubmitting">Ya, Simpan</span>
                                <span x-show="isSubmitting" style="display: none;">Menyimpan...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT MODAL -->
    <div x-show="editModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center" style="display: none;">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="editModalOpen = false" x-transition.opacity></div>
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl relative z-10 m-4 overflow-hidden transform transition-all" x-transition>
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Ubah Tagihan Supplier</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Perbarui informasi tagihan</p>
                </div>
                <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-lg hover:bg-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form :action="`/tagihan-supplier/${editForm.id}`" method="POST" @submit="if(isSubmitting) { $event.preventDefault(); return false; } isSubmitting = true;">
                @csrf
                @method('PUT')
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-[70vh] overflow-y-auto">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Supplier <span class="text-red-500">*</span></label>
                        <select name="supplier_id" x-model="editForm.supplier_id" required class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                            @foreach($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">No. Invoice <span class="text-red-500">*</span></label>
                        <input type="text" name="no_invoice" x-model="editForm.no_invoice" required class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Tanggal Invoice <span class="text-red-500">*</span></label>
                        <input type="date" name="tgl_invoice" x-model="editForm.tgl_invoice" required class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Jatuh Tempo <span class="text-red-500">*</span></label>
                        <input type="date" name="jatuh_tempo" x-model="editForm.jatuh_tempo" required class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Total Tagihan (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" name="total" x-model="editForm.total" required class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Status <span class="text-red-500">*</span></label>
                        <select name="status" x-model="editForm.status" required class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                            <option value="belum_bayar">Belum Bayar</option>
                            <option value="cicilan">Cicilan</option>
                            <option value="lunas">Lunas</option>
                        </select>
                    </div>
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Catatan</label>
                        <textarea name="catatan" x-model="editForm.catatan" rows="2" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 resize-none bg-white"></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/50">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors shadow-sm">Batal</button>
                    <button type="button" @click="showConfirmEdit = true" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg text-sm font-bold text-white shadow transition-colors">Perbarui Tagihan</button>
                </div>

                <!-- DOUBLE CONFIRMATION MODAL (EDIT) -->
                <div x-show="showConfirmEdit" class="fixed inset-0 z-[110] flex items-center justify-center" style="display: none;">
                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showConfirmEdit = false"></div>
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm relative z-10 m-4 overflow-hidden border border-slate-200">
                        <div class="p-8 text-center">
                            <div class="w-20 h-20 bg-orange-50 text-orange-600 rounded-full flex items-center justify-center mx-auto mb-6 ring-8 ring-orange-50/50">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-2">Simpan Perubahan?</h3>
                            <p class="text-slate-500 leading-relaxed">Apakah Anda yakin ingin memperbarui data tagihan ini? Perubahan akan langsung berdampak pada laporan keuangan.</p>
                        </div>
                        <div class="px-6 py-5 bg-slate-50 border-t border-slate-100 flex gap-3">
                            <button type="button" @click="showConfirmEdit = false" :disabled="isSubmitting" class="flex-1 py-3 bg-white border border-slate-300 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all shadow-sm">Batal</button>
                            <button type="submit" :disabled="isSubmitting" :class="{'opacity-70 cursor-wait': isSubmitting}" class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm font-bold text-white transition-all shadow-lg shadow-blue-600/20">
                                <span x-show="!isSubmitting">Ya, Perbarui</span>
                                <span x-show="isSubmitting" style="display: none;">Memperbarui...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- DELETE MODAL -->
    <div x-show="deleteModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center" style="display: none;">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="deleteModalOpen = false" x-transition.opacity></div>
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm relative z-10 m-4 overflow-hidden transform transition-all" x-transition>
            <div class="p-6 text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-2">Hapus Tagihan?</h3>
                <p class="text-sm text-slate-500">Anda yakin ingin menghapus tagihan <span class="font-bold text-slate-700" x-text="deleteForm.no_invoice"></span>?</p>
            </div>
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex gap-3">
                <button type="button" @click="deleteModalOpen = false" :disabled="isSubmitting" class="flex-1 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors shadow-sm">Batal</button>
                <form :action="`/tagihan-supplier/${deleteForm.id}`" method="POST" class="flex-1" @submit="if(isSubmitting) { $event.preventDefault(); return false; } isSubmitting = true;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" :disabled="isSubmitting" :class="{'opacity-70 cursor-wait': isSubmitting}" class="w-full py-2 bg-red-600 hover:bg-red-700 rounded-lg text-sm font-bold text-white shadow transition-colors">
                        <span x-show="!isSubmitting">Ya, Hapus</span>
                        <span x-show="isSubmitting" style="display: none;">Menghapus...</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
