<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Toko Bangunan Rajawali')</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Alpine.js for interactive UI (dropdowns, mobile menu) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-slate-50 text-slate-900 font-sans antialiased" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden relative">

        <!-- Overlay for mobile -->
        <div x-show="sidebarOpen" style="display: none;" class="fixed inset-0 bg-black/50 z-40 lg:hidden"
            @click="sidebarOpen = false"></div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 w-64 bg-[#0f172a] text-white transform transition-transform duration-300 lg:relative lg:translate-x-0 flex flex-col h-full shadow-xl">

            <!-- Logo -->
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-600 rounded flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold tracking-wide">Toko Rajawali</h2>
                        <p class="text-[11px] text-slate-400 font-medium">IMS</p>
                    </div>
                </div>
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            @auth
                <!-- User Info -->
                <div class="px-6 py-5 bg-slate-800/20 border-b border-slate-800">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-11 h-11 bg-[#2563eb] rounded-full flex items-center justify-center flex-shrink-0 font-bold text-lg border-2 border-slate-700">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-sm truncate">{{ auth()->user()->name }}</p>
                            <span
                                class="inline-flex items-center px-2 py-0.5 mt-1 rounded text-[10px] font-bold bg-yellow-500/20 text-yellow-500 uppercase tracking-wider">
                                {{ auth()->user()->role }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="flex-1 overflow-y-auto px-3 py-4 custom-scrollbar">
                    <nav class="space-y-1">
                        <!-- MAIN -->
                        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-3 mt-4 mb-3">Main
                        </div>

                        @if(in_array(auth()->user()->role, ['owner', 'admin']))
                            <a href="{{ url('/dashboard') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->is('dashboard') ? 'bg-[#2563eb] text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                                    </path>
                                </svg>
                                <span class="flex-1 text-sm font-medium">Dashboard</span>
                            </a>
                        @endif

                        <a href="{{ url('/pos') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->is('pos') ? 'bg-[#2563eb] text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                            <span class="flex-1 text-sm font-medium">POS / Cashier</span>
                        </a>

                        @if(in_array(auth()->user()->role, ['owner', 'admin']))
                            <a href="{{ route('product.index') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->is('products*') ? 'bg-[#2563eb] text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                <span class="flex-1 text-sm font-medium">Products</span>
                            </a>

                            <a href="{{ route('category.index') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->is('categories*') ? 'bg-[#2563eb] text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                    </path>
                                </svg>
                                <span class="flex-1 text-sm font-medium">Kategori & Sub</span>
                            </a>

                            <a href="{{ route('unit.index') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->is('units*') ? 'bg-[#2563eb] text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                    </path>
                                </svg>
                                <span class="flex-1 text-sm font-medium">Master Satuan</span>
                            </a>

                            <a href="{{ url('/tagihan-supplier') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->is('tagihan-supplier') ? 'bg-[#2563eb] text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                                    </path>
                                </svg>
                                <span class="flex-1 text-sm font-medium">Tagihan Supplier</span>
                            </a>

                            <a href="{{ url('/pembelian') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->is('pembelian*') ? 'bg-[#2563eb] text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                                <span class="flex-1 text-sm font-medium">Transaksi Pembelian</span>
                            </a>
                        @endif

                        <!-- MANAGEMENT -->
                        @if(in_array(auth()->user()->role, ['owner', 'admin']))
                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-3 mt-6 mb-3">
                                Management</div>

                            <a href="{{ url('/stock-management') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->is('stock-management') ? 'bg-[#2563eb] text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2">
                                    </path>
                                </svg>
                                <span class="flex-1 text-sm font-medium">Stock Management</span>
                            </a>

                            <a href="{{ url('/stock-opname') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->is('stock-opname') ? 'bg-[#2563eb] text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <span class="flex-1 text-sm font-medium">Stock Opname</span>
                            </a>

                            <a href="{{ route('supplier.index') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->is('supplier') ? 'bg-[#2563eb] text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path>
                                </svg>
                                <span class="flex-1 text-sm font-medium">Suppliers</span>
                            </a>

                            @if(auth()->user()->role === 'owner')
                                <a href="{{ route('employee.index') }}"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->is('employees*') ? 'bg-[#2563eb] text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                        </path>
                                    </svg>
                                    <span class="flex-1 text-sm font-medium">Karyawan</span>
                                </a>
                            @endif

                            <a href="{{ route('customer.index') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->is('customers*') ? 'bg-[#2563eb] text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                                <span class="flex-1 text-sm font-medium">Pelanggan</span>
                            </a>

                            <a href="{{ route('operationalitem.index') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->is('operational-items*') ? 'bg-[#2563eb] text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                    </path>
                                </svg>
                                <span class="flex-1 text-sm font-medium">Barang Operasional</span>
                            </a>

                            <a href="{{ route('notification.index') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->is('notifications*') ? 'bg-[#2563eb] text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                                    </path>
                                </svg>
                                <span class="flex-1 text-sm font-medium">Notifications</span>
                                <span class="bg-[#2563eb] text-white text-[10px] font-bold px-2 py-0.5 rounded-full">2</span>
                            </a>
                        @endif

                        <!-- REPORTS & ADVANCED -->
                        @if(auth()->user()->role === 'owner')
                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-3 mt-6 mb-3">Reports &
                                Advanced</div>

                            <a href="{{ route('report.index') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->is('reports*') ? 'bg-[#2563eb] text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <span class="flex-1 text-sm font-medium">Reports</span>
                            </a>

                        @endif

                    </nav>
                </div>

                <!-- Logout -->
                <div class="px-4 py-4 border-t border-slate-800">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center justify-center gap-2 px-3 py-2.5 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors font-medium text-sm">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                </path>
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
            @endauth
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 bg-slate-50">
            <!-- Top Bar -->
            <header
                class="bg-white border-b border-slate-200 px-6 h-[72px] flex items-center flex-shrink-0 justify-between z-10 shadow-sm">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true"
                        class="lg:hidden p-2 -ml-2 text-slate-500 hover:bg-slate-100 rounded-lg transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-xl font-bold text-slate-800 tracking-tight">
                            @yield('header_title', 'Dashboard')
                        </h1>
                        <p class="text-[13px] text-slate-500 font-medium">
                            {{ now()->translatedFormat('l, d F Y') }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <a href="{{ route('notification.index') }}"
                        class="relative p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                            </path>
                        </svg>
                        <span
                            class="absolute top-1.5 right-2 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></span>
                    </a>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-auto bg-slate-50 custom-scrollbar">
                <div class="p-6 md:p-8 max-w-7xl mx-auto">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .custom-scrollbar:hover::-webkit-scrollbar-thumb {
            background: #94a3b8;
        }

        @media print {
            body, html, #app, main, .flex-1, .h-screen, .overflow-hidden, .overflow-y-auto, .overflow-auto {
                height: auto !important;
                min-height: auto !important;
                overflow: visible !important;
                position: relative !important;
                display: block !important;
            }
            aside, header.bg-white {
                display: none !important;
            }
            main {
                padding: 0 !important;
                margin: 0 !important;
            }
        }
    </style>
    @stack('scripts')
    <script>
        // Global handler to prevent double form submissions
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form.tagName === 'FORM') {
                // If already submitting, prevent duplicate
                if (form.dataset.submitting) {
                    e.preventDefault();
                    return;
                }
                
                // Mark as submitting
                form.dataset.submitting = 'true';
                
                // Find the submit button and disable it visually and functionally
                const submitBtn = e.submitter || form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    setTimeout(() => {
                        submitBtn.disabled = true;
                        submitBtn.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                    }, 10);
                }
                
                // Safety fallback: re-enable after 8 seconds in case of silent failure or file download
                setTimeout(() => {
                    delete form.dataset.submitting;
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                    }
                }, 8000);
            }
        });
    </script>
</body>

</html>