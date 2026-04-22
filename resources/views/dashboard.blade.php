@extends('layouts.app')

@section('title', 'Dashboard - Toko Bangunan IMS')
@section('header_title', 'Selamat Datang, ' . (auth()->user()->name ?? 'Admin') . '!')

@section('content')
<div class="space-y-6">
    <div>
        <p class="text-slate-600">Ringkasan inventori dan sumber daya manusia (HR) hari ini.</p>
    </div>

    <!-- Stats Cards Row 1: HR Focus -->
    <h3 class="text-lg font-bold text-slate-800">Human Resources & Payroll</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Active Employees -->
        <div class="rounded-xl border border-slate-200 bg-white text-slate-950 shadow-sm">
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium">Total Active Employees</h3>
                <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <div class="p-6 pt-0">
                <div class="text-2xl font-bold text-blue-600">24</div>
                <p class="text-xs text-slate-500 mt-1">Karyawan berstatus aktif</p>
            </div>
        </div>

        <!-- Today's Attendance -->
        <div class="rounded-xl border border-slate-200 bg-white text-slate-950 shadow-sm">
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium">Today's Attendance</h3>
                <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="p-6 pt-0">
                <div class="text-2xl font-bold text-green-600">22 / 24</div>
                <p class="text-xs text-slate-500 mt-1">2 Izin/Sakit hari ini</p>
            </div>
        </div>

        <!-- Pending Payroll -->
        <div class="rounded-xl border border-slate-200 bg-white text-slate-950 shadow-sm">
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium">Pending Payroll (Minggu Ini)</h3>
                <svg class="h-4 w-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="p-6 pt-0">
                <div class="text-2xl font-bold text-orange-600">Rp 12.5M</div>
                <p class="text-xs text-slate-500 mt-1">Estimasi gaji terutang</p>
            </div>
        </div>

        <!-- Face Recognition Scans -->
        <div class="rounded-xl border border-slate-200 bg-white text-slate-950 shadow-sm">
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium">Face Scans Hari Ini</h3>
                <svg class="h-4 w-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
            <div class="p-6 pt-0">
                <div class="text-2xl font-bold text-purple-600">45</div>
                <p class="text-xs text-slate-500 mt-1">Log absensi diproses</p>
            </div>
        </div>
    </div>

    <!-- Stats Cards Row 2: Store/Inventory Focus -->
    <h3 class="text-lg font-bold text-slate-800 mt-8">Store & Inventory Overview</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Revenue -->
        <div class="rounded-xl border border-slate-200 bg-white text-slate-950 shadow-sm">
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium">Pendapatan (Bulan Ini)</h3>
                <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div class="p-6 pt-0">
                <div class="text-2xl font-bold text-green-600">Rp 45.2M</div>
                <p class="text-xs text-slate-500 mt-1">124 transaksi tunai/transfer</p>
            </div>
        </div>

        <!-- Receivables -->
        <div class="rounded-xl border border-slate-200 bg-white text-slate-950 shadow-sm">
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium">Piutang (Kredit)</h3>
                <svg class="h-4 w-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
            </div>
            <div class="p-6 pt-0">
                <div class="text-2xl font-bold text-orange-600">Rp 12.8M</div>
                <p class="text-xs text-slate-500 mt-1">15 invoice belum lunas</p>
            </div>
        </div>

        <!-- Profit -->
        <div class="rounded-xl border border-slate-200 bg-white text-slate-950 shadow-sm">
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium">Laba Bersih</h3>
                <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            </div>
            <div class="p-6 pt-0">
                <div class="text-2xl font-bold text-blue-600">Rp 18.5M</div>
                <p class="text-xs text-slate-500 mt-1">Margin: 24.5%</p>
            </div>
        </div>

        <!-- Stock Value -->
        <div class="rounded-xl border border-slate-200 bg-white text-slate-950 shadow-sm">
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium">Nilai Stok</h3>
                <svg class="h-4 w-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
            <div class="p-6 pt-0">
                <div class="text-2xl font-bold">Rp 125.0M</div>
                <p class="text-xs text-slate-500 mt-1">320 produk (12 stok rendah)</p>
            </div>
        </div>
    </div>
</div>
@endsection
