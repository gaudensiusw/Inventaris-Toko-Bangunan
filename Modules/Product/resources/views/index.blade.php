@extends('layouts.app')

@section('title', 'Toko Bangunan - Products')
@section('header_title', 'Products')

@section('content')
    @php
        $editFormData = old('edit_id') ? [
            'id' => old('edit_id'),
            'nama' => old('nama'),
            'sku' => old('sku'),
            'kategori_id' => old('kategori_id'),
            'supplier_id' => old('supplier_id'),
            'stok' => old('stok'),
            'unit' => old('unit'),
            'min_stok' => old('min_stok'),
            'harga_beli' => old('harga_beli'),
            'harga_jual' => old('harga_jual'),
            'merk' => old('merk')
        ] : new \stdClass();
    @endphp

    <div x-data="{ 
            addModalOpen: {{ $errors->any() && !old('edit_id') ? 'true' : 'false' }}, 
            editModalOpen: {{ $errors->any() && old('edit_id') ? 'true' : 'false' }}, 
            deleteModalOpen: false,
            editForm: @json($editFormData),
            addForm: {
                harga_beli: {{ old('harga_beli', 0) }},
                harga_jual: {{ old('harga_jual', 0) }},
                margin: 10
            },
            deleteForm: {},

            formatNumber(val) {
                if (val === undefined || val === null || val === '') return '';
                return val.toString().replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            },

            unformatNumber(val) {
                if (!val) return 0;
                return parseInt(val.toString().replace(/\./g, '')) || 0;
            },

            updatePrices(type, source) {
                let form = type === 'add' ? this.addForm : this.editForm;
                
                if (source === 'beli' || source === 'margin') {
                    // Update Harga Jual based on Margin
                    form.harga_jual = Math.round(form.harga_beli * (1 + form.margin / 100));
                } else if (source === 'jual') {
                    // Update Margin based on Harga Jual
                    if (form.harga_beli > 0) {
                        form.margin = parseFloat(((form.harga_jual / form.harga_beli - 1) * 100).toFixed(2));
                    }
                }
            },

            openEditModal(product) {
                this.editForm = JSON.parse(JSON.stringify(product));
                // Cast to integers to remove decimals
                this.editForm.harga_beli = Math.floor(this.editForm.harga_beli);
                this.editForm.harga_jual = Math.floor(this.editForm.harga_jual);
                this.editForm.stok = Math.floor(this.editForm.stok);
                this.editForm.min_stok = Math.floor(this.editForm.min_stok);
                
                // Calculate initial margin
                if (this.editForm.harga_beli > 0) {
                    this.editForm.margin = parseFloat(((this.editForm.harga_jual / this.editForm.harga_beli - 1) * 100).toFixed(2));
                } else {
                    this.editForm.margin = 0;
                }
                
                this.editModalOpen = true;
            },
            openDeleteModal(product) {
                this.deleteForm = product;
                this.deleteModalOpen = true;
            }
        }">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center gap-4">
                <div
                    class="w-12 h-12 bg-blue-50 text-[#2563eb] rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-0.5">Total Products</p>
                    <h3 class="text-2xl font-bold text-slate-800">{{ $totalProducts }}</h3>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center gap-4">
                <div
                    class="w-12 h-12 bg-orange-50 text-orange-600 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-0.5">Low Stock</p>
                    <h3 class="text-2xl font-bold text-slate-800">{{ $lowStockCount }}</h3>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 bg-red-50 text-red-600 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-0.5">Out of Stock</p>
                    <h3 class="text-2xl font-bold text-slate-800">{{ $outOfStockCount }}</h3>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <p class="text-sm font-bold mb-1">Terdapat kesalahan pada form:</p>
                <ul class="list-disc list-inside text-sm space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Product Table Section -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm mb-6 overflow-hidden">
            <div class="p-5 border-b border-slate-200 flex flex-wrap gap-4 items-center justify-between bg-slate-50/50">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Product Catalog</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Manage your inventory and pricing</p>
                </div>
                <button @click="addModalOpen = true"
                    class="bg-[#0f172a] hover:bg-slate-800 text-white px-4 py-2.5 rounded-lg text-sm font-bold flex items-center gap-2 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Product
                </button>
            </div>
            <div class="p-4 border-b border-slate-200 bg-white flex flex-wrap gap-3">
                <form action="{{ route('product.index') }}" method="GET" class="flex flex-wrap gap-3 flex-1">
                    <div class="relative min-w-[300px] flex-1">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or SKU..." class="pl-10 pr-4 py-2.5 border border-slate-200 rounded-lg text-sm w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                    </div>
                    <div class="flex gap-2">
                        <select name="category" onchange="this.form.submit()" class="pl-4 pr-10 py-2.5 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white transition-all appearance-none cursor-pointer" style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 10px center; background-size: 16px;">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->nama }}</option>
                            @endforeach
                        </select>
                        <select name="supplier" onchange="this.form.submit()" class="pl-4 pr-10 py-2.5 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white transition-all appearance-none cursor-pointer" style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 10px center; background-size: 16px;">
                            <option value="">All Suppliers</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}" {{ request('supplier') == $sup->id ? 'selected' : '' }}>{{ $sup->company_name }}</option>
                            @endforeach
                        </select>
                        <select name="per_page" onchange="this.form.submit()" class="pl-4 pr-10 py-2.5 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-slate-50 transition-all appearance-none cursor-pointer font-medium" style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 10px center; background-size: 16px;">
                            @foreach([10, 15, 25, 50, 100] as $val)
                                <option value="{{ $val }}" {{ request('per_page', 15) == $val ? 'selected' : '' }}>Show {{ $val }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if(request('search') || request('category') || request('supplier'))
                        <a href="{{ route('product.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-sm font-bold flex items-center gap-2 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Clear
                        </a>
                    @endif
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs">Product Info</th>
                            <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs">Category</th>
                            <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs">Supplier</th>
                            <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs">Stock</th>
                            <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs">Pricing</th>
                            <th class="py-3 px-5 font-semibold uppercase tracking-wider text-xs text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($products as $index => $product)
                            <tr
                                class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-slate-50/50' }} hover:bg-blue-50/30 transition-colors">
                                <td class="py-4 px-5">
                                    <div class="font-bold text-slate-800">{{ $product->nama }}</div>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-[11px] px-1.5 py-0.5 bg-slate-100 text-slate-600 rounded font-medium">SKU: {{ $product->sku }}</span>
                                        @if($product->merk)
                                            <span class="text-[11px] px-1.5 py-0.5 bg-blue-50 text-blue-600 rounded font-bold uppercase tracking-wider">{{ $product->merk }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-4 px-5 font-medium text-slate-700">
                                    {{ $product->category->nama ?? 'Uncategorized' }}
                                </td>
                                <td class="py-4 px-5">
                                    <div class="text-slate-600 text-[13px]">
                                        {{ $product->supplier->company_name ?? 'No Supplier' }}
                                    </div>
                                </td>
                                <td class="py-4 px-5">
                                    <div class="flex flex-col">
                                        <span
                                            class="font-bold {{ $product->stok <= $product->min_stok ? 'text-red-600' : 'text-slate-800' }}">
                                            {{ number_format($product->stok) }} {{ $product->unit }}
                                        </span>
                                        <span class="text-[10px] text-slate-500 uppercase">Min: {{ $product->min_stok }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-5">
                                    <div class="flex flex-col">
                                        <span class="text-xs text-slate-500">Sell: <span class="font-bold text-blue-600">Rp
                                                {{ number_format($product->harga_jual, 0, ',', '.') }}</span></span>
                                        <span class="text-xs text-slate-400">Buy: Rp
                                            {{ number_format($product->harga_beli, 0, ',', '.') }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-5 text-right space-x-2">
                                    <button @click="openEditModal({{ $product->toJson() }})"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors inline-block"
                                        title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                    </button>
                                    <button @click="openDeleteModal({{ $product->toJson() }})"
                                        class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition-colors inline-block"
                                        title="Delete">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-500">
                                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                    <p class="text-sm font-medium text-slate-600">No products found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($products->hasPages())
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50">
                    {{ $products->links() }}
                </div>
            @endif
        </div>

        @php
            $unitOptions = [
                'Satuan Berat/Volume' => ['Kg', 'Ton', 'Liter', 'Galon'],
                'Satuan Ukuran' => ['Meter', 'Batang', 'Lembar', 'Keping'],
                'Satuan Kemasan' => ['Box', 'Karton', 'Kaleng', 'Sak', 'Pack', 'Rol', 'Ikat'],
                'Satuan Unit/Eceran' => ['Pcs', 'Buah', 'Lusin', 'Set', 'Pasang'],
            ];
        @endphp

        <!-- ADD MODAL -->
        <div x-show="addModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center" style="display: none;">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="addModalOpen = false"
                x-transition.opacity></div>
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl relative z-10 m-4 overflow-hidden transform transition-all"
                x-transition>
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Add New Product</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Enter product details</p>
                    </div>
                    <button @click="addModalOpen = false"
                        class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-lg hover:bg-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
                <form action="{{ route('product.store') }}" method="POST">
                    @csrf
                    <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Product
                                    Name <span class="text-red-500">*</span></label>
                                <input type="text" name="nama" value="{{ old('nama') }}" required
                                    class="w-full border rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white {{ $errors->has('nama') ? 'border-red-400' : 'border-slate-300' }}">
                                @error('nama')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Merk / Brand</label>
                                <input type="text" name="merk" value="{{ old('merk') }}"
                                    class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white"
                                    placeholder="Contoh: Holcim, Rucika, dll">
                            </div>
                        </div>
                        <div class="grid grid-cols-1">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">SKU</label>
                                <input type="text" name="sku" value="{{ old('sku') }}"
                                    class="w-full border rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white {{ $errors->has('sku') ? 'border-red-400' : 'border-slate-300' }}"
                                    placeholder="Kosongkan jika otomatis">
                                @error('sku')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Category</label>
                                <select name="kategori_id"
                                    class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Supplier</label>
                                <select name="supplier_id"
                                    class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->company_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Stock
                                    <span class="text-red-500">*</span></label>
                                <input type="number" name="stok" value="{{ old('stok', 0) }}" @focus="$event.target.select()" required
                                    class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Unit
                                    <span class="text-red-500">*</span></label>
                                <select name="unit" required
                                    class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                    <option value="">Select Unit</option>
                                    @foreach($unitOptions as $group => $options)
                                        <optgroup label="{{ $group }}">
                                            @foreach($options as $option)
                                                <option value="{{ $option }}" {{ old('unit') == $option ? 'selected' : '' }}>
                                                    {{ $option }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Min
                                    Stock <span class="text-red-500">*</span></label>
                                <input type="number" name="min_stok" value="{{ old('min_stok', 5) }}" @focus="$event.target.select()" required
                                    class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Purchase
                                    Price (Rp) <span class="text-red-500">*</span></label>
                                <input type="text" :value="formatNumber(addForm.harga_beli)"
                                    @input="addForm.harga_beli = unformatNumber($event.target.value); updatePrices('add', 'beli')"
                                    @focus="$event.target.select()" required
                                    class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white font-mono">
                                <input type="hidden" name="harga_beli" :value="addForm.harga_beli">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Margin (%)</label>
                                <input type="number" step="0.01" x-model="addForm.margin"
                                    @input="updatePrices('add', 'margin')"
                                    @focus="$event.target.select()"
                                    class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-slate-50 font-bold text-blue-600">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Selling
                                    Price (Rp) <span class="text-red-500">*</span></label>
                                <input type="text" :value="formatNumber(addForm.harga_jual)"
                                    @input="addForm.harga_jual = unformatNumber($event.target.value); updatePrices('add', 'jual')"
                                    @focus="$event.target.select()" required
                                    class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white font-mono">
                                <input type="hidden" name="harga_jual" :value="addForm.harga_jual">
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/50">
                        <button type="button" @click="addModalOpen = false"
                            class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors shadow-sm">Cancel</button>
                        <button type="submit"
                            class="px-5 py-2 bg-[#0f172a] hover:bg-slate-800 rounded-lg text-sm font-bold text-white shadow transition-colors">Add
                            Product</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- EDIT MODAL -->
        <div x-show="editModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center" style="display: none;">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="editModalOpen = false"
                x-transition.opacity></div>
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl relative z-10 m-4 overflow-hidden transform transition-all"
                x-transition>
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Edit Product</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Update product info</p>
                    </div>
                    <button @click="editModalOpen = false"
                        class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-lg hover:bg-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
                <form :action="`/products/${editForm.id}`" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="edit_id" :value="editForm.id">
                    <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Product
                                    Name <span class="text-red-500">*</span></label>
                                <input type="text" name="nama" x-model="editForm.nama" required
                                    class="w-full border rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white {{ $errors->has('nama') && old('edit_id') ? 'border-red-400' : 'border-slate-300' }}">
                                @if(old('edit_id')) @error('nama')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror @endif
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Merk / Brand</label>
                                <input type="text" name="merk" x-model="editForm.merk"
                                    class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white"
                                    placeholder="Contoh: Holcim, Rucika, dll">
                            </div>
                        </div>
                        <div class="grid grid-cols-1">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">SKU</label>
                                <input type="text" name="sku" x-model="editForm.sku"
                                    class="w-full border rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white {{ $errors->has('sku') && old('edit_id') ? 'border-red-400' : 'border-slate-300' }}">
                                @if(old('edit_id')) @error('sku')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror @endif
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Category</label>
                                <select name="kategori_id" x-model="editForm.kategori_id"
                                    class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Supplier</label>
                                <select name="supplier_id" x-model="editForm.supplier_id"
                                    class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->company_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Stock
                                    <span class="text-red-500">*</span></label>
                                <input type="number" name="stok" x-model="editForm.stok" @focus="$event.target.select()" required
                                    class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Unit
                                    <span class="text-red-500">*</span></label>
                                <select name="unit" x-model="editForm.unit" required
                                    class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                    <option value="">Select Unit</option>
                                    @foreach($unitOptions as $group => $options)
                                        <optgroup label="{{ $group }}">
                                            @foreach($options as $option)
                                                <option value="{{ $option }}">{{ $option }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Min
                                    Stock <span class="text-red-500">*</span></label>
                                <input type="number" name="min_stok" x-model="editForm.min_stok" @focus="$event.target.select()" required
                                    class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Purchase
                                    Price (Rp) <span class="text-red-500">*</span></label>
                                <input type="text" :value="formatNumber(editForm.harga_beli)"
                                    @input="editForm.harga_beli = unformatNumber($event.target.value); updatePrices('edit', 'beli')"
                                    @focus="$event.target.select()" required
                                    class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white font-mono">
                                <input type="hidden" name="harga_beli" :value="editForm.harga_beli">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Margin (%)</label>
                                <input type="number" step="0.01" x-model="editForm.margin"
                                    @input="updatePrices('edit', 'margin')"
                                    @focus="$event.target.select()"
                                    class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-slate-50 font-bold text-blue-600">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Selling
                                    Price (Rp) <span class="text-red-500">*</span></label>
                                <input type="text" :value="formatNumber(editForm.harga_jual)"
                                    @input="editForm.harga_jual = unformatNumber($event.target.value); updatePrices('edit', 'jual')"
                                    @focus="$event.target.select()" required
                                    class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white font-mono">
                                <input type="hidden" name="harga_jual" :value="editForm.harga_jual">
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/50">
                        <button type="button" @click="editModalOpen = false"
                            class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors shadow-sm">Cancel</button>
                        <button type="submit"
                            class="px-5 py-2 bg-[#0f172a] hover:bg-slate-800 rounded-lg text-sm font-bold text-white shadow transition-colors">Update
                            Product</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- DELETE MODAL -->
        <div x-show="deleteModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center" style="display: none;">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="deleteModalOpen = false"
                x-transition.opacity></div>
            <div class="bg-white rounded-xl shadow-2xl w-full max-sm relative z-10 m-4 overflow-hidden transform transition-all"
                x-transition>
                <div class="p-6 text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 mb-2">Delete Product?</h3>
                    <p class="text-sm text-slate-500">Are you sure you want to delete <span class="font-bold text-slate-700"
                            x-text="deleteForm.nama"></span>?</p>
                </div>
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex gap-3">
                    <button type="button" @click="deleteModalOpen = false"
                        class="flex-1 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors shadow-sm">Cancel</button>
                    <form :action="`/products/${deleteForm.id}`" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full py-2 bg-red-600 hover:bg-red-700 rounded-lg text-sm font-bold text-white shadow transition-colors">Yes,
                            Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection