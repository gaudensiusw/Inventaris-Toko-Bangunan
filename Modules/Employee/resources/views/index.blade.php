@extends('layouts.app')

@section('title', 'Toko Bangunan - Karyawan')
@section('header_title', 'Manajemen Karyawan')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Manajemen Karyawan</h1>
            <p class="text-slate-600 mt-1">Kelola data karyawan, absensi, dan penggajian</p>
        </div>
        <button onclick="document.getElementById('modalTambah').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Karyawan
        </button>
    </div>

    <!-- Master-Detail Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Panel: Daftar Karyawan -->
        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-200 bg-white">
                <h2 class="text-lg font-bold text-slate-900">Daftar Karyawan</h2>
            </div>
            <div class="overflow-x-auto h-[600px] overflow-y-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 sticky top-0 z-10">
                        <tr>
                            <th class="py-3 px-5 font-semibold text-xs tracking-wider uppercase">No</th>
                            <th class="py-3 px-5 font-semibold text-xs tracking-wider uppercase">Nama Karyawan</th>
                            <th class="py-3 px-5 font-semibold text-xs tracking-wider uppercase">Jabatan</th>
                            <th class="py-3 px-5 font-semibold text-xs tracking-wider uppercase">Gaji Harian</th>
                            <th class="py-3 px-5 font-semibold text-xs tracking-wider uppercase">Status</th>
                            <th class="py-3 px-5 font-semibold text-xs tracking-wider uppercase text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($employees as $index => $emp)
                        <tr class="hover:bg-slate-50 transition-colors cursor-pointer employee-row" data-id="{{ $emp->id }}" onclick="loadEmployeeDetail({{ $emp->id }})">
                            <td class="py-4 px-5 text-slate-500">{{ $index + 1 }}</td>
                            <td class="py-4 px-5">
                                <div class="font-bold text-slate-900">{{ $emp->nama }}</div>
                            </td>
                            <td class="py-4 px-5 text-slate-700 font-medium">{{ $emp->jabatan->nama_jabatan ?? '-' }}</td>
                            <td class="py-4 px-5 text-slate-600">Rp {{ number_format($emp->jabatan->gaji_harian ?? 0, 0, ',', '.') }}</td>
                            <td class="py-4 px-5">
                                @if($emp->aktif)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                        Tidak Aktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-5 text-right space-x-2">
                                <button onclick="event.stopPropagation(); openEditModal({{ $emp->id }}, '{{ $emp->kode_karyawan }}', '{{ addslashes($emp->nama) }}', '{{ $emp->jabatan_id }}', '{{ $emp->tanggal_masuk->format('Y-m-d') }}', {{ $emp->aktif ? 'true' : 'false' }}, '{{ $emp->no_hp }}', '{{ $emp->email }}', '{{ addslashes($emp->alamat) }}', {{ $emp->bonus_tetap ?? 500000 }})" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-500 hover:text-orange-600 hover:bg-orange-50 transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <button onclick="event.stopPropagation(); deleteEmployee({{ $emp->id }})" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-500 hover:text-red-600 hover:bg-red-50 transition-colors" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right Panel: Detail Panel -->
        <div class="lg:col-span-1 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden flex flex-col h-[600px]">
            <div class="p-5 border-b border-slate-200 bg-white">
                <h2 class="text-lg font-bold text-slate-900">Detail Karyawan</h2>
            </div>
            
            <!-- Default State: Empty -->
            <div id="detailEmptyState" class="flex-1 flex flex-col items-center justify-center p-6 text-center text-slate-500">
                <svg class="w-16 h-16 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <p>Pilih karyawan dari daftar untuk melihat detail.</p>
            </div>

            <!-- Detail Content -->
            <div id="detailContent" class="hidden flex-1 overflow-y-auto p-5 space-y-6">
                <!-- Info Utama -->
                <div class="text-center">
                    <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 mx-auto mb-3 text-2xl font-bold" id="detailInisial">A</div>
                    <h3 class="text-xl font-bold text-slate-900" id="detailNama">Nama Karyawan</h3>
                    <p class="text-blue-600 font-medium" id="detailJabatan">Jabatan</p>
                    <p class="text-slate-500 text-sm mt-1" id="detailTanggalMasuk">Bergabung: -</p>
                </div>

                <hr class="border-slate-200">

                <!-- Gaji -->
                <div class="bg-blue-50/50 rounded-xl border border-blue-100 p-5 mt-4">
                    <h4 class="text-sm font-semibold text-blue-900 mb-4">Estimasi Gaji Bulan Ini</h4>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-600">Gaji Pokok: <span id="detailHariHadir">0</span> hari &times; <span id="detailGajiHarian">Rp 0</span></span>
                            <span class="font-medium text-slate-800" id="detailTotalGajiPokok">Rp 0</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-600">Bonus</span>
                            <span class="font-medium text-slate-800" id="detailBonus">Rp 500.000</span>
                        </div>
                        <div class="pt-3 border-t border-blue-200/60 flex justify-between items-center">
                            <span class="font-bold text-slate-900">Total</span>
                            <span class="font-bold text-blue-700 text-xl" id="detailTotalGaji">Rp 0</span>
                        </div>
                    </div>
                </div>

                <!-- Generate Slip Button -->
                <a href="#" id="btnGenerateSlip" class="w-full mt-4 bg-slate-900 hover:bg-slate-800 text-white rounded-lg py-3 flex items-center justify-center gap-2 font-medium transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Generate Slip Gaji
                </a>

                <!-- Statistik Kehadiran -->
                <div>
                    <h4 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3">Statistik Kehadiran (Bulan Ini)</h4>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-green-50 p-3 rounded-lg border border-green-100 flex flex-col justify-center items-center">
                            <span class="text-2xl font-bold text-green-700" id="statHadir">0</span>
                            <span class="text-xs text-green-600 uppercase font-semibold">Hadir</span>
                        </div>
                        <div class="bg-yellow-50 p-3 rounded-lg border border-yellow-100 flex flex-col justify-center items-center">
                            <span class="text-2xl font-bold text-yellow-700" id="statIzin">0</span>
                            <span class="text-xs text-yellow-600 uppercase font-semibold">Izin/Cuti</span>
                        </div>
                        <div class="bg-orange-50 p-3 rounded-lg border border-orange-100 flex flex-col justify-center items-center">
                            <span class="text-2xl font-bold text-orange-700" id="statSakit">0</span>
                            <span class="text-xs text-orange-600 uppercase font-semibold">Sakit</span>
                        </div>
                        <div class="bg-red-50 p-3 rounded-lg border border-red-100 flex flex-col justify-center items-center">
                            <span class="text-2xl font-bold text-red-700" id="statAlpha">0</span>
                            <span class="text-xs text-red-600 uppercase font-semibold">Alpha</span>
                        </div>
                    </div>
                </div>

                <!-- Kalender Absensi -->
                <div class="mt-8">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-base font-bold text-slate-900">Kalender Absensi</h4>
                        <div class="flex items-center gap-3">
                            <button class="p-1 rounded hover:bg-slate-100 text-slate-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            </button>
                            <span class="text-sm font-medium text-slate-700" id="calendarMonthName">Mei 2026</span>
                            <button class="p-1 rounded hover:bg-slate-100 text-slate-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        </div>
                    </div>
                    
                    <div class="border border-slate-200 rounded-lg overflow-hidden">
                        <!-- Days Header -->
                        <div class="grid grid-cols-7 bg-slate-50 border-b border-slate-200 text-xs font-medium text-slate-500 text-center py-2">
                            <div>Min</div><div>Sen</div><div>Sel</div><div>Rab</div><div>Kam</div><div>Jum</div><div>Sab</div>
                        </div>
                        <!-- Calendar Grid -->
                        <div class="grid grid-cols-7 text-sm" id="calendarGrid">
                            <!-- Filled by JS -->
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Karyawan -->
<div id="modalTambah" class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
        <div class="p-5 border-b border-slate-200 flex justify-between items-center">
            <h2 class="text-lg font-bold">Tambah Karyawan Baru</h2>
            <button onclick="document.getElementById('modalTambah').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form action="{{ route('employee.store') }}" method="POST" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                <input type="text" name="nama" required class="w-full border-slate-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Jabatan</label>
                <select name="jabatan_id" required class="w-full border-slate-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Pilih Jabatan...</option>
                    @foreach($jabatans as $j)
                        <option value="{{ $j->id }}">{{ $j->nama_jabatan }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Masuk</label>
                <input type="date" name="tanggal_masuk" value="{{ date('Y-m-d') }}" required class="w-full border-slate-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="document.getElementById('modalTambah').classList.add('hidden')" class="px-4 py-2 text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg font-medium">Batal</button>
                <button type="submit" class="px-4 py-2 text-white bg-blue-600 hover:bg-blue-700 rounded-lg font-medium">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Karyawan -->
<div id="modalEdit" class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex justify-between items-start bg-white">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Edit Karyawan</h2>
                <p class="text-sm text-slate-500 mt-1">Update informasi karyawan</p>
            </div>
            <button onclick="document.getElementById('modalEdit').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 rounded-full p-1 hover:bg-slate-100 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <form id="formEdit" method="POST" class="space-y-5">
                @csrf
                @method('PUT')
                
                <!-- Row 1: Kode Karyawan & Status -->
                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Kode Karyawan <span class="text-red-500">*</span></label>
                        <input type="text" id="editKode" name="kode_karyawan" readonly class="w-full bg-slate-100 border-transparent text-slate-500 rounded-lg shadow-sm focus:ring-0 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Status <span class="text-red-500">*</span></label>
                        <select id="editStatus" name="aktif" required class="w-full border-slate-200 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
                    </div>
                </div>

                <!-- Row 2: Nama Lengkap -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" id="editNama" name="nama" required class="w-full border-slate-200 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Row 3: Posisi & Tanggal Bergabung -->
                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Posisi <span class="text-red-500">*</span></label>
                        <select id="editJabatan" name="jabatan_id" required class="w-full border-slate-200 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" onchange="updateEditGajiPokok()">
                            @foreach($jabatans as $j)
                                <option value="{{ $j->id }}" data-gaji="{{ $j->gaji_harian }}">{{ $j->nama_jabatan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Tanggal Bergabung <span class="text-red-500">*</span></label>
                        <input type="date" id="editTanggal" name="tanggal_masuk" required class="w-full border-slate-200 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Row 4: Telepon & Email -->
                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Telepon <span class="text-red-500">*</span></label>
                        <input type="text" id="editTelepon" name="no_hp" class="w-full border-slate-200 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Email</label>
                        <input type="email" id="editEmail" name="email" class="w-full border-slate-200 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Row 5: Alamat -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Alamat</label>
                    <textarea id="editAlamat" name="alamat" rows="2" class="w-full border-slate-200 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>

                <!-- Informasi Gaji Section -->
                <div class="pt-4 border-t border-slate-100">
                    <h3 class="text-base font-bold text-slate-900 mb-4">Informasi Gaji</h3>
                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Gaji Pokok per Hari <span class="text-red-500">*</span></label>
                            <input type="text" id="editGajiPokok" readonly class="w-full bg-slate-50 border-slate-200 text-slate-600 rounded-lg shadow-sm cursor-not-allowed">
                            <p class="text-xs text-slate-500 mt-1">Gaji yang diterima untuk setiap hari hadir</p>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Bonus Tetap</label>
                            <input type="number" id="editBonus" name="bonus_tetap" value="500000" class="w-full border-slate-200 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-xs text-slate-500 mt-1">Bonus yang diberikan setiap bulan</p>
                        </div>
                    </div>
                </div>

            </form>
        </div>
        <div class="p-6 border-t border-slate-100 bg-white grid grid-cols-2 gap-4">
            <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')" class="w-full py-3 text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl font-bold transition-colors shadow-sm">Batal</button>
            <button type="submit" form="formEdit" class="w-full py-3 text-white bg-[#0A0F2C] hover:bg-[#111942] rounded-xl font-bold transition-colors shadow-sm">Update</button>
        </div>
    </div>
</div>

<!-- Modal Preview Slip Gaji -->
<div id="modalSlip" class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col">
        <!-- Header Blue Gradient -->
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-6 text-white flex justify-between items-start m-4 rounded-xl shadow-md">
            <div>
                <h2 class="text-xl font-bold" id="slipNama">Nama Karyawan</h2>
                <p class="text-blue-100 text-sm mt-1"><span id="slipJabatan">Jabatan</span> - <span id="slipKode">EMP-000</span></p>
            </div>
            <div class="text-right">
                <p class="text-blue-200 text-xs uppercase tracking-wider font-bold">Periode</p>
                <p class="font-bold text-sm" id="slipPeriode">Mei 2026</p>
            </div>
        </div>

        <div class="p-6 pt-2 space-y-6 flex-1 overflow-y-auto">
            <!-- Ringkasan Kehadiran -->
            <div>
                <h3 class="text-sm font-bold text-slate-800 mb-3">Ringkasan Kehadiran</h3>
                <div class="grid grid-cols-4 gap-3">
                    <div class="flex flex-col items-center justify-center">
                        <div class="w-10 h-10 rounded-full bg-green-50 text-green-500 flex items-center justify-center mb-1 border border-green-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <span class="font-bold text-slate-900" id="slipHadir">0</span>
                        <span class="text-[10px] text-slate-500 uppercase">Hadir</span>
                    </div>
                    <div class="flex flex-col items-center justify-center">
                        <div class="w-10 h-10 rounded-full bg-red-50 text-red-500 flex items-center justify-center mb-1 border border-red-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </div>
                        <span class="font-bold text-slate-900" id="slipAlpha">0</span>
                        <span class="text-[10px] text-slate-500 uppercase">Tidak Hadir</span>
                    </div>
                    <div class="flex flex-col items-center justify-center">
                        <div class="w-10 h-10 rounded-full bg-yellow-50 text-yellow-500 flex items-center justify-center mb-1 border border-yellow-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="font-bold text-slate-900" id="slipSakit">0</span>
                        <span class="text-[10px] text-slate-500 uppercase">Sakit</span>
                    </div>
                    <div class="flex flex-col items-center justify-center">
                        <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center mb-1 border border-blue-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <span class="font-bold text-slate-900" id="slipIzin">0</span>
                        <span class="text-[10px] text-slate-500 uppercase">Cuti</span>
                    </div>
                </div>
            </div>

            <!-- Rincian Gaji -->
            <div class="border border-slate-100 rounded-xl p-5 shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 mb-4">Rincian Gaji</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 text-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Gaji Pokok
                        </span>
                        <span class="text-slate-800 text-sm font-medium"><span id="slipHariHadir">0</span> hari &times; <span id="slipGajiHarian">Rp 0</span></span>
                    </div>
                    <div class="flex justify-between items-center pl-6 py-2 border-b border-slate-100">
                        <span class="text-slate-500 text-sm">Subtotal Gaji Pokok</span>
                        <span class="text-slate-800 text-sm font-bold" id="slipSubtotal">Rp 0</span>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-b border-slate-100 pb-3">
                        <span class="text-slate-600 text-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            Bonus
                        </span>
                        <span class="text-green-600 font-bold" id="slipBonus">Rp 0</span>
                    </div>
                    <div class="flex justify-between items-center pt-2 bg-blue-50/50 p-3 rounded-lg border border-blue-100">
                        <span class="font-bold text-blue-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            Total Gaji
                        </span>
                        <span class="font-bold text-blue-700 text-xl" id="slipTotal">Rp 0</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6 border-t border-slate-100 grid grid-cols-2 gap-4 bg-white">
            <button type="button" onclick="document.getElementById('modalSlip').classList.add('hidden')" class="w-full py-3 text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl font-bold transition-colors">Tutup</button>
            <button type="button" id="btnProsesPembayaran" class="w-full py-3 text-white bg-[#0A0F2C] hover:bg-[#111942] rounded-xl font-bold transition-colors flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Proses Pembayaran
            </button>
        </div>
    </div>
</div>

<!-- Modal Input Absensi -->
<div id="modalAbsensi" class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col">
        <div class="p-6 border-b border-slate-100 flex justify-between items-start bg-white">
            <div>
                <h2 class="text-xl font-bold text-slate-900" id="absensiModalTitle">Input Absensi</h2>
                <p class="text-sm text-slate-500 mt-1">Masukkan data kehadiran secara manual</p>
            </div>
            <button onclick="document.getElementById('modalAbsensi').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 rounded-full p-1 hover:bg-slate-100 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <form id="formAbsensi" onsubmit="submitAbsensi(event)" class="p-6 space-y-4">
            <input type="hidden" id="absensiKaryawanId" name="karyawan_id">
            <input type="hidden" id="absensiTanggal" name="tanggal">
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Status Kehadiran <span class="text-red-500">*</span></label>
                <select id="absensiStatus" name="status" required class="w-full border-slate-200 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="hadir">Hadir</option>
                    <option value="alpha">Tidak Hadir / Alpha</option>
                    <option value="sakit">Sakit</option>
                    <option value="izin">Cuti / Izin</option>
                </select>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Jam Masuk</label>
                    <input type="time" id="absensiJamMasuk" name="jam_masuk" class="w-full border-slate-200 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Jam Keluar</label>
                    <input type="time" id="absensiJamKeluar" name="jam_keluar" class="w-full border-slate-200 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Catatan</label>
                <textarea id="absensiCatatan" name="catatan" rows="3" class="w-full border-slate-200 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Tambahkan keterangan jika perlu..."></textarea>
            </div>
            
            <div id="absensiWarningPaid" class="hidden p-3 bg-yellow-50 text-yellow-800 text-xs rounded-lg border border-yellow-200">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Data absensi ini sudah dibayarkan dan tidak dapat diubah.
            </div>

            <div class="pt-4 flex gap-3">
                <button type="button" id="btnHapusAbsensi" onclick="deleteAbsensi()" class="hidden w-1/3 py-3 text-white bg-red-600 hover:bg-red-700 rounded-xl font-bold transition-colors shadow-sm flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Hapus
                </button>
                <button type="button" onclick="document.getElementById('modalAbsensi').classList.add('hidden')" class="flex-1 py-3 text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl font-bold transition-colors shadow-sm">Batal</button>
                <button type="submit" id="btnSimpanAbsensi" class="flex-1 py-3 text-white bg-[#0A0F2C] hover:bg-[#111942] rounded-xl font-bold transition-colors shadow-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Form Hapus (Hidden) -->
<form id="formHapus" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    // Format currency IDR
    const formatRp = (angka) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
    };

    // Load Employee Detail
    function loadEmployeeDetail(id) {
        // Highlight active row
        document.querySelectorAll('.employee-row').forEach(row => {
            row.classList.remove('bg-blue-50', 'border-l-4', 'border-blue-600');
        });
        const activeRow = document.querySelector(`.employee-row[data-id="${id}"]`);
        if (activeRow) {
            activeRow.classList.add('bg-blue-50', 'border-l-4', 'border-blue-600');
        }

        // Fetch Data
        fetch(`/employees/${id}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('detailEmptyState').classList.add('hidden');
                document.getElementById('detailContent').classList.remove('hidden');

                const emp = data.employee;
                const rekap = data.rekap_absensi;
                
                // Populate Info
                document.getElementById('detailInisial').textContent = emp.nama.charAt(0).toUpperCase();
                document.getElementById('detailNama').textContent = emp.nama;
                document.getElementById('detailJabatan').textContent = emp.jabatan ? emp.jabatan.nama_jabatan : '-';
                
                // Format Date
                const tglMasuk = new Date(emp.tanggal_masuk).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
                document.getElementById('detailTanggalMasuk').textContent = `Bergabung: ${tglMasuk}`;

                // Gaji
                const gajiHarian = emp.jabatan ? parseFloat(emp.jabatan.gaji_harian) : 0;
                const hadir = rekap.hadir;
                const totalGajiPokok = hadir * gajiHarian;
                const bonus = parseFloat(emp.bonus_tetap || 0);
                const totalGaji = totalGajiPokok + bonus;
                
                document.getElementById('detailHariHadir').textContent = hadir;
                document.getElementById('detailGajiHarian').textContent = formatRp(gajiHarian);
                document.getElementById('detailTotalGajiPokok').textContent = formatRp(totalGajiPokok);
                document.getElementById('detailBonus').textContent = formatRp(bonus);
                document.getElementById('detailTotalGaji').textContent = formatRp(totalGaji);

                // Button Generate Slip
                document.getElementById('btnGenerateSlip').onclick = function(e) {
                    e.preventDefault();
                    openPreviewModal(emp, rekap, gajiHarian, totalGajiPokok, bonus, totalGaji);
                };

                // Rekap
                document.getElementById('statHadir').textContent = rekap.hadir;
                document.getElementById('statIzin').textContent = rekap.izin;
                document.getElementById('statSakit').textContent = rekap.sakit;
                document.getElementById('statAlpha').textContent = rekap.alpha;

                // Riwayat Kalender
                const date = new Date();
                const year = date.getFullYear();
                const month = date.getMonth();
                
                const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                document.getElementById('calendarMonthName').textContent = `${monthNames[month]} ${year}`;
                
                const firstDay = new Date(year, month, 1).getDay(); // 0 (Sun) to 6 (Sat)
                const daysInMonth = new Date(year, month + 1, 0).getDate();
                
                const calendarGrid = document.getElementById('calendarGrid');
                calendarGrid.innerHTML = '';
                
                let dayCounter = 1;
                for (let i = 0; i < 42; i++) {
                    const cell = document.createElement('div');
                    cell.className = 'border-b border-r border-slate-200 h-20 p-1 flex flex-col relative';
                    
                    if (i >= firstDay && dayCounter <= daysInMonth) {
                        const dateString = `${year}-${String(month + 1).padStart(2, '0')}-${String(dayCounter).padStart(2, '0')}`;
                        cell.innerHTML = `<span class="text-xs font-medium text-slate-700 absolute top-1 left-2">${dayCounter}</span>`;
                        
                        cell.className += ' hover:bg-slate-100 cursor-pointer transition-colors';
                        cell.onclick = () => openAbsensiModal(id, dateString, data.absensi_harian);
                        
                        const statusData = data.kalender_absensi ? data.kalender_absensi[dateString] : null;
                        
                        if (statusData) {
                            const status = statusData.status;
                            const isPaid = statusData.status_bayar == 1;

                            let colorClass = 'text-slate-600';
                            let bgClass = 'bg-slate-100';
                            if (status === 'hadir') { colorClass = 'text-green-700'; bgClass = 'bg-green-50 border border-green-100'; }
                            if (status === 'izin') { colorClass = 'text-yellow-700'; bgClass = 'bg-yellow-50 border border-yellow-100'; }
                            if (status === 'sakit') { colorClass = 'text-orange-700'; bgClass = 'bg-orange-50 border border-orange-100'; }
                            if (status === 'alpha') { colorClass = 'text-red-700'; bgClass = 'bg-red-50 border border-red-100'; }

                            if (isPaid) {
                                bgClass += ' opacity-60';
                            }

                            cell.innerHTML += `
                                <div class="mt-auto mb-1 mx-1">
                                    <div class="px-1 py-0.5 text-[10px] rounded font-semibold uppercase text-center truncate ${bgClass} ${colorClass}">
                                        ${status}
                                    </div>
                                    ${isPaid ? '<div class="text-[9px] text-center text-slate-500 font-bold mt-0.5 flex items-center justify-center gap-1"><svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Paid</div>' : ''}
                                </div>
                            `;
                        } else {
                            if(dayCounter <= date.getDate()){
                                cell.innerHTML += `
                                    <div class="mt-auto mb-1 mx-1">
                                        <div class="px-1 py-0.5 text-[10px] rounded text-slate-400 uppercase text-center truncate bg-slate-50 border border-slate-100">
                                            0
                                        </div>
                                    </div>
                                `;
                            }
                        }
                        dayCounter++;
                    } else {
                        cell.className += ' bg-slate-50/50';
                    }
                    calendarGrid.appendChild(cell);
                    if(dayCounter > daysInMonth && i % 7 === 6) break;
                }
            })
            .catch(error => {
                console.error('Error fetching employee detail:', error);
                alert('Gagal mengambil data karyawan.');
            });
    }

    // Modal Edit
    function openEditModal(id, kode, nama, jabatan_id, tanggal_masuk, aktif, telepon, email, alamat, bonus) {
        document.getElementById('formEdit').action = `/employees/${id}`;
        document.getElementById('editKode').value = kode || '';
        document.getElementById('editNama').value = nama || '';
        document.getElementById('editJabatan').value = jabatan_id;
        document.getElementById('editTanggal').value = tanggal_masuk;
        document.getElementById('editStatus').value = aktif ? '1' : '0';
        document.getElementById('editTelepon').value = telepon || '';
        document.getElementById('editEmail').value = email || '';
        document.getElementById('editAlamat').value = alamat || '';
        document.getElementById('editBonus').value = bonus || 0;
        
        updateEditGajiPokok();
        document.getElementById('modalEdit').classList.remove('hidden');
    }

    function updateEditGajiPokok() {
        const select = document.getElementById('editJabatan');
        if(select.selectedIndex === -1) return;
        const selectedOption = select.options[select.selectedIndex];
        const gaji = selectedOption.getAttribute('data-gaji') || 0;
        document.getElementById('editGajiPokok').value = formatRp(gaji);
    }
    
    function openPreviewModal(emp, rekap, gajiHarian, totalGajiPokok, bonus, totalGaji) {
        document.getElementById('slipNama').textContent = emp.nama;
        document.getElementById('slipJabatan').textContent = emp.jabatan ? emp.jabatan.nama_jabatan : '-';
        document.getElementById('slipKode').textContent = emp.kode_karyawan || 'EMP-000';
        document.getElementById('slipPeriode').textContent = document.getElementById('calendarMonthName').textContent;
        
        document.getElementById('slipHadir').textContent = rekap.hadir;
        document.getElementById('slipAlpha').textContent = rekap.alpha;
        document.getElementById('slipSakit').textContent = rekap.sakit;
        document.getElementById('slipIzin').textContent = rekap.izin;
        
        document.getElementById('slipHariHadir').textContent = rekap.hadir;
        document.getElementById('slipGajiHarian').textContent = formatRp(gajiHarian);
        document.getElementById('slipSubtotal').textContent = formatRp(totalGajiPokok);
        document.getElementById('slipBonus').textContent = formatRp(bonus);
        document.getElementById('slipTotal').textContent = formatRp(totalGaji);
        
        document.getElementById('btnProsesPembayaran').onclick = function() {
            const btn = document.getElementById('btnProsesPembayaran');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = 'Memproses...';
            btn.disabled = true;

            fetch(`/employees/${emp.id}/bayar-gaji`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    window.open(data.download_url, '_blank');
                    document.getElementById('modalSlip').classList.add('hidden');
                    loadEmployeeDetail(emp.id); // Refresh data
                } else {
                    alert('Gagal memproses pembayaran: ' + (data.message || 'Error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memproses pembayaran.');
            })
            .finally(() => {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            });
        };
        
        document.getElementById('modalSlip').classList.remove('hidden');
    }

    // Modal Input Absensi
    function openAbsensiModal(id, dateString, absensiHarian) {
        document.getElementById('formAbsensi').reset();
        
        document.getElementById('absensiKaryawanId').value = id;
        document.getElementById('absensiTanggal').value = dateString;
        
        // Format date for title (e.g., 30 Apr 2026)
        const dateObj = new Date(dateString);
        const options = { day: 'numeric', month: 'short', year: 'numeric' };
        document.getElementById('absensiModalTitle').textContent = `Input Absensi - ${dateObj.toLocaleDateString('id-ID', options)}`;
        
        // Auto-fill logic
        const existingData = absensiHarian.find(ab => ab.tanggal === dateString || ab.tanggal.startsWith(dateString));
        
        if (existingData) {
            document.getElementById('absensiStatus').value = existingData.status;
            document.getElementById('absensiJamMasuk').value = existingData.jam_masuk ? existingData.jam_masuk.substring(0,5) : '';
            document.getElementById('absensiJamKeluar').value = existingData.jam_keluar ? existingData.jam_keluar.substring(0,5) : '';
            document.getElementById('absensiCatatan').value = existingData.catatan || '';
            
            if (existingData.status_bayar == 1) {
                document.getElementById('absensiStatus').disabled = true;
                document.getElementById('absensiJamMasuk').disabled = true;
                document.getElementById('absensiJamKeluar').disabled = true;
                document.getElementById('absensiCatatan').disabled = true;

                document.getElementById('btnSimpanAbsensi').classList.add('hidden');
                document.getElementById('btnHapusAbsensi').classList.add('hidden');
                document.getElementById('absensiWarningPaid').classList.remove('hidden');
            } else {
                document.getElementById('absensiStatus').disabled = false;
                document.getElementById('absensiJamMasuk').disabled = false;
                document.getElementById('absensiJamKeluar').disabled = false;
                document.getElementById('absensiCatatan').disabled = false;

                document.getElementById('btnSimpanAbsensi').classList.remove('hidden');
                document.getElementById('btnHapusAbsensi').classList.remove('hidden');
                document.getElementById('absensiWarningPaid').classList.add('hidden');
            }
        } else {
            document.getElementById('absensiStatus').value = 'hadir';
            document.getElementById('absensiJamMasuk').value = '';
            document.getElementById('absensiJamKeluar').value = '';
            document.getElementById('absensiCatatan').value = '';
            
            document.getElementById('absensiStatus').disabled = false;
            document.getElementById('absensiJamMasuk').disabled = false;
            document.getElementById('absensiJamKeluar').disabled = false;
            document.getElementById('absensiCatatan').disabled = false;

            document.getElementById('btnHapusAbsensi').classList.add('hidden');
            document.getElementById('btnSimpanAbsensi').classList.remove('hidden');
            document.getElementById('absensiWarningPaid').classList.add('hidden');
        }
        
        document.getElementById('modalAbsensi').classList.remove('hidden');
    }

    // Submit Absensi via AJAX
    function submitAbsensi(event) {
        event.preventDefault();
        const form = document.getElementById('formAbsensi');
        const formData = new FormData(form);
        const id = document.getElementById('absensiKaryawanId').value;
        
        fetch(`/employees/${id}/absensi/store`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                document.getElementById('modalAbsensi').classList.add('hidden');
                loadEmployeeDetail(id); // Refresh detail panel and calendar
            } else {
                alert('Gagal menyimpan absensi.');
            }
        })
        .catch(error => {
            console.error('Error submitting absensi:', error);
            alert('Terjadi kesalahan saat menyimpan absensi.');
        });
    }

    // Delete Absensi
    function deleteAbsensi() {
        const id = document.getElementById('absensiKaryawanId').value;
        const tanggal = document.getElementById('absensiTanggal').value;
        
        if (!confirm('Apakah Anda yakin ingin menghapus data absensi ini?')) return;

        fetch(`/employees/${id}/absensi/destroy`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ tanggal: tanggal })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                document.getElementById('modalAbsensi').classList.add('hidden');
                loadEmployeeDetail(id); // Refresh detail panel and calendar
            } else {
                alert('Gagal menghapus absensi: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error deleting absensi:', error);
            alert('Terjadi kesalahan saat menghapus absensi.');
        });
    }

    // Modal Delete
    function deleteEmployee(id) {
        if (confirm('Apakah Anda yakin ingin menonaktifkan karyawan ini?')) {
            const form = document.getElementById('formHapus');
            form.action = `/employees/${id}`;
            form.submit();
        }
    }
</script>
@endsection
