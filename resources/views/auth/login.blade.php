<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Toko Bangunan IMS</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-4 font-sans antialiased text-slate-900">
    <div class="w-full max-w-md">
        <!-- Logo and Title -->
        <div class="text-center mb-8">
            <div class="flex justify-center mb-4">
                <div class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Toko Bangunan IMS</h1>
            <p class="text-slate-400">Inventory Management System</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-xl shadow-lg border border-slate-200">
            <div class="p-6 pb-2">
                <h3 class="text-xl font-semibold leading-none tracking-tight">Sign In</h3>
                <p class="text-sm text-slate-500 mt-1.5">Enter your credentials to access the system</p>
            </div>
            <div class="p-6 pt-4">
                <!-- Ensure this points to the correct route in the future -->
                <form method="POST" action="/login" class="space-y-4">
                    @csrf
                    
                    <div class="space-y-2">
                        <label for="email" class="text-sm rounded font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Email</label>
                        <div class="relative">
                            <svg class="absolute left-3 top-3 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="user@tokobangunan.com" class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm ring-offset-white file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-50 pl-10" required autofocus>
                        </div>
                        @error('email')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="password" class="text-sm rounded font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Password</label>
                        <div class="relative">
                            <svg class="absolute left-3 top-3 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            <input id="password" type="password" name="password" placeholder="••••••••" class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm ring-offset-white file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-50 pl-10" required>
                        </div>
                        @error('password')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center space-x-2 py-1">
                        <input id="remember" type="checkbox" name="remember" class="h-4 w-4 shrink-0 rounded border-slate-200 text-blue-600 focus:ring-blue-500">
                        <label for="remember" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-slate-600">
                            Remember Me
                        </label>
                    </div>

                    <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 disabled:pointer-events-none disabled:opacity-50 bg-blue-600 text-white hover:bg-blue-700 h-10 px-4 py-2 w-full">
                        Sign In
                    </button>
                </form>

                <!-- Quick Login Options -->
                <div class="mt-8">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <span class="w-full border-t border-slate-200"></span>
                        </div>
                        <div class="relative flex justify-center text-xs uppercase">
                            <span class="bg-white px-2 text-slate-500">Quick Login (Demo)</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 mt-4">
                        <button type="button" onclick="fillDemo('operator@tokobangunan.com')" class="inline-flex items-center justify-center rounded-md text-xs font-medium border border-slate-200 bg-white hover:bg-slate-100 hover:text-slate-900 h-8 px-3 transition-colors">
                            Operator
                        </button>
                        <button type="button" onclick="fillDemo('supervisor@tokobangunan.com')" class="inline-flex items-center justify-center rounded-md text-xs font-medium border border-slate-200 bg-white hover:bg-slate-100 hover:text-slate-900 h-8 px-3 transition-colors">
                            Supervisor
                        </button>
                        <button type="button" onclick="fillDemo('owner@tokobangunan.com')" class="inline-flex items-center justify-center rounded-md text-xs font-medium border border-slate-200 bg-white hover:bg-slate-100 hover:text-slate-900 h-8 px-3 transition-colors">
                            Owner
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Demo Info Card -->
        <div class="mt-4 bg-blue-50 border border-blue-200 rounded-xl shadow-sm p-6 text-sm">
            <p class="text-blue-900"><strong>Demo Accounts:</strong></p>
            <ul class="text-blue-800 mt-2 space-y-1 text-xs">
                <li>• <strong>Operator:</strong> operator@tokobangunan.com</li>
                <li>• <strong>Supervisor:</strong> supervisor@tokobangunan.com</li>
                <li>• <strong>Owner:</strong> owner@tokobangunan.com</li>
            </ul>
            <p class="mt-3 text-xs text-blue-800">Password: <strong>demo123</strong> (any password works for demo)</p>
        </div>
    </div>

    <script>
        function fillDemo(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'demo123';
        }
    </script>
</body>
</html>
