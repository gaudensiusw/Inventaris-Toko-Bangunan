@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Manajemen Akun</h1>
            <p class="text-sm text-gray-500">Kelola akun karyawan dan hak akses</p>
        </div>
        <div class="flex gap-3">
            <button onclick="openAddModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors shadow-sm flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Akun
            </button>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Total Akun</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ $stats['total'] }}</h3>
                </div>
                <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Akun Aktif</p>
                    <h3 class="text-3xl font-bold text-green-600">{{ $stats['active'] }}</h3>
                </div>
                <div class="p-3 bg-green-50 text-green-600 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Total Supervisor</p>
                    <h3 class="text-3xl font-bold text-purple-600">{{ $stats['supervisor'] }}</h3>
                </div>
                <div class="p-3 bg-purple-50 text-purple-600 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Total Operator</p>
                    <h3 class="text-3xl font-bold text-orange-600">{{ $stats['operator'] }}</h3>
                </div>
                <div class="p-3 bg-orange-50 text-orange-600 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Controls -->
        <div class="p-4 border-b border-gray-200 bg-gray-50 flex flex-col sm:flex-row justify-between items-center gap-4">
            <form action="{{ route('accounts.index') }}" method="GET" class="flex flex-col sm:flex-row w-full gap-4">
                <div class="relative flex-grow max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div class="flex gap-2">
                    <select name="role" class="border border-gray-300 text-gray-700 py-2 px-4 rounded-lg focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">Semua Role</option>
                        @if(auth()->user()->role === 'owner')
                            <option value="owner" {{ request('role') == 'owner' ? 'selected' : '' }}>Owner</option>
                        @endif
                        <option value="supervisor" {{ request('role') == 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                        <option value="operator" {{ request('role') == 'operator' ? 'selected' : '' }}>Operator</option>
                        <option value="gudang" {{ request('role') == 'gudang' ? 'selected' : '' }}>Gudang</option>
                    </select>
                    <button type="submit" class="bg-white border border-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
                        Filter
                    </button>
                    @if(request('search') || request('role'))
                        <a href="{{ route('accounts.index') }}" class="text-gray-500 hover:text-red-500 py-2 px-3 flex items-center transition-colors">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Info Pengguna</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Login</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $user)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-lg">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $badgeColor = match($user->role) {
                                    'owner' => 'bg-red-100 text-red-800',
                                    'supervisor' => 'bg-purple-100 text-purple-800',
                                    'operator' => 'bg-orange-100 text-orange-800',
                                    'gudang' => 'bg-gray-100 text-gray-800',
                                    default => 'bg-blue-100 text-blue-800',
                                };
                            @endphp
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badgeColor }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($user->last_login_at)
                                {{ $user->last_login_at->format('d M Y, H:i') }}
                            @else
                                <span class="text-gray-400 italic">Belum pernah login</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @php
                                $isSupervisor = auth()->user()->role === 'supervisor';
                                $isOwnerRow = $user->role === 'owner';
                                $disableAction = ($user->id === auth()->id()) || ($isSupervisor && $isOwnerRow);
                            @endphp
                            <!-- Toggle Switch -->
                            <label class="relative inline-flex items-center {{ $disableAction ? 'cursor-not-allowed' : 'cursor-pointer' }}">
                                <input type="checkbox" class="sr-only peer" {{ $user->aktif ? 'checked' : '' }} onchange="toggleStatus({{ $user->id }}, this)" {{ $disableAction ? 'disabled' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500 {{ $disableAction ? 'opacity-50 cursor-not-allowed' : '' }}"></div>
                            </label>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end gap-2">
                                @if(!($isSupervisor && $isOwnerRow))
                                <button onclick="resetPassword({{ $user->id }}, '{{ $user->name }}')" class="text-blue-600 hover:text-blue-900 bg-blue-50 p-2 rounded-md hover:bg-blue-100 transition-colors" title="Reset Password">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                    </svg>
                                </button>
                                <button onclick="openEditModal({{ $user }})" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 p-2 rounded-md hover:bg-indigo-100 transition-colors" title="Edit Akun">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <button onclick="deleteAccount({{ $user->id }}, '{{ $user->name }}')" class="text-red-600 hover:text-red-900 bg-red-50 p-2 rounded-md hover:bg-red-100 transition-colors {{ $user->id === auth()->id() ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $user->id === auth()->id() ? 'disabled' : '' }} title="Hapus Akun">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @if($users->isEmpty())
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                            Tidak ada data akun ditemukan.
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Account Modal -->
<div id="accountModal" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center backdrop-blur-sm transition-opacity duration-300 opacity-0">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 transform scale-95 transition-transform duration-300">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 id="modalTitle" class="text-lg font-bold text-gray-800">Tambah Akun</h3>
            <button onclick="closeModal('accountModal')" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form id="accountForm" onsubmit="submitForm(event)">
            @csrf
            <input type="hidden" id="userId" name="id">
            <div class="px-6 py-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" id="name" name="name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="email" name="email" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select id="role" name="role" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        @if(auth()->user()->role === 'owner')
                            <option value="owner">Owner</option>
                        @endif
                        <option value="supervisor">Supervisor</option>
                        <option value="operator">Operator</option>
                        <option value="gudang">Gudang</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" minlength="6" class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-10 focus:ring-blue-500 focus:border-blue-500">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600" title="Tampilkan/Sembunyikan Password">
                            <!-- Eye icon (password hidden) -->
                            <svg id="eyeIconOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <!-- Eye slash icon (password visible) hidden by default -->
                            <svg id="eyeIconClosed" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.978 9.978 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    <p id="passwordHelp" class="mt-1 text-xs text-gray-500 hidden">Kosongkan jika tidak ingin mengubah password.</p>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3 rounded-b-xl">
                <button type="button" onclick="closeModal('accountModal')" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
                <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors shadow-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>


<!-- SweetAlert2 CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

<script>
    const roles = ['owner', 'supervisor', 'operator', 'gudang'];

    // Setup CSRF Token for Fetch API
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value;

    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        const modalInner = modal.firstElementChild;
        modal.classList.remove('hidden');
        // Trigger reflow
        void modal.offsetWidth;
        modal.classList.remove('opacity-0');
        modalInner.classList.remove('scale-95');
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        const modalInner = modal.firstElementChild;
        modal.classList.add('opacity-0');
        modalInner.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    function openAddModal() {
        document.getElementById('modalTitle').innerText = 'Tambah Akun';
        document.getElementById('accountForm').reset();
        document.getElementById('userId').value = '';
        document.getElementById('password').required = true;
        document.getElementById('passwordHelp').classList.add('hidden');
        resetPasswordVisibility();
        openModal('accountModal');
    }

    function openEditModal(user) {
        document.getElementById('modalTitle').innerText = 'Edit Akun';
        document.getElementById('userId').value = user.id;
        document.getElementById('name').value = user.name;
        document.getElementById('email').value = user.email;
        document.getElementById('role').value = user.role;
        document.getElementById('password').required = false;
        document.getElementById('password').value = '';
        document.getElementById('passwordHelp').classList.remove('hidden');
        resetPasswordVisibility();
        openModal('accountModal');
    }

    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const eyeOpen = document.getElementById('eyeIconOpen');
        const eyeClosed = document.getElementById('eyeIconClosed');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeOpen.classList.add('hidden');
            eyeClosed.classList.remove('hidden');
        } else {
            passwordInput.type = 'password';
            eyeOpen.classList.remove('hidden');
            eyeClosed.classList.add('hidden');
        }
    }

    function resetPasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const eyeOpen = document.getElementById('eyeIconOpen');
        const eyeClosed = document.getElementById('eyeIconClosed');
        if (passwordInput) {
            passwordInput.type = 'password';
            if(eyeOpen) eyeOpen.classList.remove('hidden');
            if(eyeClosed) eyeClosed.classList.add('hidden');
        }
    }

    async function submitForm(e) {
        e.preventDefault();
        const form = document.getElementById('accountForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        const id = data.id;

        const url = id ? `/accounts/${id}` : '/accounts';
        // Laravel requires _method=PUT for forms
        if (id) { data._method = 'PUT'; }

        try {
            const response = await fetch(url, {
                method: 'POST', // always POST when sending formData in JS, use _method=PUT for laravel spoofing
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                closeModal('accountModal');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: result.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                let errorMsg = result.message || 'Terjadi kesalahan.';
                if (result.errors) {
                    errorMsg = Object.values(result.errors).flat().join('\n');
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: errorMsg,
                });
            }
        } catch (error) {
            Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
        }
    }

    async function toggleStatus(id, checkbox) {
        try {
            const response = await fetch(`/accounts/${id}/toggle-status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (!response.ok) {
                // Revert checkbox if failed
                checkbox.checked = !checkbox.checked;
                Swal.fire('Gagal', result.message || 'Gagal mengubah status', 'error');
            } else {
                Swal.fire({
                    icon: 'success',
                    title: 'Status Diperbarui',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        } catch (error) {
            checkbox.checked = !checkbox.checked;
            Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
        }
    }

    function deleteAccount(id, name) {
        Swal.fire({
            title: 'Hapus Akun?',
            text: `Anda yakin ingin menghapus akun ${name}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/accounts/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });
                    
                    const resData = await response.json();

                    if (response.ok) {
                        Swal.fire('Terhapus!', resData.message, 'success').then(() => window.location.reload());
                    } else {
                        Swal.fire('Gagal!', resData.message || 'Gagal menghapus akun', 'error');
                    }
                } catch (error) {
                    Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
                }
            }
        });
    }

    function resetPassword(id, name) {
        Swal.fire({
            title: 'Reset Password?',
            text: `Password untuk ${name} akan direset secara otomatis.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Reset!',
            cancelButtonText: 'Batal'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/accounts/${id}/reset-password`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });
                    
                    const resData = await response.json();

                    if (response.ok) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Password Direset!',
                            html: `Password baru untuk <b>${name}</b> adalah:<br><br><div class="bg-gray-100 p-3 rounded text-xl font-mono tracking-wider border border-gray-300 select-all">${resData.password}</div><br><small class="text-red-500">Silakan salin password ini sekarang, Anda tidak dapat melihatnya lagi!</small>`,
                            confirmButtonText: 'Tutup'
                        });
                    } else {
                        Swal.fire('Gagal!', resData.message || 'Gagal reset password', 'error');
                    }
                } catch (error) {
                    Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
                }
            }
        });
    }


</script>
@endsection
