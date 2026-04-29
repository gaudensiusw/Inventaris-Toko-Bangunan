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

<body class="bg-[#0f172a] min-h-screen flex flex-col items-center justify-center p-6">

    <!-- Header Section -->
    <div class="mb-8 text-center">
        <div
            class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-2xl mb-4 shadow-lg shadow-blue-900/20">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                </path>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-white tracking-tight">Toko Bangunan Rajawali</h1>
        <p class="text-slate-400 mt-1">Inventory Management System</p>
    </div>

    <!-- Login Card -->
    <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl p-8 mb-6">
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-slate-800">Sign In</h2>
            <p class="text-slate-500 mt-1">Enter your credentials to access the system</p>
        </div>

        @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 text-red-600 text-sm rounded-xl border border-red-100">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" id="loginForm">
            @csrf
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207">
                                </path>
                            </svg>
                        </span>
                        <input type="email" name="email" id="emailField" required
                            class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                            placeholder="user@tokobangunan.com">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                </path>
                            </svg>
                        </span>
                        <input type="password" name="password" id="passwordField" required
                            class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                            placeholder="••••••••">
                    </div>
                </div>

                <button type="submit"
                    class="w-full py-4 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-2xl transition-colors shadow-lg shadow-slate-900/20 active:scale-[0.98]">
                    Sign In
                </button>
            </div>
        </form>

        <!-- Quick Login Section -->
        <div class="mt-8">
            <div class="relative flex items-center mb-6">
                <div class="flex-grow border-t border-slate-100"></div>
                <span class="flex-shrink mx-4 text-xs font-bold text-slate-300 uppercase tracking-widest">Quick Login
                    (Demo)</span>
                <div class="flex-grow border-t border-slate-100"></div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <button onclick="quickLogin('operator@tokobangunan.com')"
                    class="py-2.5 px-2 bg-white border border-slate-100 hover:border-blue-500 hover:text-blue-600 rounded-xl text-xs font-bold text-slate-600 transition-all shadow-sm">
                    Operator
                </button>
                <button onclick="quickLogin('supervisor@tokobangunan.com')"
                    class="py-2.5 px-2 bg-white border border-slate-100 hover:border-blue-500 hover:text-blue-600 rounded-xl text-xs font-bold text-slate-600 transition-all shadow-sm">
                    Supervisor
                </button>
                <button onclick="quickLogin('owner@tokobangunan.com')"
                    class="py-2.5 px-2 bg-white border border-slate-100 hover:border-blue-500 hover:text-blue-600 rounded-xl text-xs font-bold text-slate-600 transition-all shadow-sm">
                    Owner
                </button>
            </div>
        </div>
    </div>

    <!-- Info Box -->
    <div class="w-full max-w-md bg-blue-50/50 border border-blue-100 rounded-3xl p-6">
        <h3 class="text-sm font-bold text-blue-900 mb-3">Demo Accounts:</h3>
        <ul class="space-y-2 text-xs font-medium text-blue-800/80">
            <li class="flex items-center gap-2">
                <span class="w-1.5 h-1.5 bg-blue-400 rounded-full"></span>
                <strong class="text-blue-900">Operator:</strong> operator@tokobangunan.com
            </li>
            <li class="flex items-center gap-2">
                <span class="w-1.5 h-1.5 bg-blue-400 rounded-full"></span>
                <strong class="text-blue-900">Supervisor:</strong> supervisor@tokobangunan.com
            </li>
            <li class="flex items-center gap-2">
                <span class="w-1.5 h-1.5 bg-blue-400 rounded-full"></span>
                <strong class="text-blue-900">Owner:</strong> owner@tokobangunan.com
            </li>
        </ul>
        <p class="mt-4 text-[10px] font-bold text-blue-400 uppercase tracking-wider">Password: demo123 (any password
            works)</p>
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