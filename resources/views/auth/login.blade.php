<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Toko Bangunan IMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>

<body class="bg-white min-h-screen">
    <div class="flex min-h-screen">
        <!-- Left Side: Image & Branding -->
        <div class="hidden lg:flex lg:w-1/2 relative bg-slate-900 items-end p-12 overflow-hidden">
            <!-- Background Image -->
            <img src="{{ asset('hardware_store_interior_1778523115324.png') }}" 
                 class="absolute inset-0 w-full h-full object-cover opacity-60 scale-110" alt="Background">
            
            <!-- Overlay Gradient -->
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/20 to-transparent"></div>

            <div class="relative z-10 w-full max-w-xl">
                <!-- Logo -->
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/20">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white tracking-tight">Aplikasi Toko Bangunan Rajawali</h1>
                        <p class="text-slate-400 text-xs font-medium">Inventory Management System</p>
                    </div>
                </div>

                <h2 class="text-4xl font-bold text-white leading-tight mb-4">
                    Kelola toko dengan mudah,<br> akurat, dan transparan.
                </h2>
                <p class="text-slate-300 text-lg font-medium leading-relaxed">
                    Manajemen stok, kasir, laporan keuangan, dan prediksi penjualan dalam satu platform.
                </p>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12 md:p-20">
            <div class="w-full max-w-md">
                <div class="mb-10 text-center lg:text-left">
                    <h2 class="text-3xl font-extrabold text-slate-900 mb-2 tracking-tight">Selamat Datang,</h2>
                    <p class="text-slate-500 font-medium">Silakan masuk untuk melanjutkan ke dashboard</p>
                </div>

                @if($errors->any())
                    <div class="mb-6 p-4 bg-red-50 text-red-600 text-sm rounded-2xl border border-red-100 flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1-2 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                        <span class="font-bold">{{ $errors->first() }}</span>
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" id="loginForm" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2 ml-1">Email</label>
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 group-focus-within:text-blue-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207">
                                    </path>
                                </svg>
                            </span>
                            <input type="email" name="email" id="emailField" required
                                class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent focus:bg-white transition-all font-medium"
                                placeholder="user@tokobangunan.com">
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2 ml-1">
                            <label class="block text-sm font-bold text-slate-700">Password</label>
                            <a href="#" class="text-xs font-bold text-blue-600 hover:text-blue-700">Lupa password?</a>
                        </div>
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 group-focus-within:text-blue-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                            </span>
                            <input type="password" name="password" id="passwordField" required
                                class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent focus:bg-white transition-all font-medium"
                                placeholder="••••••••">
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full py-4 bg-slate-950 hover:bg-black text-white font-black rounded-2xl transition-all shadow-xl shadow-slate-200 active:scale-[0.98] tracking-wider uppercase text-sm">
                        Login
                    </button>
                </form>

                <!-- Quick Login Section -->
                <div class="mt-12">
                    <div class="relative flex items-center mb-8">
                        <div class="flex-grow border-t border-slate-100"></div>
                        <span class="flex-shrink mx-4 text-[10px] font-black text-slate-300 uppercase tracking-[0.2em]">Quick Login (Demo)</span>
                        <div class="flex-grow border-t border-slate-100"></div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <button onclick="quickLogin('operator@tokobangunan.com')"
                            class="py-3 px-2 bg-white border border-slate-200 hover:border-blue-600 hover:bg-blue-50 hover:text-blue-700 rounded-2xl text-xs font-bold text-slate-600 transition-all shadow-sm active:scale-95">
                            Operator
                        </button>
                        <button onclick="quickLogin('supervisor@tokobangunan.com')"
                            class="py-3 px-2 bg-white border border-slate-200 hover:border-blue-600 hover:bg-blue-50 hover:text-blue-700 rounded-2xl text-xs font-bold text-slate-600 transition-all shadow-sm active:scale-95">
                            Supervisor
                        </button>
                        <button onclick="quickLogin('owner@tokobangunan.com')"
                            class="py-3 px-2 bg-white border border-slate-200 hover:border-blue-600 hover:bg-blue-50 hover:text-blue-700 rounded-2xl text-xs font-bold text-slate-600 transition-all shadow-sm active:scale-95">
                            Owner
                        </button>
                    </div>
                    <p class="mt-6 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest">Password demo: demo123</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function quickLogin(email) {
            document.getElementById('emailField').value = email;
            document.getElementById('passwordField').value = 'demo123';
            // Optional: Auto submit
            // document.getElementById('loginForm').submit();
        }
    </script>
</body>

</html>