@extends('layouts.app')

@section('title', 'Toko Bangunan - Data Barang')
@section('header_title', 'Data Barang')

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
            'merk' => old('merk'),
            'aktif_grosir' => old('aktif_grosir'),
            'min_qty_grosir' => old('min_qty_grosir'),
            'harga_grosir' => old('harga_grosir'),
            'units' => old('units', [])
        ] : (object)['units' => []];

        $productMap = [];
        foreach ($products as $p) {
            $productMap[$p->id] = [
                'id'              => $p->id,
                'nama'            => $p->nama,
                'sku'             => $p->sku,
                'merk'            => $p->merk,
                'kategori_id'     => $p->kategori_id,
                'sub_kategori_id' => $p->sub_kategori_id,
                'supplier_id'     => $p->supplier_id,
                'unit'            => $p->unit,
                'harga_beli'      => (int)$p->harga_beli,
                'harga_jual'      => (int)$p->harga_jual,
                'stok'            => (int)$p->stok,
                'min_stok'        => (int)$p->min_stok,
                'aktif_grosir'    => (bool)$p->aktif_grosir,
                'min_qty_grosir'  => (int)$p->min_qty_grosir,
                'harga_grosir'    => (int)$p->harga_grosir,
                'image'           => $p->image,
                'units'           => $p->units->map(function($u) {
                    return [
                        'id'         => $u->id,
                        'nama'       => $u->nama,
                        'isi'        => $u->isi,
                        'harga_jual' => (int)$u->harga_jual,
                        'is_base'    => (bool)$u->is_base,
                    ];
                })->values()->all(),
            ];
        }
    @endphp

    <script>
        window.productManagerConfig = {
            initialAddModal: {{ $errors->any() && !old('edit_id') ? 'true' : 'false' }},
            initialEditModal: {{ $errors->any() && old('edit_id') ? 'true' : 'false' }},
            editFormData: {!! json_encode($editFormData) !!},
            oldHargaBeli: {{ old('harga_beli', 0) }},
            oldHargaJual: {{ old('harga_jual', 0) }},
            oldKategoriId: {!! json_encode(old('kategori_id')) !!},
            oldSubKategoriId: {!! json_encode(old('sub_kategori_id')) !!},
            oldUnits: {!! json_encode(old('units', [])) !!},
            availableUnits: {!! json_encode($availableUnits) !!}
        };

        // Product data map (built safely server-side)
        window.productData = {!! json_encode(isset($productMap) ? $productMap : [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) !!};

        // Available units for dropdowns
        const availableUnitsData = window.productManagerConfig.availableUnits;

        function productManager(config = window.productManagerConfig) {
            return {
                // State
                addModalOpen: config.initialAddModal,
                editModalOpen: config.initialEditModal,
                deleteModalOpen: false,
                barcodePrintModalOpen: false,
                selectedProducts: [],
                currentPageProductIds: [],
                printProducts: [],
                printLayout: 'a4',
                editForm: {
                    id: null, nama: '', sku: '', merk: '', unit: '',
                    harga_beli_kemasan: 0, isi_kemasan_beli: 1,
                    harga_beli: 0, harga_jual: 0, margin: 0,
                    stok: 0, min_stok: 0, units: [],
                    kategori_id: null, sub_kategori_id: null,
                    supplier_id: null, imagePreview: null,
                    skuAuto: false,
                    aktif_grosir: false, min_qty_grosir: 10, harga_grosir: 0
                },
                addForm: {
                    harga_beli_kemasan: 0, isi_kemasan_beli: 1,
                    harga_beli: 0, harga_jual: 0, margin: 0,
                    kategori_id: null, sub_kategori_id: null,
                    units: [], unit: '', imagePreview: null,
                    skuAuto: true,
                    aktif_grosir: false, min_qty_grosir: 10, harga_grosir: 0
                },
                editSubCategories: [],
                addSubCategories: [],
                availableUnits: availableUnitsData,
                deleteForm: {},

                // Called automatically by Alpine on startup
                init() {
                    try {
                        this.addModalOpen = config.initialAddModal || false;
                        this.editModalOpen = config.initialEditModal;
                        this.availableUnits = config.availableUnits;
                        this.addForm.harga_beli = config.oldHargaBeli;
                        this.addForm.harga_beli_kemasan = config.oldHargaBeli;
                        this.addForm.isi_kemasan_beli = 1;
                        this.addForm.harga_jual = config.oldHargaJual;
                        this.addForm.margin = config.oldHargaBeli > 0
                            ? parseFloat(((config.oldHargaJual / config.oldHargaBeli - 1) * 100).toFixed(2)) : 0;
                        this.addForm.kategori_id = config.oldKategoriId;
                        this.addForm.sub_kategori_id = config.oldSubKategoriId;
                        this.addForm.units = (config.oldUnits && config.oldUnits.length > 0) ? config.oldUnits.map(u => Object.assign({}, u, {
                            target_satuan: u.target_satuan || (u.is_base ? u.nama : this.addForm.unit),
                            target_isi: Number(u.target_isi) || (u.is_base ? 1 : Number(u.isi))
                        })) : [];
                        const oldSku = {!! json_encode(old('sku', '')) !!};
                        this.addForm.sku = oldSku;
                        this.addForm.skuAuto = oldSku === '' || oldSku === '[Otomatis]';

                        if (config.editFormData && config.editFormData.id) {
                            this.editForm = Object.assign(this.editForm, config.editFormData);
                            this.editForm.harga_beli_kemasan = Math.floor(this.editForm.harga_beli);
                            this.editForm.isi_kemasan_beli = 1;
                            this.editForm.skuAuto = !config.editFormData.sku;
                            if (this.editForm.harga_beli > 0) {
                                this.editForm.margin = parseFloat(((this.editForm.harga_jual / this.editForm.harga_beli - 1) * 100).toFixed(2));
                            }
                        }

                        this.$watch('addForm.skuAuto', value => {
                            if (value) this.addForm.sku = '[Otomatis]';
                            else if (this.addForm.sku === '[Otomatis]') this.addForm.sku = '';
                        });
                        this.$watch('addForm.kategori_id', value => {
                            if (!value) {
                                this.addForm.skuAuto = false;
                                if (this.addForm.sku === '[Otomatis]') this.addForm.sku = '';
                            }
                        });
                        this.$watch('editForm.skuAuto', value => {
                            if (value) this.editForm.sku = '[Otomatis]';
                            else if (this.editForm.sku === '[Otomatis]') this.editForm.sku = '';
                        });
                        this.$watch('editForm.kategori_id', value => {
                            if (!value) {
                                this.editForm.skuAuto = false;
                                if (this.editForm.sku === '[Otomatis]') this.editForm.sku = '';
                            }
                        });

                        this.currentPageProductIds = Object.keys(window.productData || {}).map(Number);

                        if (config.initialAddModal && config.oldKategoriId) {
                            this.fetchSubCategories('add', config.oldKategoriId, false);
                        }
                        if (config.initialEditModal && config.editFormData && config.editFormData.kategori_id) {
                            this.fetchSubCategories('edit', config.editFormData.kategori_id, false);
                        }
                    } catch (e) {
                        console.error('Error during productManager init:', e);
                    }
                },

                async fetchSubCategories(type, categoryId, resetSub = true) {
                    if (resetSub) {
                        if (type === 'add') this.addForm.sub_kategori_id = null;
                        else this.editForm.sub_kategori_id = null;
                    }

                    if (!categoryId) {
                        if (type === 'add') this.addSubCategories = [];
                        else this.editSubCategories = [];
                        return;
                    }
                    try {
                        const response = await fetch(`/api/sub-categories/${categoryId}`);
                        const data = await response.json();
                        if (type === 'add') this.addSubCategories = data;
                        else this.editSubCategories = data;
                    } catch (error) {
                        console.error('Error fetching sub-categories:', error);
                    }
                },

                addUnit(type) {
                    let form = type === 'add' ? this.addForm : this.editForm;
                    if (!form.units) form.units = [];
                    const isBase = form.units.length === 0;
                    form.units.push({
                        nama: isBase ? (form.unit || '') : '',
                        isi: 1,
                        target_satuan: isBase ? (form.unit || '') : (form.unit || ''),
                        target_isi: 1,
                        harga_jual: isBase ? (form.harga_jual || 0) : 0,
                        is_base: isBase
                    });
                    if (isBase) {
                        form.unit = form.units[0].nama;
                        form.harga_jual = form.units[0].harga_jual;
                    }
                },

                removeUnit(type, index) {
                    let form = type === 'add' ? this.addForm : this.editForm;
                    form.units.splice(index, 1);
                },

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
                    
                    if (source === 'beli' || source === 'jual') {
                        // Jika harga beli atau harga jual berubah, hitung ulang marginnya
                        if (form.harga_beli > 0) {
                            form.margin = parseFloat(((form.harga_jual / form.harga_beli - 1) * 100).toFixed(2));
                        }
                    } else if (source === 'margin') {
                        // Jika margin yang diubah manual, hitung ulang harga jualnya
                        form.harga_jual = Math.round(form.harga_beli * (1 + form.margin / 100));
                        
                        // Update harga satuan-satuan lainnya mengikuti margin baru
                        if (form.units) {
                            form.units.forEach(unit => {
                                unit.harga_jual = Math.round((form.harga_beli * unit.isi) * (1 + form.margin / 100));
                            });
                        }
                    }
                },

                    async openEditModal(product) {
                    this.editForm = Object.assign({
                        id: null, nama: '', sku: '', merk: '', unit: '',
                        harga_beli: 0, harga_jual: 0, margin: 0,
                        stok: 0, min_stok: 0, units: [],
                        kategori_id: null, sub_kategori_id: null,
                        supplier_id: null, imagePreview: null,
                        aktif_grosir: false, min_qty_grosir: 10, harga_grosir: 0
                    }, JSON.parse(JSON.stringify(product)));

                    this.editForm.harga_beli = Math.floor(this.editForm.harga_beli);
                    this.editForm.harga_beli_kemasan = this.editForm.harga_beli;
                    this.editForm.isi_kemasan_beli = 1;
                    this.editForm.harga_jual = Math.floor(this.editForm.harga_jual);
                    this.editForm.stok = Math.floor(this.editForm.stok);
                    this.editForm.min_stok = Math.floor(this.editForm.min_stok);

                    if (this.editForm.harga_beli > 0) {
                        this.editForm.margin = parseFloat(((this.editForm.harga_jual / this.editForm.harga_beli - 1) * 100).toFixed(2));
                    } else {
                        this.editForm.margin = 0;
                    }

                    if (this.editForm.kategori_id) {
                        await this.fetchSubCategories('edit', this.editForm.kategori_id, false);
                    } else {
                        this.editSubCategories = [];
                    }

                    this.editForm.units = (product.units || []).map(u => Object.assign({}, u, {
                        target_satuan: u.target_satuan || (u.is_base ? u.nama : product.unit),
                        target_isi: Number(u.target_isi) || (u.is_base ? 1 : Number(u.isi))
                    }));
                    if (this.editForm.units.length === 0 && this.editForm.unit) {
                        this.editForm.units.push({
                            nama: this.editForm.unit,
                            isi: 1,
                            target_satuan: '',
                            target_isi: 1,
                            harga_jual: this.editForm.harga_jual,
                            is_base: true
                        });
                    }

                    this.editForm.skuAuto = !product.sku;
                    this.editModalOpen = true;
                },

                openDeleteModal(product) {
                    this.deleteForm = product;
                    this.deleteModalOpen = true;
                },

                toggleSelectAll(checked) {
                    if (checked) {
                        this.selectedProducts = [...this.currentPageProductIds];
                    } else {
                        this.selectedProducts = [];
                    }
                },

                toggleSelectProduct(id) {
                    const idx = this.selectedProducts.indexOf(id);
                    if (idx > -1) {
                        this.selectedProducts.splice(idx, 1);
                    } else {
                        this.selectedProducts.push(id);
                    }
                },

                openBarcodePrintModal() {
                    this.printProducts = this.selectedProducts.map(id => {
                        const prod = window.productData[id];
                        return {
                            id: prod.id,
                            nama: prod.nama,
                            sku: prod.sku || '[Otomatis]',
                            qty: 1
                        };
                    });
                    this.printLayout = 'a4';
                    this.barcodePrintModalOpen = true;
                },

                removePrintProduct(index) {
                    this.printProducts.splice(index, 1);
                    if (this.printProducts.length === 0) {
                        this.barcodePrintModalOpen = false;
                    }
                },

                printBarcodes() {
                    const invalidProds = this.printProducts.filter(p => p.sku === '[Otomatis]' || !p.sku);
                    if (invalidProds.length > 0) {
                        Swal.fire({
                            title: 'Pemberitahuan',
                            text: 'Beberapa produk terpilih belum memiliki SKU di database. Silakan edit produk tersebut terlebih dahulu dan centang "Generate SKU Otomatis" agar mendapatkan kode SKU yang unik sebelum dicetak.',
                            icon: 'warning',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#2563eb'
                        });
                        return;
                    }

                    const printArea = document.getElementById('print-barcode-area');
                    if (!printArea) return;
                    printArea.innerHTML = ''; // clear first

                    const allLabels = [];
                    this.printProducts.forEach(prod => {
                        const qty = parseInt(prod.qty) || 1;
                        for (let i = 0; i < qty; i++) {
                            const wrapper = document.createElement('div');
                            wrapper.className = 'barcode-label';

                            const nameEl = document.createElement('div');
                            nameEl.className = 'label-title';
                            nameEl.innerText = prod.nama;
                            wrapper.appendChild(nameEl);

                            const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                            svg.className = 'barcode-svg';
                            wrapper.appendChild(svg);

                            allLabels.push({ wrapper, svg, sku: prod.sku });
                        }
                    });

                    // Group labels into pages of 30 (A4 layout)
                    const labelsPerPage = 30;
                    for (let i = 0; i < allLabels.length; i += labelsPerPage) {
                        const pageDiv = document.createElement('div');
                        pageDiv.className = 'print-page layout-a4';
                        
                        const pageItems = allLabels.slice(i, i + labelsPerPage);
                        pageItems.forEach(item => {
                            pageDiv.appendChild(item.wrapper);
                        });
                        
                        printArea.appendChild(pageDiv);
                    }

                    // Render barcodes (A4 layout sizing)
                    allLabels.forEach(item => {
                        try {
                            JsBarcode(item.svg, item.sku, {
                                format: "CODE128",
                                width: 1.2,
                                height: 25,
                                displayValue: true,
                                fontSize: 10,
                                margin: 2
                            });
                        } catch (e) {
                            console.error("Gagal generate barcode untuk SKU: " + item.sku, e);
                        }
                    });

                    setTimeout(() => {
                        window.print();
                    }, 300);
                }
            };
        }
    </script>
    <div x-data="productManager()" x-init="currentPageProductIds = {{ json_encode($products->pluck('id')->toArray()) }}">
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
                    <h3 class="text-lg font-bold text-slate-800">Katalog Produk</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Kelola stok dan harga jual Anda</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('category.index') }}"
                        class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2.5 rounded-lg text-sm font-bold flex items-center gap-2 transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        Kategori & Sub
                    </a>
                    <button x-show="selectedProducts.length > 0" @click="openBarcodePrintModal()" type="button"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-sm font-bold flex items-center gap-2 transition-colors shadow-sm" style="display: none;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak Barcode Terpilih (<span x-text="selectedProducts.length"></span>)
                    </button>
                    <button @click="addForm.units = []; addUnit('add'); addModalOpen = true"
                        class="bg-[#0f172a] hover:bg-slate-800 text-white px-4 py-2.5 rounded-lg text-sm font-bold flex items-center gap-2 transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Tambah Produk
                    </button>
                </div>
            </div>
            <div class="p-4 border-b border-slate-200 bg-white flex flex-wrap gap-3">
                <form action="{{ route('product.index') }}" method="GET" class="flex flex-wrap gap-3 flex-1">
                    <div class="relative min-w-[300px] flex-1">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama barang atau SKU..." class="pl-10 pr-4 py-2.5 border border-slate-200 rounded-lg text-sm w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                    </div>
                    <div class="flex gap-2">
                        <select name="category" onchange="this.form.submit()" class="pl-4 pr-10 py-2.5 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white transition-all appearance-none cursor-pointer" style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 10px center; background-size: 16px;">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->nama }}</option>
                            @endforeach
                        </select>
                        <select name="supplier" onchange="this.form.submit()" class="pl-4 pr-10 py-2.5 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white transition-all appearance-none cursor-pointer" style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 10px center; background-size: 16px;">
                            <option value="">Semua Supplier</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}" {{ request('supplier') == $sup->id ? 'selected' : '' }}>{{ $sup->company_name }}</option>
                            @endforeach
                        </select>
                        <select name="per_page" onchange="this.form.submit()" class="pl-4 pr-10 py-2.5 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-slate-50 transition-all appearance-none cursor-pointer font-medium" style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 10px center; background-size: 16px;">
                            @foreach([10, 15, 25, 50, 100] as $val)
                                <option value="{{ $val }}" {{ request('per_page', 15) == $val ? 'selected' : '' }}>Tampil {{ $val }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if(request('search') || request('category') || request('supplier'))
                        <a href="{{ route('product.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-sm font-bold flex items-center gap-2 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Bersihkan
                        </a>
                    @endif
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs xl:text-sm whitespace-nowrap">
                    <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-2 xl:px-3 font-semibold text-xs w-10">
                                <input type="checkbox" @change="toggleSelectAll($event.target.checked)" :checked="selectedProducts.length > 0 && selectedProducts.length === currentPageProductIds.length" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
                            </th>
                            <th class="py-3 px-2 xl:px-3 font-semibold uppercase tracking-wider text-xs hidden xl:table-cell">FOTO</th>
                            <th class="py-3 px-2 xl:px-3 font-semibold uppercase tracking-wider text-xs">Info Produk</th>
                            <th class="py-3 px-2 xl:px-3 font-semibold uppercase tracking-wider text-xs hidden xl:table-cell">Kategori</th>
                            <th class="py-3 px-2 xl:px-3 font-semibold uppercase tracking-wider text-xs hidden xl:table-cell">Supplier</th>
                            <th class="py-3 px-2 xl:px-3 font-semibold uppercase tracking-wider text-xs">Stok</th>
                            <th class="py-3 px-2 xl:px-3 font-semibold uppercase tracking-wider text-xs">Harga</th>
                            <th class="py-3 px-2 xl:px-3 font-semibold uppercase tracking-wider text-xs text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($products as $index => $product)
                            <tr
                                class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-slate-50/50' }} hover:bg-blue-50/30 transition-colors">
                                <td class="py-4 px-2 xl:px-3 w-10">
                                    <input type="checkbox" :value="{{ $product->id }}" :checked="selectedProducts.includes({{ $product->id }})" @change="toggleSelectProduct({{ $product->id }})" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
                                </td>
                                <td class="py-4 px-2 xl:px-3 hidden xl:table-cell">
                                    <div class="w-10 h-10 rounded-lg overflow-hidden bg-slate-100 border border-slate-200 flex-shrink-0">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->nama }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-slate-400">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-4 px-2 xl:px-3 whitespace-normal max-w-[200px] lg:max-w-[250px] xl:max-w-none break-words">
                                    <div class="font-bold text-slate-800 leading-tight">{{ $product->nama }}</div>
                                    <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                        @if($product->sku)
                                            <span class="text-[10px] text-slate-500 font-mono">SKU: {{ $product->sku }}</span>
                                        @endif
                                        @if($product->merk)
                                            <span class="text-[9px] px-1.5 py-0.5 bg-blue-50 text-blue-700 rounded font-bold uppercase tracking-wider border border-blue-100/50 shadow-sm">{{ $product->merk }}</span>
                                        @endif
                                        @if($product->category)
                                            <span class="xl:hidden text-[9px] px-1.5 py-0.5 bg-slate-100 text-slate-700 rounded font-bold uppercase tracking-wider border border-slate-200/50 shadow-sm">
                                                {{ $product->category->nama }}
                                                @if($product->subCategory)
                                                    • {{ $product->subCategory->nama }}
                                                @endif
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-4 px-2 xl:px-3 hidden xl:table-cell">
                                    <div class="font-bold text-slate-700 text-[13px]">{{ $product->category->nama ?? 'Uncategorized' }}</div>
                                    @if($product->subCategory)
                                        <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                                            <span class="w-3 h-px bg-slate-200"></span>
                                            <span>Sub: <span class="font-medium text-slate-600">{{ $product->subCategory->nama }}</span></span>
                                        </div>
                                    @endif
                                </td>
                                <td class="py-4 px-2 xl:px-3 hidden xl:table-cell">
                                    <div class="text-slate-600 text-[13px]">
                                        {{ $product->supplier->company_name ?? 'No Supplier' }}
                                    </div>
                                </td>
                                <td class="py-4 px-2 xl:px-3">
                                    <div class="flex flex-col">
                                        <span
                                            class="font-bold {{ $product->stok <= $product->min_stok ? 'text-red-600' : 'text-slate-800' }}">
                                            {{ number_format($product->stok) }} {{ $product->unit }}
                                        </span>
                                        <span class="text-[10px] text-slate-500 uppercase">Min: {{ $product->min_stok }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-2 xl:px-3">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-blue-600 text-xs xl:text-sm">Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</span>
                                        <span class="text-[10px] text-slate-400 mt-0.5 font-medium">Beli: Rp {{ number_format($product->harga_beli, 0, ',', '.') }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-2 xl:px-3 text-right space-x-1 lg:space-x-2">
                                    <button @click="openEditModal(productData[{{ $product->id }}])"
                                        class="p-1 xl:p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors inline-block"
                                        title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                    </button>
                                    <button @click="openDeleteModal(productData[{{ $product->id }}])"
                                        class="p-1 xl:p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition-colors inline-block"
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
                                <td colspan="100" class="py-12 text-center text-slate-500">
                                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                    <p class="text-sm font-medium text-slate-600">Produk tidak ditemukan.</p>
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
                        <h3 class="text-lg font-bold text-slate-800">Tambah Produk Baru</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Masukkan detail informasi produk</p>
                    </div>
                    <button @click="addModalOpen = false"
                        class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-lg hover:bg-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
                
                @if(!old('edit_id') && $errors->any())
                    <div class="px-6 pt-4">
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                            <p class="text-sm font-bold mb-1">Terdapat kesalahan pada form:</p>
                            <ul class="list-disc list-inside text-sm space-y-0.5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form action="{{ route('product.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Nama Produk <span class="text-red-500">*</span></label>
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
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">SKU</label>
                                    <label class="flex items-center gap-1 text-[11px] text-slate-500 cursor-pointer">
                                        <input type="checkbox" x-model="addForm.skuAuto" :disabled="!addForm.kategori_id" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5">
                                        <span class="font-bold">Otomatis</span>
                                    </label>
                                </div>
                                <input type="text" name="sku" x-model="addForm.sku"
                                    :disabled="addForm.skuAuto"
                                    :class="addForm.skuAuto ? 'bg-slate-50 border-slate-200 text-slate-400 cursor-not-allowed font-medium' : 'bg-white border-slate-300 text-slate-800'"
                                    class="w-full border rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500">
                                @error('sku')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                <p class="text-[10px] text-slate-400 mt-1" x-show="!addForm.kategori_id">Pilih kategori terlebih dahulu untuk auto SKU</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Product Image</label>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg border-2 border-dashed border-slate-200 bg-slate-50 overflow-hidden flex items-center justify-center text-slate-400 group hover:border-blue-300 transition-colors cursor-pointer relative">
                                        <template x-if="!addForm.imagePreview">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                        </template>
                                        <template x-if="addForm.imagePreview">
                                            <img :src="addForm.imagePreview" class="w-full h-full object-cover">
                                        </template>
                                        <input type="file" name="image" class="absolute inset-0 opacity-0 cursor-pointer" 
                                            @change="const file = $event.target.files[0]; if(file) { addForm.imagePreview = URL.createObjectURL(file); }">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[9px] text-slate-500 leading-tight">JPG, PNG. Max 2MB.</p>
                                        <button type="button" x-show="addForm.imagePreview" @click="addForm.imagePreview = null;" class="text-[9px] text-red-500 font-bold uppercase">Hapus</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label
                                     class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Category</label>
                                <select name="kategori_id" x-model="addForm.kategori_id"
                                    @change="fetchSubCategories('add', $event.target.value)"
                                    class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Sub Category</label>
                                <select name="sub_kategori_id" x-model="addForm.sub_kategori_id"
                                    :disabled="!addForm.kategori_id"
                                    :class="!addForm.kategori_id ? 'bg-slate-50 cursor-not-allowed opacity-60' : 'bg-white'"
                                    class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 transition-all">
                                    <template x-if="!addForm.kategori_id">
                                        <option value="">Silahkan pilih kategori dulu</option>
                                    </template>
                                    <template x-if="addForm.kategori_id">
                                        <option value="">Select Sub Category</option>
                                    </template>
                                    <template x-for="sub in addSubCategories" :key="sub.id">
                                        <option :value="sub.id" x-text="sub.nama"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1">
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

                        <!-- MULTIPLE UNITS SECTION -->
                        <div class="border-t border-slate-100 pt-4 mt-2">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-widest">Multi-Satuan & Harga</h4>
                                    <p class="text-[10px] text-slate-400 mt-0.5">Gunakan ini jika barang dijual dalam berbagai satuan (Contoh: Dus, Box, dll)</p>
                                    <div class="mt-1 flex items-center gap-1.5">
                                        <span class="flex w-2 h-2 rounded-full bg-blue-500"></span>
                                        <p class="text-[9px] text-blue-600 font-bold uppercase italic">PENTING: Centang "Utama" pada satuan dasar (misal: Pcs) untuk harga standar.</p>
                                    </div>
                                </div>
                                <button type="button" @click="addUnit('add')" class="text-[10px] bg-blue-50 text-blue-600 px-2 py-1 rounded font-bold hover:bg-blue-100 transition-colors border border-blue-100 shadow-sm">
                                    + Tambah Satuan
                                </button>
                            </div>
                            
                            <div class="space-y-3">
                                <template x-for="(unit, index) in addForm.units" :key="index">
                                    <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm relative group hover:border-blue-300 transition-all">
                                        <button type="button" @click="removeUnit('add', index)" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity z-10 shadow-lg">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                        <div class="grid grid-cols-2 sm:grid-cols-12 gap-3 items-end">
                                            <div class="col-span-2 sm:col-span-3">
                                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Pilih Satuan</label>
                                                <select :name="`units[${index}][nama]`" x-model="unit.nama"
                                                    @change="if(unit.is_base) { addForm.unit = $event.target.value; unit.target_satuan = $event.target.value; unit.target_isi = 1; unit.isi = 1; }"
                                                    class="w-full border-slate-200 rounded text-xs p-2 focus:ring-blue-500 bg-slate-50/50">
                                                    <option value="">Pilih Satuan...</option>
                                                    <template x-for="std in availableUnits" :key="std.id">
                                                        <option :value="std.nama" x-text="std.nama"></option>
                                                    </template>
                                                    <template x-if="unit.nama && !availableUnits.find(u => u.nama === unit.nama)">
                                                        <option :value="unit.nama" x-text="unit.nama" selected></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <div class="col-span-2 sm:col-span-1">
                                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Jml</label>
                                                <div class="relative">
                                                    <input type="text" value="1" readonly
                                                        class="w-full border-slate-200 rounded text-xs p-2 focus:ring-blue-500 bg-slate-100 font-bold text-center text-slate-500">
                                                </div>
                                            </div>
                                            <div class="col-span-2 sm:col-span-1 hidden sm:flex items-center justify-center pb-2">
                                                <span class="text-slate-400 font-black">=</span>
                                            </div>
                                            <div class="col-span-2 sm:col-span-2">
                                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Konversi Ke</label>
                                                <select :name="`units[${index}][target_satuan]`" x-model="unit.target_satuan"
                                                    :disabled="unit.is_base"
                                                    :class="unit.is_base ? 'w-full border-slate-200 rounded text-xs p-2 bg-slate-100 font-bold text-slate-500 opacity-70 cursor-not-allowed' : 'w-full border-slate-200 rounded text-xs p-2 focus:ring-blue-500 bg-slate-50/50'"
                                                    @change="if(!unit.is_base) { unit.isi = (Number(unit.target_isi) || 1) * ((addForm.units.find(u => u.nama === ($event.target.value || addForm.unit)) || {}).isi || 1); unit.harga_jual = Math.round((addForm.harga_beli * unit.isi) * (1 + addForm.margin / 100)) }">
                                                    <option value="" disabled>Pilih...</option>
                                                    <template x-for="u in addForm.units.filter(u => u.nama)" :key="u.nama">
                                                        <option :value="u.nama" x-text="u.nama"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <div class="col-span-2 sm:col-span-2">
                                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Isi</label>
                                                <input type="number" step="1" min="1" :name="`units[${index}][target_isi]`" x-model="unit.target_isi" 
                                                    :readonly="unit.is_base"
                                                    :class="unit.is_base ? 'w-full border-slate-200 rounded text-xs p-2 bg-slate-100 font-bold text-slate-500 opacity-70 cursor-not-allowed' : 'w-full border-slate-200 rounded text-xs p-2 focus:ring-blue-500 bg-slate-50/50 font-bold'"
                                                    @input="if(!unit.is_base) { unit.isi = (Number($event.target.value) || 1) * ((addForm.units.find(u => u.nama === (unit.target_satuan || addForm.unit)) || {}).isi || 1); unit.harga_jual = Math.round((addForm.harga_beli * unit.isi) * (1 + addForm.margin / 100)) }">
                                                <input type="hidden" :name="`units[${index}][isi]`" :value="unit.isi || 1">
                                            </div>
                                            <div class="col-span-2 sm:col-span-2">
                                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Harga (Rp)</label>
                                                <input type="text" :value="formatNumber(unit.harga_jual)" 
                                                    @input="unit.harga_jual = unformatNumber($event.target.value); if(unit.is_base) { addForm.harga_jual = unit.harga_jual; updatePrices('add', 'jual') }"
                                                    class="w-full border-slate-200 rounded text-xs p-2 focus:ring-blue-500 font-mono bg-slate-50/50 font-bold text-blue-600">
                                                <input type="hidden" :name="`units[${index}][harga_jual]`" :value="unit.harga_jual">
                                            </div>
                                            <div class="col-span-2 sm:col-span-1 flex flex-row sm:flex-col items-center justify-between sm:justify-center pt-1 px-2 border-t border-dashed border-slate-100 sm:border-t-0 mt-2 sm:mt-0">
                                                <label class="text-[8px] font-black text-slate-400 uppercase mb-1 text-center leading-none">Utama?</label>
                                                <input type="checkbox" :name="`units[${index}][is_base]`" value="1" x-model="unit.is_base" 
                                                    @change="if($event.target.checked) { 
                                                        addForm.units.forEach((u, i) => { 
                                                            if(i !== index) u.is_base = false; 
                                                            else {
                                                                u.target_satuan = u.nama;
                                                                u.target_isi = 1;
                                                                u.isi = 1;
                                                            }
                                                        });
                                                        addForm.unit = unit.nama;
                                                        addForm.harga_jual = unit.harga_jual;
                                                    }"
                                                    class="rounded text-blue-600 focus:ring-blue-500 w-4 h-4 shadow-sm" title="Jadikan sebagai satuan harga utama">
                                            </div>
                                        </div>
                                        <!-- Helpful Summary -->
                                        <div class="mt-2 flex items-center gap-2">
                                            <div class="h-px flex-1 bg-slate-100"></div>
                                            <div class="px-2 py-0.5 bg-blue-50 rounded text-[9px] font-bold text-blue-700 italic border border-blue-100">
                                                Artinya: 1 <span x-text="unit.nama || '...'"></span> berisi <span x-text="Number(unit.target_isi) || '0'"></span> <span x-text="unit.target_satuan || addForm.unit || 'Unit Dasar'"></span>
                                            </div>
                                            <div class="h-px flex-1 bg-slate-100"></div>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="addForm.units.length === 0">
                                    <div class="text-center py-6 border-2 border-dashed border-slate-100 rounded-xl bg-slate-50/30">
                                        <svg class="w-8 h-8 text-slate-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                        <p class="text-[11px] text-slate-400 font-medium px-10">Belum ada pengaturan multi-satuan. Gunakan tombol di atas jika barang ini memiliki satuan jual grosir/box.</p>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Stock
                                    <span class="text-red-500">*</span></label>
                                <input type="number" name="stok" value="{{ old('stok', 0) }}" @focus="$event.target.select()" required
                                    class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                <p class="text-[10px] text-slate-500 mt-1 italic">Input jumlah dalam <span class="font-bold text-slate-700" x-text="addForm.unit || 'satuan utama'"></span>.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Min
                                    Stock <span class="text-red-500">*</span></label>
                                <input type="number" name="min_stok" value="{{ old('min_stok', 5) }}" @focus="$event.target.select()" required
                                    class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                            </div>
                        </div>

                        <!-- Harga Beli & Jual -->
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 mt-2">
                            <h4 class="text-[11px] font-black text-slate-600 uppercase tracking-widest mb-3 flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Pengaturan Harga Dasar
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="flex flex-col">
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1.5 h-7 flex items-end pb-1">Harga Beli (Modal per Satuan Utama) <span class="text-red-500 ml-1">*</span></label>
                                    <div class="relative flex-1">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">Rp</span>
                                        <input type="text" :value="formatNumber(addForm.harga_beli)"
                                            @input="addForm.harga_beli = unformatNumber($event.target.value); updatePrices('add', 'beli')"
                                            @focus="$event.target.select()" required
                                            class="w-full pl-9 pr-3 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 bg-white font-mono font-bold">
                                        <input type="hidden" name="harga_beli" :value="addForm.harga_beli">
                                    </div>
                                </div>
                                <div class="flex flex-col">
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1.5 h-7 flex items-end pb-1">Target Margin (%)</label>
                                    <div class="relative flex-1">
                                        <input type="number" step="0.01" x-model="addForm.margin"
                                            @input="updatePrices('add', 'margin')"
                                            @focus="$event.target.select()"
                                            class="w-full pr-8 pl-3 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 bg-white font-bold text-blue-600">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">%</span>
                                    </div>
                                </div>
                                <div class="flex flex-col">
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1.5 h-7 flex items-end pb-1">Harga Jual (Satuan Utama)</label>
                                    <div class="relative flex-1">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-blue-400">Rp</span>
                                        <input type="text" :value="formatNumber(addForm.harga_jual)"
                                            @input="addForm.harga_jual = unformatNumber($event.target.value); updatePrices('add', 'jual')"
                                            @focus="$event.target.select()" required
                                            class="w-full pl-9 pr-3 py-2.5 border border-blue-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 bg-blue-50 font-mono font-bold text-blue-700">
                                        <input type="hidden" name="harga_jual" :value="addForm.harga_jual">
                                    </div>
                                </div>
                            </div>
                            <p class="text-[10px] text-slate-500 mt-3 italic">Catatan: Modal / Harga Beli ini akan diperbarui otomatis (Moving Average Cost) setiap ada Transaksi Pembelian baru.</p>
                        </div>

                        <!-- Hidden but synced fields -->
                        <input type="hidden" name="unit" :value="addForm.unit">
                        <input type="hidden" name="harga_jual" :value="addForm.harga_jual">

                        <!-- HARGA GROSIR SECTION (ADD) -->
                        <div class="border border-emerald-200 bg-emerald-50/40 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <h4 class="text-[11px] font-black text-emerald-700 uppercase tracking-widest">Harga Grosir</h4>
                                </div>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <span class="text-[10px] font-bold text-slate-500">Aktifkan</span>
                                    <div class="relative">
                                        <input type="checkbox" name="aktif_grosir" value="1" x-model="addForm.aktif_grosir" class="sr-only peer">
                                        <div class="w-9 h-5 bg-slate-200 rounded-full peer peer-checked:bg-emerald-500 transition-colors"></div>
                                        <div class="absolute top-0.5 left-0.5 bg-white w-4 h-4 rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                                    </div>
                                </label>
                            </div>
                            <template x-if="addForm.aktif_grosir">
                                <div class="space-y-3">
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Min. Qty Grosir <span class="text-red-500">*</span></label>
                                            <div class="relative">
                                                <input type="number" name="min_qty_grosir" x-model="addForm.min_qty_grosir" min="2" @focus="$event.target.select()"
                                                    class="w-full border border-slate-300 rounded-lg text-sm p-2.5 pr-14 focus:ring-emerald-500 focus:border-emerald-500 bg-white font-bold">
                                                <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[9px] text-slate-400 font-bold" x-text="addForm.unit || 'unit'"></span>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Harga Grosir / Satuan <span class="text-red-500">*</span></label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-emerald-500">Rp</span>
                                                <input type="text" :value="formatNumber(addForm.harga_grosir)"
                                                    @input="addForm.harga_grosir = unformatNumber($event.target.value)" @focus="$event.target.select()"
                                                    class="w-full pl-9 pr-3 py-2.5 border border-emerald-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500 bg-white font-mono font-bold text-emerald-700">
                                                <input type="hidden" name="harga_grosir" :value="addForm.harga_grosir">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="px-3 py-2 bg-emerald-100 rounded-lg border border-emerald-200">
                                        <p class="text-[10px] text-emerald-700 font-semibold italic" x-text="`Artinya: Jika pembeli membeli ≥ ${addForm.min_qty_grosir || '?'} ${addForm.unit || 'unit'}, harga otomatis menjadi Rp ${formatNumber(addForm.harga_grosir)}/satuan (hemat Rp ${formatNumber(Math.max(0, addForm.harga_jual - addForm.harga_grosir))}/satuan)`"></p>
                                    </div>
                                </div>
                            </template>
                            <template x-if="!addForm.aktif_grosir">
                                <p class="text-[10px] text-slate-400 italic">Aktifkan untuk memberikan harga khusus saat pembelian dalam jumlah banyak.</p>
                            </template>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/50">
                        <button type="button" @click="addModalOpen = false"
                            class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors shadow-sm">Batal</button>
                        <button type="submit"
                            class="px-5 py-2 bg-[#0f172a] hover:bg-slate-800 rounded-lg text-sm font-bold text-white shadow transition-colors">Simpan Produk</button>
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
                        <h3 class="text-lg font-bold text-slate-800">Edit Produk</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Perbarui informasi produk</p>
                    </div>
                    <button @click="editModalOpen = false"
                        class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-lg hover:bg-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>

                @if(old('edit_id') && $errors->any())
                    <div class="px-6 pt-4">
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                            <p class="text-sm font-bold mb-1">Terdapat kesalahan pada form:</p>
                            <ul class="list-disc list-inside text-sm space-y-0.5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form :action="`{{ url('/products') }}/${editForm.id}`" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="edit_id" :value="editForm.id">
                    <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">SKU</label>
                                    <label class="flex items-center gap-1 text-[11px] text-slate-500 cursor-pointer">
                                        <input type="checkbox" x-model="editForm.skuAuto" :disabled="!editForm.kategori_id" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5">
                                        <span class="font-bold">Otomatis</span>
                                    </label>
                                </div>
                                <input type="text" name="sku" x-model="editForm.sku"
                                    :disabled="editForm.skuAuto"
                                    :class="editForm.skuAuto ? 'bg-slate-50 border-slate-200 text-slate-400 cursor-not-allowed font-medium' : 'bg-white border-slate-300 text-slate-800'"
                                    class="w-full border rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500">
                                @error('sku')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                <p class="text-[10px] text-slate-400 mt-1" x-show="!editForm.kategori_id">Pilih kategori terlebih dahulu untuk auto SKU</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Product Image</label>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg border-2 border-dashed border-slate-200 bg-slate-50 overflow-hidden flex items-center justify-center text-slate-400 group hover:border-blue-300 transition-colors cursor-pointer relative">
                                        <template x-if="!editForm.imagePreview && !editForm.image">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                        </template>
                                        <template x-if="!editForm.imagePreview && editForm.image">
                                            <img :src="`{{ asset('storage') }}/${editForm.image}`" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="editForm.imagePreview">
                                            <img :src="editForm.imagePreview" class="w-full h-full object-cover">
                                        </template>
                                        <input type="file" name="image" class="absolute inset-0 opacity-0 cursor-pointer" 
                                            @change="const file = $event.target.files[0]; if(file) { editForm.imagePreview = URL.createObjectURL(file); }">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[9px] text-slate-500 leading-tight">JPG, PNG. Max 2MB.</p>
                                        <button type="button" x-show="editForm.imagePreview || editForm.image" @click="editForm.imagePreview = null; editForm.image = null;" class="text-[9px] text-red-500 font-bold uppercase">Hapus</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Category</label>
                                <select name="kategori_id" x-model="editForm.kategori_id"
                                    @change="fetchSubCategories('edit', $event.target.value)"
                                    class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Sub Category</label>
                                <select name="sub_kategori_id" x-model="editForm.sub_kategori_id"
                                    :disabled="!editForm.kategori_id"
                                    :class="!editForm.kategori_id ? 'bg-slate-50 cursor-not-allowed opacity-60' : 'bg-white'"
                                    class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 transition-all">
                                    <template x-if="!editForm.kategori_id">
                                        <option value="">Silahkan pilih kategori dulu</option>
                                    </template>
                                    <template x-if="editForm.kategori_id">
                                        <option value="">Select Sub Category</option>
                                    </template>
                                    <template x-for="sub in editSubCategories" :key="sub.id">
                                        <option :value="sub.id" :selected="sub.id == editForm.sub_kategori_id" x-text="sub.nama"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1">
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

                        <!-- MULTIPLE UNITS SECTION -->
                        <div class="border-t border-slate-100 pt-4 mt-2">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-widest">Multi-Satuan & Harga</h4>
                                    <p class="text-[10px] text-slate-400 mt-0.5">Gunakan ini jika barang dijual dalam berbagai satuan (Contoh: Dus, Box, dll)</p>
                                    <div class="mt-1 flex items-center gap-1.5">
                                        <span class="flex w-2 h-2 rounded-full bg-blue-500"></span>
                                        <p class="text-[9px] text-blue-600 font-bold uppercase italic">PENTING: Centang "Utama" pada satuan dasar (misal: Pcs) untuk harga standar.</p>
                                    </div>
                                </div>
                                <button type="button" @click="addUnit('edit')" class="text-[10px] bg-blue-50 text-blue-600 px-2 py-1 rounded font-bold hover:bg-blue-100 transition-colors border border-blue-100 shadow-sm">
                                    + Tambah Satuan
                                </button>
                            </div>
                            
                            <div class="space-y-3">
                                <template x-for="(unit, index) in editForm.units" :key="index">
                                    <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm relative group hover:border-blue-300 transition-all">
                                        <button type="button" @click="removeUnit('edit', index)" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity z-10 shadow-lg">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                        <div class="grid grid-cols-2 sm:grid-cols-12 gap-3 items-end">
                                            <div class="col-span-2 sm:col-span-3">
                                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Pilih Satuan</label>
                                                <select :name="`units[${index}][nama]`" x-model="unit.nama"
                                                    @change="if(unit.is_base) { editForm.unit = $event.target.value; unit.target_satuan = $event.target.value; unit.target_isi = 1; unit.isi = 1; }"
                                                    class="w-full border-slate-200 rounded text-xs p-2 focus:ring-blue-500 bg-slate-50/50">
                                                    <option value="">Pilih Satuan...</option>
                                                    <template x-for="std in availableUnits" :key="std.id">
                                                        <option :value="std.nama" x-text="std.nama"></option>
                                                    </template>
                                                    <template x-if="unit.nama && !availableUnits.find(u => u.nama === unit.nama)">
                                                        <option :value="unit.nama" x-text="unit.nama" selected></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <div class="col-span-2 sm:col-span-1">
                                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Jml</label>
                                                <div class="relative">
                                                    <input type="text" value="1" readonly
                                                        class="w-full border-slate-200 rounded text-xs p-2 focus:ring-blue-500 bg-slate-100 font-bold text-center text-slate-500">
                                                </div>
                                            </div>
                                            <div class="col-span-2 sm:col-span-1 hidden sm:flex items-center justify-center pb-2">
                                                <span class="text-slate-400 font-black">=</span>
                                            </div>
                                            <div class="col-span-2 sm:col-span-2">
                                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Konversi Ke</label>
                                                <select :name="`units[${index}][target_satuan]`" x-model="unit.target_satuan"
                                                    :disabled="unit.is_base"
                                                    :class="unit.is_base ? 'w-full border-slate-200 rounded text-xs p-2 bg-slate-100 font-bold text-slate-500 opacity-70 cursor-not-allowed' : 'w-full border-slate-200 rounded text-xs p-2 focus:ring-blue-500 bg-slate-50/50'"
                                                    @change="if(!unit.is_base) { unit.isi = (Number(unit.target_isi) || 1) * ((editForm.units.find(u => u.nama === ($event.target.value || editForm.unit)) || {}).isi || 1); unit.harga_jual = Math.round((editForm.harga_beli * unit.isi) * (1 + editForm.margin / 100)) }">
                                                    <option value="" disabled>Pilih...</option>
                                                    <template x-for="u in editForm.units.filter(u => u.nama)" :key="u.nama">
                                                        <option :value="u.nama" x-text="u.nama"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <div class="col-span-2 sm:col-span-2">
                                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Isi</label>
                                                <input type="number" step="1" min="1" :name="`units[${index}][target_isi]`" x-model="unit.target_isi" 
                                                    :readonly="unit.is_base"
                                                    :class="unit.is_base ? 'w-full border-slate-200 rounded text-xs p-2 bg-slate-100 font-bold text-slate-500 opacity-70 cursor-not-allowed' : 'w-full border-slate-200 rounded text-xs p-2 focus:ring-blue-500 bg-slate-50/50 font-bold'"
                                                    @input="if(!unit.is_base) { unit.isi = (Number($event.target.value) || 1) * ((editForm.units.find(u => u.nama === (unit.target_satuan || editForm.unit)) || {}).isi || 1); unit.harga_jual = Math.round((editForm.harga_beli * unit.isi) * (1 + editForm.margin / 100)) }">
                                                <input type="hidden" :name="`units[${index}][isi]`" :value="unit.isi || 1">
                                            </div>
                                            <div class="col-span-2 sm:col-span-2">
                                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Harga (Rp)</label>
                                                <input type="text" :value="formatNumber(unit.harga_jual)" 
                                                    @input="unit.harga_jual = unformatNumber($event.target.value); if(unit.is_base) { editForm.harga_jual = unit.harga_jual; updatePrices('edit', 'jual') }"
                                                    class="w-full border-slate-200 rounded text-xs p-2 focus:ring-blue-500 font-mono bg-slate-50/50 font-bold text-blue-600">
                                                <input type="hidden" :name="`units[${index}][harga_jual]`" :value="unit.harga_jual">
                                            </div>
                                            <div class="col-span-2 sm:col-span-1 flex flex-row sm:flex-col items-center justify-between sm:justify-center pt-1 px-2 border-t border-dashed border-slate-100 sm:border-t-0 mt-2 sm:mt-0">
                                                <label class="text-[8px] font-black text-slate-400 uppercase mb-1 text-center leading-none">Utama?</label>
                                                <input type="checkbox" :name="`units[${index}][is_base]`" value="1" x-model="unit.is_base" 
                                                    @change="if($event.target.checked) { 
                                                        editForm.units.forEach((u, i) => { 
                                                            if(i !== index) u.is_base = false; 
                                                            else {
                                                                u.target_satuan = u.nama;
                                                                u.target_isi = 1;
                                                                u.isi = 1;
                                                            }
                                                        });
                                                        editForm.unit = unit.nama;
                                                        editForm.harga_jual = unit.harga_jual;
                                                    }"
                                                    class="rounded text-blue-600 focus:ring-blue-500 w-4 h-4 shadow-sm" title="Jadikan sebagai satuan harga utama">
                                            </div>
                                        </div>
                                        <!-- Helpful Summary -->
                                        <div class="mt-2 flex items-center gap-2">
                                            <div class="h-px flex-1 bg-slate-100"></div>
                                            <div class="px-2 py-0.5 bg-blue-50 rounded text-[9px] font-bold text-blue-700 italic border border-blue-100">
                                                Artinya: 1 <span x-text="unit.nama || '...'"></span> berisi <span x-text="Number(unit.target_isi) || '0'"></span> <span x-text="unit.target_satuan || editForm.unit || 'Unit Dasar'"></span>
                                            </div>
                                            <div class="h-px flex-1 bg-slate-100"></div>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!editForm.units || editForm.units.length === 0">
                                    <div class="text-center py-6 border-2 border-dashed border-slate-100 rounded-xl bg-slate-50/30">
                                        <svg class="w-8 h-8 text-slate-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                        <p class="text-[11px] text-slate-400 font-medium px-10">Belum ada pengaturan multi-satuan. Gunakan tombol di atas jika barang ini memiliki satuan jual grosir/box.</p>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Stock
                                    <span class="text-red-500">*</span></label>
                                <input type="number" name="stok" x-model="editForm.stok" @focus="$event.target.select()" required
                                    class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                <p class="text-[10px] text-slate-500 mt-1 italic">Input jumlah dalam <span class="font-bold text-slate-700" x-text="editForm.unit || 'satuan utama'"></span>.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Min
                                    Stock <span class="text-red-500">*</span></label>
                                <input type="number" name="min_stok" x-model="editForm.min_stok" @focus="$event.target.select()" required
                                    class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                            </div>
                        </div>

                        <!-- Harga Beli & Jual -->
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 mt-2">
                            <h4 class="text-[11px] font-black text-slate-600 uppercase tracking-widest mb-3 flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Pengaturan Harga Dasar
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="flex flex-col">
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1.5 h-7 flex items-end pb-1">Harga Beli (Modal per Satuan Utama) <span class="text-red-500 ml-1">*</span></label>
                                    <div class="relative flex-1">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">Rp</span>
                                        <input type="text" :value="formatNumber(editForm.harga_beli)"
                                            @input="editForm.harga_beli = unformatNumber($event.target.value); updatePrices('edit', 'beli')"
                                            @focus="$event.target.select()" required
                                            class="w-full pl-9 pr-3 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 bg-white font-mono font-bold">
                                        <input type="hidden" name="harga_beli" :value="editForm.harga_beli">
                                    </div>
                                </div>
                                <div class="flex flex-col">
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1.5 h-7 flex items-end pb-1">Target Margin (%)</label>
                                    <div class="relative flex-1">
                                        <input type="number" step="0.01" x-model="editForm.margin"
                                            @input="updatePrices('edit', 'margin')"
                                            @focus="$event.target.select()"
                                            class="w-full pr-8 pl-3 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 bg-white font-bold text-blue-600">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">%</span>
                                    </div>
                                </div>
                                <div class="flex flex-col">
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1.5 h-7 flex items-end pb-1">Harga Jual (Satuan Utama)</label>
                                    <div class="relative flex-1">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-blue-400">Rp</span>
                                        <input type="text" :value="formatNumber(editForm.harga_jual)"
                                            @input="editForm.harga_jual = unformatNumber($event.target.value); updatePrices('edit', 'jual')"
                                            @focus="$event.target.select()" required
                                            class="w-full pl-9 pr-3 py-2.5 border border-blue-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 bg-blue-50 font-mono font-bold text-blue-700">
                                        <input type="hidden" name="harga_jual" :value="editForm.harga_jual">
                                    </div>
                                </div>
                            </div>
                            <p class="text-[10px] text-slate-500 mt-3 italic">Catatan: Modal / Harga Beli ini akan diperbarui otomatis (Moving Average Cost) setiap ada Transaksi Pembelian baru.</p>
                        </div>

                        <!-- Hidden but synced fields -->
                        <input type="hidden" name="unit" :value="editForm.unit">
                        <input type="hidden" name="harga_jual" :value="editForm.harga_jual">

                        <!-- HARGA GROSIR SECTION (EDIT) -->
                        <div class="border border-emerald-200 bg-emerald-50/40 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <h4 class="text-[11px] font-black text-emerald-700 uppercase tracking-widest">Harga Grosir</h4>
                                </div>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <span class="text-[10px] font-bold text-slate-500">Aktifkan</span>
                                    <div class="relative">
                                        <input type="checkbox" name="aktif_grosir" value="1" x-model="editForm.aktif_grosir" class="sr-only peer">
                                        <div class="w-9 h-5 bg-slate-200 rounded-full peer peer-checked:bg-emerald-500 transition-colors"></div>
                                        <div class="absolute top-0.5 left-0.5 bg-white w-4 h-4 rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                                    </div>
                                </label>
                            </div>
                            <template x-if="editForm.aktif_grosir">
                                <div class="space-y-3">
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Min. Qty Grosir <span class="text-red-500">*</span></label>
                                            <div class="relative">
                                                <input type="number" name="min_qty_grosir" x-model="editForm.min_qty_grosir" min="2" @focus="$event.target.select()"
                                                    class="w-full border border-slate-300 rounded-lg text-sm p-2.5 pr-14 focus:ring-emerald-500 focus:border-emerald-500 bg-white font-bold">
                                                <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[9px] text-slate-400 font-bold" x-text="editForm.unit || 'unit'"></span>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Harga Grosir / Satuan <span class="text-red-500">*</span></label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-emerald-500">Rp</span>
                                                <input type="text" :value="formatNumber(editForm.harga_grosir)"
                                                    @input="editForm.harga_grosir = unformatNumber($event.target.value)" @focus="$event.target.select()"
                                                    class="w-full pl-9 pr-3 py-2.5 border border-emerald-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500 bg-white font-mono font-bold text-emerald-700">
                                                <input type="hidden" name="harga_grosir" :value="editForm.harga_grosir">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="px-3 py-2 bg-emerald-100 rounded-lg border border-emerald-200">
                                        <p class="text-[10px] text-emerald-700 font-semibold italic" x-text="`Artinya: Jika pembeli membeli ≥ ${editForm.min_qty_grosir || '?'} ${editForm.unit || 'unit'}, harga otomatis menjadi Rp ${formatNumber(editForm.harga_grosir)}/satuan (hemat Rp ${formatNumber(Math.max(0, editForm.harga_jual - editForm.harga_grosir))}/satuan)`"></p>
                                    </div>
                                </div>
                            </template>
                            <template x-if="!editForm.aktif_grosir">
                                <p class="text-[10px] text-slate-400 italic">Aktifkan untuk memberikan harga khusus saat pembelian dalam jumlah banyak.</p>
                            </template>
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
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md relative z-10 m-4 overflow-hidden transform transition-all"
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

        <!-- BARCODE PRINT MODAL -->
        <div x-show="barcodePrintModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center" style="display: none;">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="barcodePrintModalOpen = false"
                x-transition.opacity></div>
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl relative z-10 m-4 overflow-hidden transform transition-all"
                x-transition>
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Cetak Barcode Terpilih</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Konfigurasi jumlah cetak barcode stiker A4 (3 Kolom x 10 Baris)</p>
                    </div>
                    <button @click="barcodePrintModalOpen = false"
                        class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-lg hover:bg-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
                <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto">

                    <!-- Products List Table -->
                    <div class="border border-slate-200 rounded-lg overflow-x-auto">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                                <tr>
                                    <th class="py-2.5 px-4 font-semibold text-xs uppercase">Nama Produk</th>
                                    <th class="py-2.5 px-4 font-semibold text-xs uppercase">SKU</th>
                                    <th class="py-2.5 px-4 font-semibold text-xs uppercase w-32">Jumlah Cetak</th>
                                    <th class="py-2.5 px-4 font-semibold text-xs uppercase text-right w-12">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="(prod, index) in printProducts" :key="prod.id">
                                    <tr class="hover:bg-slate-50">
                                        <td class="py-3 px-4">
                                            <div class="font-medium text-slate-800" x-text="prod.nama"></div>
                                        </td>
                                        <td class="py-3 px-4 text-xs font-mono text-slate-600">
                                            <template x-if="prod.sku === '[Otomatis]'">
                                                <span class="text-red-500 font-bold text-[10px] bg-red-50 px-2 py-0.5 rounded border border-red-100 block w-max">Belum Ada SKU</span>
                                            </template>
                                            <template x-if="prod.sku !== '[Otomatis]'">
                                                <span x-text="prod.sku"></span>
                                            </template>
                                        </td>
                                        <td class="py-3 px-4">
                                            <input type="number" min="1" x-model="prod.qty" class="w-full border border-slate-300 rounded px-2 py-1 text-xs text-center font-bold">
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <button type="button" @click="removePrintProduct(index)" class="text-red-500 hover:text-red-700 p-1 rounded hover:bg-red-50">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <button type="button" @click="selectedProducts = []; barcodePrintModalOpen = false" class="text-xs text-red-600 hover:text-red-800 font-bold uppercase">Kosongkan Pilihan</button>
                    <div class="flex gap-3">
                        <button type="button" @click="barcodePrintModalOpen = false"
                            class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors shadow-sm">Cancel</button>
                        <button type="button" @click="printBarcodes()"
                            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg text-sm font-bold text-white shadow transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Cetak Barcode
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- HIDDEN PRINT AREA -->
        <div id="print-barcode-area"></div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    @endpush

    <style>
        @page {
            margin: 0;
        }
        @media print {
            /* Hide everything on page */
            body * {
                visibility: hidden;
            }
            /* Show print container and its descendants */
            #print-barcode-area, #print-barcode-area * {
                visibility: visible;
            }
            #print-barcode-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                display: block !important;
                background: white;
            }

            /* A4 Grid: 3 Columns x 10 Rows layout */
            .print-page.layout-a4 {
                width: 210mm;
                height: 297mm;
                padding: 8mm 9mm 12mm 9mm;
                box-sizing: border-box;
                display: grid !important;
                grid-template-columns: repeat(3, 62mm);
                grid-template-rows: repeat(10, 25mm);
                grid-gap: 3mm;
                background: white;
                page-break-after: always;
                break-after: page;
            }
            .print-page.layout-a4:last-child {
                page-break-after: avoid;
                break-after: avoid;
            }
            .print-page.layout-a4 .barcode-label {
                width: 62mm;
                height: 25mm;
                border: 1px dashed #bbb;
                box-sizing: border-box;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                padding: 1.5mm;
                overflow: hidden;
                background: white;
            }
            .print-page.layout-a4 .label-title {
                font-size: 7px;
                font-weight: bold;
                text-align: center;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                width: 100%;
                margin-bottom: 0.5mm;
                text-transform: uppercase;
                font-family: sans-serif;
                color: #000;
            }
            .print-page.layout-a4 .barcode-svg {
                max-width: 100%;
                max-height: 16mm;
                display: block;
            }
        }
        @media screen {
            #print-barcode-area {
                display: none;
            }
        }
    </style>
@endsection
