@extends('layouts.app')

@section('title', 'Toko Bangunan - Tagihan Supplier')
@section('header_title', 'Tagihan Supplier')

@section('content')
<!-- Stats Row -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <!-- Total Hutang -->
    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-50 text-[#2563eb] rounded-full flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1">Total Hutang</p>
            <h3 class="text-xl font-bold text-slate-800">Rp 149.090.000</h3>
        </div>
    </div>
    
    <!-- Jatuh Tempo -->
    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 bg-red-50 text-red-500 rounded-full flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1">Jatuh Tempo</p>
            <h3 class="text-xl font-bold text-slate-800">Rp 0</h3>
        </div>
    </div>

    <!-- Bulan Ini -->
    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 bg-orange-50 text-orange-500 rounded-full flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1">Bulan Ini</p>
            <h3 class="text-xl font-bold text-slate-800">Rp 45.000.000</h3>
        </div>
    </div>

    <!-- Akan Datang -->
    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 bg-green-50 text-green-500 rounded-full flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1">Akan Datang</p>
            <h3 class="text-xl font-bold text-slate-800">Rp 149.090.000</h3>
        </div>
    </div>
</div>

<!-- Calendar Section -->
<div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
    <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
        <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            Kalender Jatuh Tempo
        </h3>
        <div class="flex items-center gap-3">
            <button class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </button>
            <span class="font-bold text-sm">March 2026</span>
            <button class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </button>
        </div>
    </div>
    <div class="p-0">
        <div class="grid grid-cols-7 border-b border-slate-200 bg-slate-100 text-center text-xs font-bold text-slate-500 uppercase tracking-widest divide-x divide-slate-200">
            <div class="py-2">Sun</div>
            <div class="py-2">Mon</div>
            <div class="py-2">Tue</div>
            <div class="py-2">Wed</div>
            <div class="py-2">Thu</div>
            <div class="py-2">Fri</div>
            <div class="py-2">Sat</div>
        </div>
        
        <!-- Mocked grid for March 2026 -->
        <div class="grid grid-cols-7 bg-white text-sm divide-x divide-y divide-slate-200">
            <!-- Row 1 -->
            <div class="min-h-[100px] p-2 bg-slate-50 text-slate-400 font-medium">1</div>
            <div class="min-h-[100px] p-2 text-slate-700 font-medium">2</div>
            <div class="min-h-[100px] p-2 text-slate-700 font-medium">3</div>
            <div class="min-h-[100px] p-2 text-slate-700 font-medium">4</div>
            <div class="min-h-[100px] p-2 text-slate-700 font-medium">5</div>
            <div class="min-h-[100px] p-2 text-slate-700 font-medium">6</div>
            <div class="min-h-[100px] p-2 text-slate-700 font-medium bg-slate-50">7</div>
            
            <!-- Row 2 -->
            <div class="min-h-[100px] p-2 bg-slate-50 text-slate-400 font-medium">8</div>
            <div class="min-h-[100px] p-2 text-slate-700 font-medium">9</div>
            <div class="min-h-[100px] p-2 text-slate-700 font-medium">10</div>
            <div class="min-h-[100px] p-2 text-slate-700 font-medium">11</div>
            <div class="min-h-[100px] p-2 text-slate-700 font-medium">12</div>
            <div class="min-h-[100px] p-2 text-slate-700 font-medium">13</div>
            <div class="min-h-[100px] p-2 text-slate-700 font-medium bg-slate-50">14</div>
            
            <!-- Row 3 -->
            <div class="min-h-[100px] p-2 bg-slate-50 text-slate-400 font-medium">15</div>
            <div class="min-h-[100px] p-2 text-slate-700 font-medium">16</div>
            
            <!-- CURRENT DATE -->
            <div class="min-h-[100px] p-2 text-slate-700 font-medium border-2 border-[#2563eb] relative bg-blue-50/30">
                17
            </div>
            
            <!-- HIGHLIGHT DATE -->
            <div class="min-h-[100px] p-2 text-slate-700 font-medium relative">
                18
                <div class="mt-2 bg-orange-100 border border-orange-200 text-orange-800 rounded p-1.5 text-xs">
                    <div class="font-bold">1 tagihan</div>
                    <div class="text-[10px]">Rp 14.000.000</div>
                </div>
            </div>
            
            <div class="min-h-[100px] p-2 text-slate-700 font-medium">19</div>
            <div class="min-h-[100px] p-2 text-slate-700 font-medium">20</div>
            <div class="min-h-[100px] p-2 text-slate-700 font-medium bg-slate-50">21</div>

            <!-- Row 4 -->
            <div class="min-h-[100px] p-2 bg-slate-50 text-slate-400 font-medium">22</div>
            <div class="min-h-[100px] p-2 text-slate-700 font-medium">23</div>
            <div class="min-h-[100px] p-2 text-slate-700 font-medium">24</div>
            <div class="min-h-[100px] p-2 text-slate-700 font-medium">25</div>
            <div class="min-h-[100px] p-2 text-slate-700 font-medium">26</div>
            <div class="min-h-[100px] p-2 text-slate-700 font-medium">27</div>
            <div class="min-h-[100px] p-2 text-slate-700 font-medium bg-slate-50">28</div>

            <!-- Row 5 -->
            <div class="min-h-[100px] p-2 bg-slate-50 text-slate-400 font-medium">29</div>
            <div class="min-h-[100px] p-2 text-slate-700 font-medium">30</div>
            <div class="min-h-[100px] p-2 text-slate-700 font-medium">31</div>
            <div class="min-h-[100px] p-2 text-slate-400 font-medium bg-slate-50">1</div>
            <div class="min-h-[100px] p-2 text-slate-400 font-medium bg-slate-50">2</div>
            <div class="min-h-[100px] p-2 text-slate-400 font-medium bg-slate-50">3</div>
            <div class="min-h-[100px] p-2 text-slate-400 font-medium bg-slate-50">4</div>
        </div>
    </div>
</div>
@endsection
