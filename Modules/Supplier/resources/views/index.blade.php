@extends('layouts.app')

@section('title', 'Toko Bangunan - Suppliers')
@section('header_title', 'Suppliers')

@section('content')
<div x-data="{ 
    addModalOpen: false, 
    editModalOpen: false, 
    deleteModalOpen: false,
    editForm: {},
    deleteForm: {},
    openEditModal(supplier) {
        this.editForm = supplier;
        this.editModalOpen = true;
    },
    openDeleteModal(supplier) {
        this.deleteForm = supplier;
        this.deleteModalOpen = true;
    }
}">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-slate-100 text-slate-600 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-0.5">Total Suppliers</p>
                <h3 class="text-2xl font-bold text-slate-800">{{ $totalSuppliers }}</h3>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-50 text-[#2563eb] rounded-full flex items-center justify-center flex-shrink-0 relative">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full border-2 border-white">{{ $activeProducts }}</span>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-0.5">Active Products</p>
                <h3 class="text-2xl font-bold text-slate-800">{{ $activeProducts }}</h3>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-green-50 text-green-600 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-0.5">Avg Products/Supplier</p>
                <h3 class="text-2xl font-bold text-slate-800">{{ number_format($avgProducts, 1) }}</h3>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span class="text-sm font-medium">{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center gap-2">
        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <span class="text-sm font-medium">{{ session('error') }}</span>
    </div>
    @endif
    @if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-start gap-2">
        <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <div>
            <ul class="list-disc list-inside text-sm font-medium">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <!-- Supplier Table Section -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm mb-6">
        <div class="p-5 border-b border-slate-200 flex flex-wrap gap-4 items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Suppliers ({{ $totalSuppliers }})</h3>
                <p class="text-sm text-slate-500 mt-0.5">Manage your product suppliers and vendors</p>
            </div>
            <button @click="addModalOpen = true" class="bg-[#0f172a] hover:bg-slate-800 text-white px-4 py-2.5 rounded-lg text-sm font-bold flex items-center gap-2 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Supplier
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs">Company Name</th>
                        <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs">Contact Person</th>
                        <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs">Contact Info</th>
                        <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs">Address</th>
                        <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs">Products</th>
                        <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($suppliers as $index => $supplier)
                    <tr class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-slate-50/50' }} hover:bg-blue-50/30 transition-colors">
                        <td class="py-4 px-5">
                            <div class="font-bold text-slate-800">{{ $supplier->company_name }}</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">Since {{ $supplier->created_at->format('M Y') }}</div>
                        </td>
                        <td class="py-4 px-5 font-medium text-slate-700">
                            {{ $supplier->contact_person }}
                        </td>
                        <td class="py-4 px-5">
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center gap-1.5 text-slate-600 text-[13px]">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                    {{ $supplier->phone ?? '-' }}
                                </div>
                                <div class="flex items-center gap-1.5 text-slate-600 text-[13px]">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    {{ $supplier->email ?? '-' }}
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-5">
                            <div class="flex items-start gap-1.5 text-slate-600 text-[13px] max-w-[200px] whitespace-normal">
                                <svg class="w-4 h-4 text-slate-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <div>
                                    <span class="block truncate max-w-[180px]">{{ $supplier->address ?? '-' }}</span>
                                    @if($supplier->city || $supplier->province)
                                        <span class="text-xs text-slate-500">{{ $supplier->city }}{{ $supplier->city && $supplier->province ? ', ' : '' }}{{ $supplier->province }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-5">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200 shadow-sm">
                                {{ $supplier->products_count }} products
                            </span>
                        </td>
                        <td class="py-4 px-5 text-right space-x-2">
                            <button @click="openEditModal({{ $supplier->toJson() }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors inline-block" title="Edit">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>
                            <button @click="openDeleteModal({{ $supplier->toJson() }})" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition-colors inline-block" title="Delete">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-500">
                            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <p class="text-sm font-medium text-slate-600">No suppliers found.</p>
                            <p class="text-xs mt-1">Click "Add Supplier" to create one.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-slate-100">
            {{ $suppliers->links() }}
        </div>
    </div>

    <!-- Products by Supplier Distribution -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm mb-6 p-5">
        <h3 class="text-base font-bold text-slate-800">Products by Supplier</h3>
        <p class="text-xs text-slate-500 mt-1 mb-5">Distribution of products across suppliers</p>
        
        <div class="space-y-4">
            @foreach($suppliers as $supplier)
            @php
                $percent = $activeProducts > 0 ? round(($supplier->products_count / $activeProducts) * 100) : 0;
            @endphp
            <div>
                <div class="flex items-center justify-between text-sm mb-1">
                    <span class="font-semibold text-slate-700">{{ $supplier->company_name }}</span>
                    <span class="text-slate-500 font-medium text-[11px]">{{ $supplier->products_count }} products ({{ $percent }}%)</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                    <div class="bg-[#2563eb] h-2.5 rounded-full" style="width: {{ $percent }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- ADD MODAL -->
    <div x-show="addModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center" style="display: none;">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="addModalOpen = false" x-transition.opacity></div>
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl relative z-10 m-4 overflow-hidden transform transition-all" x-transition>
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Add New Supplier</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Create a new supplier</p>
                </div>
                <button @click="addModalOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-lg hover:bg-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form action="{{ route('supplier.store') }}" method="POST">
                @csrf
                <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Company Name <span class="text-red-500">*</span></label>
                        <input type="text" name="company_name" required placeholder="e.g., PT Supplier Indonesia" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Contact Person <span class="text-red-500">*</span></label>
                        <input type="text" name="contact_person" required placeholder="e.g., John Doe" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Phone</label>
                            <input type="text" name="phone" placeholder="+62-21-1234567" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Email</label>
                            <input type="email" name="email" placeholder="contact@supplier.com" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">City</label>
                            <input type="text" name="city" placeholder="e.g., Jakarta" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Province</label>
                            <input type="text" name="province" placeholder="e.g., DKI Jakarta" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Address</label>
                        <textarea name="address" placeholder="Enter full address" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 resize-none h-20 bg-white"></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/50">
                    <button type="button" @click="addModalOpen = false" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors shadow-sm">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-[#0f172a] hover:bg-slate-800 rounded-lg text-sm font-bold text-white shadow transition-colors">Add Supplier</button>
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
                    <h3 class="text-lg font-bold text-slate-800">Edit Supplier</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Update supplier information</p>
                </div>
                <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-lg hover:bg-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form :action="`/supplier/${editForm.id}`" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Company Name <span class="text-red-500">*</span></label>
                        <input type="text" name="company_name" x-model="editForm.company_name" required class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Contact Person <span class="text-red-500">*</span></label>
                        <input type="text" name="contact_person" x-model="editForm.contact_person" required class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Phone</label>
                            <input type="text" name="phone" x-model="editForm.phone" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Email</label>
                            <input type="email" name="email" x-model="editForm.email" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">City</label>
                            <input type="text" name="city" x-model="editForm.city" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Province</label>
                            <input type="text" name="province" x-model="editForm.province" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Address</label>
                        <textarea name="address" x-model="editForm.address" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 resize-none h-20 bg-white"></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/50">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors shadow-sm">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-[#0f172a] hover:bg-slate-800 rounded-lg text-sm font-bold text-white shadow transition-colors">Update Supplier</button>
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
                <h3 class="text-lg font-bold text-slate-800 mb-2">Delete Supplier?</h3>
                <p class="text-sm text-slate-500">Are you sure you want to delete <span class="font-bold text-slate-700" x-text="deleteForm.company_name"></span>? This action cannot be undone.</p>
            </div>
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex gap-3">
                <button type="button" @click="deleteModalOpen = false" class="flex-1 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors shadow-sm">Cancel</button>
                <form :action="`/supplier/${deleteForm.id}`" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-2 bg-red-600 hover:bg-red-700 rounded-lg text-sm font-bold text-white shadow transition-colors">Yes, Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
