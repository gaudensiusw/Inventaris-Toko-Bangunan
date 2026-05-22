@extends('layouts.app')

@section('title', 'Manajemen Kategori')
@section('header_title', 'Kategori & Sub-Kategori')

@section('content')
<div x-data="{ 
    addCatModal: false, 
    editCatModal: false, 
    addSubModal: false, 
    editSubModal: false,
    deleteModalOpen: false,
    deleteForm: {
        title: '',
        url: '',
        message: ''
    },
    confirmAddSub: false,
    confirmEditSub: false,
    confirmAddCat: false,
    confirmEditCat: false,
    selectedCat: {},
    selectedSub: {}
}" @keydown.escape.window="addCatModal = false; editCatModal = false; addSubModal = false; editSubModal = false; deleteModalOpen = false">
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Sidebar / Category List -->
        <div class="xl:col-span-1 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden flex flex-col h-fit">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="font-bold text-slate-800">Daftar Kategori</h3>
                <button @click="addCatModal = true" class="p-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
            </div>
            <div class="divide-y divide-slate-50 overflow-y-auto max-h-[calc(100vh-250px)]">
                @forelse($categories as $category)
                <div class="group flex items-center justify-between p-4 hover:bg-blue-50/50 transition-colors cursor-pointer {{ request('id') == $category->id ? 'bg-blue-50 border-r-4 border-blue-600' : '' }}"
                     @click="window.location.href = '{{ route('category.index', ['id' => $category->id]) }}'">
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-sm text-slate-800 truncate">{{ $category->nama }}</p>
                        <p class="text-[11px] text-slate-500">{{ $category->sub_categories_count ?? $category->subCategories->count() }} Sub-Kategori</p>
                    </div>
                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button @click.stop='selectedCat = @json($category); editCatModal = true' class="p-1 text-blue-600 hover:bg-blue-100 rounded">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </button>
                        <button @click.stop="deleteModalOpen = true; deleteForm = { title: 'Hapus Kategori', message: 'Apakah Anda yakin ingin menghapus kategori \'' + '{{ $category->nama }}' + '\'? Semua sub-kategori terkait juga akan terhapus.', url: '{{ route('category.destroy', $category->id) }}' }" 
                                class="p-1 text-red-500 hover:bg-red-100 rounded">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-slate-400">
                    <p class="text-sm">Belum ada kategori</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Sub-Category Detail -->
        <div class="xl:col-span-2 space-y-6">
            @php
                $activeCat = $categories->find(request('id')) ?? $categories->first();
            @endphp

            @if($activeCat)
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <h3 class="font-bold text-lg text-slate-800">Sub-Kategori: {{ $activeCat->nama }}</h3>
                        <p class="text-xs text-slate-500">Kelola sub-kategori untuk kategori ini</p>
                    </div>
                    <button @click="addSubModal = true" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2 transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Tambah Sub
                    </button>
                </div>
                <div class="p-0 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-500 border-b border-slate-100">
                            <tr>
                                <th class="py-3 px-5 font-semibold">Nama Sub-Kategori</th>
                                <th class="py-3 px-5 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($activeCat->subCategories as $sub)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 px-5 font-medium text-slate-700">{{ $sub->nama }}</td>
                                <td class="py-4 px-5 text-right space-x-1 whitespace-nowrap">
                                    <button @click='selectedSub = @json($sub); editSubModal = true' class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg inline-block">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button @click="deleteModalOpen = true; deleteForm = { title: 'Hapus Sub-Kategori', message: 'Apakah Anda yakin ingin menghapus sub-kategori \'' + '{{ $sub->nama }}' + '\'?', url: '{{ route('sub-category.destroy', $sub->id) }}' }" 
                                            class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="py-12 text-center text-slate-400">
                                    <p class="text-sm">Belum ada sub-kategori untuk kategori ini</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="bg-white border border-slate-200 border-dashed rounded-xl p-12 text-center text-slate-400">
                <svg class="w-16 h-16 mx-auto mb-4 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <p class="font-medium">Silakan pilih kategori di sebelah kiri atau tambah kategori baru</p>
            </div>
            @endif
        </div>
    </div>

    <!-- MODALS -->
    <!-- Add Category -->
    <div x-show="addCatModal" class="fixed inset-0 z-[100] flex items-center justify-center" style="display: none;">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="addCatModal = false"></div>
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md relative z-10 m-4 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <h3 class="font-bold text-slate-800">Tambah Kategori Baru</h3>
                <button @click="addCatModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form action="{{ route('category.store') }}" method="POST" class="p-6">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Nama Kategori</label>
                                        <input type="text" name="nama" required class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm">

                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="addCatModal = false" class="px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50 rounded-lg">Batal</button>
                    <button type="button" @click="confirmAddCat = true" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold shadow-sm hover:bg-blue-700 transition-colors">Simpan</button>
                </div>

                <!-- DOUBLE CONFIRMATION MODAL (ADD CAT) -->
                <div x-show="confirmAddCat" class="fixed inset-0 z-[110] flex items-center justify-center" style="display: none;">
                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="confirmAddCat = false"></div>
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm relative z-10 m-4 overflow-hidden border border-slate-200">
                        <div class="p-8 text-center">
                            <div class="w-20 h-20 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-6 ring-8 ring-blue-50/50">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-2">Simpan Kategori Baru?</h3>
                            <p class="text-slate-500 leading-relaxed">Anda akan menambahkan kategori utama baru ke dalam sistem.</p>
                        </div>
                        <div class="px-6 py-5 bg-slate-50 border-t border-slate-100 flex gap-3">
                            <button type="button" @click="confirmAddCat = false" class="flex-1 py-3 bg-white border border-slate-300 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all shadow-sm">Batal</button>
                            <button type="submit" class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm font-bold text-white transition-all shadow-lg shadow-blue-600/20">Ya, Simpan</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Category -->
    <div x-show="editCatModal" class="fixed inset-0 z-[100] flex items-center justify-center" style="display: none;">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="editCatModal = false"></div>
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md relative z-10 m-4 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <h3 class="font-bold text-slate-800">Edit Kategori</h3>
                <button @click="editCatModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form :action="`{{ url('/categories') }}/${selectedCat.id}`" method="POST" class="p-6">
                @csrf @method('PUT')
                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Nama Kategori</label>
                    <input type="text" name="nama" x-model="selectedCat.nama" required class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="editCatModal = false" class="px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50 rounded-lg">Batal</button>
                    <button type="button" @click="confirmEditCat = true" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold shadow-sm hover:bg-blue-700 transition-colors">Perbarui</button>
                </div>

                <!-- DOUBLE CONFIRMATION MODAL (EDIT CAT) -->
                <div x-show="confirmEditCat" class="fixed inset-0 z-[110] flex items-center justify-center" style="display: none;">
                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="confirmEditCat = false"></div>
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm relative z-10 m-4 overflow-hidden border border-slate-200">
                        <div class="p-8 text-center">
                            <div class="w-20 h-20 bg-orange-50 text-orange-600 rounded-full flex items-center justify-center mx-auto mb-6 ring-8 ring-orange-50/50">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-2">Perbarui Kategori?</h3>
                            <p class="text-slate-500 leading-relaxed">Perubahan pada kategori utama akan berdampak pada seluruh sub-kategori di dalamnya.</p>
                        </div>
                        <div class="px-6 py-5 bg-slate-50 border-t border-slate-100 flex gap-3">
                            <button type="button" @click="confirmEditCat = false" class="flex-1 py-3 bg-white border border-slate-300 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all shadow-sm">Batal</button>
                            <button type="submit" class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm font-bold text-white transition-all shadow-lg shadow-blue-600/20">Ya, Perbarui</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Sub-Category -->
    <div x-show="addSubModal" class="fixed inset-0 z-[100] flex items-center justify-center" style="display: none;">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="addSubModal = false"></div>
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md relative z-10 m-4 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <h3 class="font-bold text-slate-800">Tambah Sub-Kategori</h3>
                <button @click="addSubModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form action="{{ route('sub-category.store') }}" method="POST" class="p-6">
                @csrf
                <input type="hidden" name="kategori_id" value="{{ $activeCat->id ?? '' }}">
                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Nama Sub-Kategori</label>
                                        <input type="text" name="nama" required class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm">

                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="addSubModal = false" class="px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50 rounded-lg">Batal</button>
                    <button type="button" @click="confirmAddSub = true" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold shadow-sm hover:bg-blue-700 transition-colors">Simpan</button>
                </div>

                <!-- DOUBLE CONFIRMATION MODAL (ADD SUB) -->
                <div x-show="confirmAddSub" class="fixed inset-0 z-[110] flex items-center justify-center" style="display: none;">
                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="confirmAddSub = false"></div>
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm relative z-10 m-4 overflow-hidden border border-slate-200">
                        <div class="p-8 text-center">
                            <div class="w-20 h-20 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-6 ring-8 ring-blue-50/50">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-2">Simpan Sub-Kategori?</h3>
                            <p class="text-slate-500 leading-relaxed">Anda akan menambahkan sub-kategori baru ke dalam sistem.</p>
                        </div>
                        <div class="px-6 py-5 bg-slate-50 border-t border-slate-100 flex gap-3">
                            <button type="button" @click="confirmAddSub = false" class="flex-1 py-3 bg-white border border-slate-300 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all shadow-sm">Batal</button>
                            <button type="submit" class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm font-bold text-white transition-all shadow-lg shadow-blue-600/20">Ya, Simpan</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Sub-Category -->
    <div x-show="editSubModal" class="fixed inset-0 z-[100] flex items-center justify-center" style="display: none;">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="editSubModal = false"></div>
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md relative z-10 m-4 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <h3 class="font-bold text-slate-800">Edit Sub-Kategori</h3>
                <button @click="editSubModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form :action="`{{ url('/sub-categories') }}/${selectedSub.id}`" method="POST" class="p-6">
                @csrf @method('PUT')
                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Nama Sub-Kategori</label>
                    <input type="text" name="nama" x-model="selectedSub.nama" required class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="editSubModal = false" class="px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50 rounded-lg">Batal</button>
                    <button type="button" @click="confirmEditSub = true" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold shadow-sm hover:bg-blue-700 transition-colors">Perbarui</button>
                </div>

                <!-- DOUBLE CONFIRMATION MODAL (EDIT SUB) -->
                <div x-show="confirmEditSub" class="fixed inset-0 z-[110] flex items-center justify-center" style="display: none;">
                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="confirmEditSub = false"></div>
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm relative z-10 m-4 overflow-hidden border border-slate-200">
                        <div class="p-8 text-center">
                            <div class="w-20 h-20 bg-orange-50 text-orange-600 rounded-full flex items-center justify-center mx-auto mb-6 ring-8 ring-orange-50/50">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-2">Perbarui Sub-Kategori?</h3>
                            <p class="text-slate-500 leading-relaxed">Perubahan nama sub-kategori akan berdampak pada seluruh produk terkait.</p>
                        </div>
                        <div class="px-6 py-5 bg-slate-50 border-t border-slate-100 flex gap-3">
                            <button type="button" @click="confirmEditSub = false" class="flex-1 py-3 bg-white border border-slate-300 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all shadow-sm">Batal</button>
                            <button type="submit" class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm font-bold text-white transition-all shadow-lg shadow-blue-600/20">Ya, Perbarui</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Global Deletion Confirmation Modal -->
    <div x-show="deleteModalOpen" class="fixed inset-0 z-[110] flex items-center justify-center" style="display: none;" x-cloak>
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="deleteModalOpen = false" x-transition.opacity></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm relative z-10 m-4 overflow-hidden transform transition-all" x-transition.scale.95>
            <div class="p-6 text-center">
                <div class="w-16 h-16 bg-red-50 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-2" x-text="deleteForm.title">Konfirmasi Hapus</h3>
                <p class="text-sm text-slate-500 mb-6" x-text="deleteForm.message">Apakah Anda yakin ingin menghapus data ini?</p>
                
                <form :action="deleteForm.url" method="POST">
                    @csrf @method('DELETE')
                    <div class="flex gap-3 mt-6">
                        <button type="button" @click="deleteModalOpen = false" class="flex-1 px-4 py-2.5 border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-colors">Batal</button>
                        <button type="submit" class="flex-1 px-4 py-2.5 bg-red-600 text-white font-bold rounded-xl hover:bg-red-700 transition-colors shadow-lg shadow-red-200">Hapus Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
