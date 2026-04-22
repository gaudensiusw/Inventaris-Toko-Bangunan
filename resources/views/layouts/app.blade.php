<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Toko Bangunan IMS')</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Optional: Alpine.js for interactive UI (dropdowns, mobile menu) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">
        
        <!-- Overlay for mobile -->
        <div x-show="sidebarOpen" 
             style="display: none;" 
             class="fixed inset-0 bg-black/50 z-40 lg:hidden"
             @click="sidebarOpen = false"></div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
               class="fixed inset-y-0 left-0 z-50 w-64 bg-[#0f172a] text-white transform transition-transform duration-300 lg:relative lg:translate-x-0 flex flex-col h-full">
            
            <!-- Logo -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-700/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <div>
                        <h2 class="font-bold">Toko Bangunan</h2>
                        <p class="text-xs text-slate-400">IMS v1.0</p>
                    </div>
                </div>
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- User Info (Stubbed) -->
            <div class="px-6 py-4 bg-slate-800/30 border-b border-slate-700/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0 font-bold">
                        {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium truncate">{{ auth()->user()->name ?? 'Admin User' }}</p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-500/20 text-yellow-300">
                            {{ auth()->user()->role ?? 'owner' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <div class="flex-1 overflow-y-auto px-3 py-4">
                <nav class="space-y-1">
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider px-3 mt-4 mb-2">Main</div>
                    
                    <a href="{{ url('/dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->is('dashboard') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700/50 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        <span class="flex-1">Dashboard</span>
                    </a>

                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider px-3 mt-6 mb-2">Inventory Module</div>
                    
                    <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-slate-300 hover:bg-slate-700/50 hover:text-white">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        <span class="flex-1">Stock Management</span>
                    </a>

                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider px-3 mt-6 mb-2">HR & Payroll Module</div>
                    
                    <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->is('karyawan*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700/50 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <span class="flex-1">Karyawan</span>
                    </a>
                    
                    <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->is('absensi*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700/50 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        <span class="flex-1">Absensi</span>
                    </a>

                    <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->is('gaji*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700/50 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="flex-1">Penggajian</span>
                    </a>
                </nav>
            </div>

            <!-- Logout -->
            <div class="px-3 py-4 border-t border-slate-700/50">
                <form method="POST" action="{{ route('logout') ?? '#' }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center px-3 py-2 text-slate-300 hover:text-white hover:bg-slate-700/50 rounded-lg transition-colors">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white border-b border-slate-200 px-6 py-4 flex-shrink-0">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <button @click="sidebarOpen = true" class="lg:hidden p-2 -ml-2 text-slate-600 hover:bg-slate-100 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                        <div>
                            <h1 class="text-xl font-bold text-slate-900">
                                @yield('header_title', 'Dashboard')
                            </h1>
                            <p class="text-sm text-slate-500">
                                {{ now()->translatedFormat('l, d F Y') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <button class="relative p-2 text-slate-400 hover:text-slate-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-auto bg-slate-50">
                <div class="p-6">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
</body>
</html>
