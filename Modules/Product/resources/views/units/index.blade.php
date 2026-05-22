@extends('layouts.app')

@section('title', 'Master Satuan')
@section('header_title', 'Master Satuan')

@section('content')
<div x-data="{ 
    addModal: false, 
    editModal: false, 
    deleteModalOpen: false,
    deleteForm: { title: '', url: '', message: '' },
    confirmAdd: false,
    confirmEdit: false,
    selectedUnit: {} 
}" @keydown.escape.window="addModal = false; editModal = false; deleteModalOpen = false">
    
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Sidebar: Unit List Grouped by Category -->
        <div class="xl:col-span-1 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden flex flex-col h-fit">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Daftar Satuan</h3>
                    <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Reference Units</p>
                </div>
                <button @click="addModal = true" class="p-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
            </div>
            <div class="overflow-y-auto max-h-[calc(100vh-250px)]">
                @forelse($groupedUnits as $category => $items)
                <div class="bg-slate-50/50 px-4 py-2 border-y border-slate-100">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $category ?: 'Lainnya' }}</span>
                </div>
                <div class="divide-y divide-slate-50">
                    @foreach($items as $unit)
                    <div class="group flex items-center justify-between p-4 hover:bg-blue-50/50 transition-colors cursor-pointer {{ ($activeUnit && $activeUnit->id == $unit->id) ? 'bg-blue-50 border-r-4 border-blue-600' : '' }}"
                         @click="window.location.href = '{{ route('unit.index', ['id' => $unit->id]) }}'">
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-sm text-slate-800 truncate">{{ $unit->nama }}</p>
                            <p class="text-[10px] text-slate-500 font-mono uppercase tracking-tighter">{{ $unit->simbol ?? '-' }}</p>
                        </div>
                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button @click.stop='selectedUnit = @json($unit); editModal = true' class="p-1 text-blue-600 hover:bg-blue-100 rounded">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </button>
                            <button @click.stop="deleteModalOpen = true; deleteForm = { title: 'Hapus Satuan', message: 'Apakah Anda yakin ingin menghapus satuan \'' + '{{ $unit->nama }}' + '\'?', url: '{{ route('unit.destroy', $unit->id) }}' }" 
                                    class="p-1 text-red-500 hover:bg-red-100 rounded">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
                @empty
                <div class="p-8 text-center text-slate-400">
                    <p class="text-sm italic">Belum ada data satuan</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Detail Panel -->
        <div class="xl:col-span-2 space-y-6">
            @if($activeUnit)
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden h-fit">
                <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center gap-5">
                    <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-sm border border-blue-200">
                        <span class="text-xl font-black uppercase" x-text="'{{ substr($activeUnit->nama, 0, 2) }}'"></span>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-0.5">
                            <h3 class="text-xl font-bold text-slate-800">{{ $activeUnit->nama }}</h3>
                            @if($activeUnit->kategori)
                            <span class="text-[9px] bg-blue-600 text-white px-2 py-0.5 rounded-full font-black uppercase tracking-widest shadow-sm">{{ $activeUnit->kategori }}</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500">Simbol: <span class="font-mono bg-slate-100 px-1.5 py-0.5 rounded text-slate-700">{{ $activeUnit->simbol ?? '-' }}</span></p>
                    </div>
                </div>
                
                <div class="p-6">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Produk Terkait (Terbaru)</h4>
                    <div class="overflow-x-auto border border-slate-100 rounded-xl">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-slate-500">
                                <tr>
                                    <th class="py-3 px-5 font-semibold text-xs uppercase">Nama Produk</th>
                                    <th class="py-3 px-5 font-semibold text-xs uppercase">Brand</th>
                                    <th class="py-3 px-5 font-semibold text-xs uppercase text-right">Stok Saat Ini</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse($productsUsing as $p)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-3 px-5">
                                        <div class="font-bold text-slate-700">{{ $p->nama }}</div>
                                        <div class="text-[10px] text-slate-400">SKU: {{ $p->sku ?? '-' }}</div>
                                    </td>
                                    <td class="py-3 px-5">
                                        <span class="text-[10px] bg-slate-100 px-2 py-0.5 rounded font-bold uppercase">{{ $p->merk ?? 'N/A' }}</span>
                                    </td>
                                    <td class="py-3 px-5 text-right font-bold text-blue-600">
                                        {{ number_format($p->stok) }} {{ $p->unit }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="py-10 text-center text-slate-300 italic">
                                        Belum ada produk yang menggunakan satuan ini.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($productsUsing->count() >= 10)
                        <p class="mt-4 text-[10px] text-slate-400 italic text-center">* Menampilkan 10 produk terbaru</p>
                    @endif
                </div>
            </div>
            @else
            <div class="bg-white border border-slate-200 border-dashed rounded-xl p-12 text-center text-slate-400">
                <svg class="w-16 h-16 mx-auto mb-4 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                <p class="font-medium">Pilih satuan di sebelah kiri untuk melihat detail penggunaan</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Modals -->
    <!-- Add Modal -->
    <div x-show="addModal" class="fixed inset-0 z-[100] flex items-center justify-center" style="display: none;" x-cloak x-transition.opacity>
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="addModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 m-4 overflow-hidden transform transition-all" x-transition.scale.95>
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <h3 class="font-bold text-slate-800">Tambah Satuan Baru</h3>
                <button @click="addModal = false" class="text-slate-400 hover:text-slate-600 text-2xl font-light">&times;</button>
            </div>
            <form action="{{ route('unit.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Kategori Satuan</label>
                    <input list="addKategoriList" name="kategori" placeholder="Pilih atau ketik kategori baru" required
                        class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white shadow-sm transition-all">
                    <datalist id="addKategoriList">
                        <option value="Satuan Berat/Volume">
                        <option value="Satuan Ukuran">
                        <option value="Satuan Kemasan">
                        <option value="Satuan Unit/Eceran">
                    </datalist>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Nama Satuan</label>
                    <input type="text" name="nama" required placeholder="Contoh: Karton, Box, Pcs"
                        class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white shadow-sm transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Simbol / Singkatan</label>
                    <input type="text" name="simbol" placeholder="Contoh: kt, bx, pcs"
                        class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white shadow-sm transition-all">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="addModal = false" class="px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50 rounded-lg">Batal</button>
                    <button type="button" @click="confirmAdd = true" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold shadow-md hover:bg-blue-700 transition-colors">Simpan Satuan</button>
                </div>

                <!-- DOUBLE CONFIRMATION MODAL (ADD) -->
                <div x-show="confirmAdd" class="fixed inset-0 z-[110] flex items-center justify-center" style="display: none;">
                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="confirmAdd = false"></div>
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm relative z-10 m-4 overflow-hidden border border-slate-200">
                        <div class="p-8 text-center">
                            <div class="w-20 h-20 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-6 ring-8 ring-blue-50/50">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-2">Simpan Satuan?</h3>
                            <p class="text-slate-500 leading-relaxed">Anda akan menambahkan satuan baru ke database master data.</p>
                        </div>
                        <div class="px-6 py-5 bg-slate-50 border-t border-slate-100 flex gap-3">
                            <button type="button" @click="confirmAdd = false" class="flex-1 py-3 bg-white border border-slate-300 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all shadow-sm">Batal</button>
                            <button type="submit" class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm font-bold text-white transition-all shadow-lg shadow-blue-600/20">Ya, Simpan</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div x-show="editModal" class="fixed inset-0 z-[100] flex items-center justify-center" style="display: none;" x-cloak x-transition.opacity>
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="editModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 m-4 overflow-hidden transform transition-all" x-transition.scale.95>
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <h3 class="font-bold text-slate-800">Edit Satuan</h3>
                <button @click="editModal = false" class="text-slate-400 hover:text-slate-600 text-2xl font-light">&times;</button>
            </div>
            <form :action="`{{ url('/units') }}/${selectedUnit.id}`" method="POST" class="p-6 space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Kategori Satuan</label>
                    <input list="editKategoriList" name="kategori" x-model="selectedUnit.kategori" placeholder="Pilih atau ketik kategori baru" required
                        class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white shadow-sm transition-all">
                    <datalist id="editKategoriList">
                        <option value="Satuan Berat/Volume">
                        <option value="Satuan Ukuran">
                        <option value="Satuan Kemasan">
                        <option value="Satuan Unit/Eceran">
                    </datalist>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Nama Satuan</label>
                    <input type="text" name="nama" x-model="selectedUnit.nama" required
                        class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white shadow-sm transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Simbol / Singkatan</label>
                    <input type="text" name="simbol" x-model="selectedUnit.simbol"
                        class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-white shadow-sm transition-all">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="editModal = false" class="px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50 rounded-lg">Batal</button>
                    <button type="button" @click="confirmEdit = true" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold shadow-md hover:bg-blue-700 transition-colors">Perbarui</button>
                </div>

                <!-- DOUBLE CONFIRMATION MODAL (EDIT) -->
                <div x-show="confirmEdit" class="fixed inset-0 z-[110] flex items-center justify-center" style="display: none;">
                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="confirmEdit = false"></div>
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm relative z-10 m-4 overflow-hidden border border-slate-200">
                        <div class="p-8 text-center">
                            <div class="w-20 h-20 bg-orange-50 text-orange-600 rounded-full flex items-center justify-center mx-auto mb-6 ring-8 ring-orange-50/50">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-2">Perbarui Satuan?</h3>
                            <p class="text-slate-500 leading-relaxed">Perubahan pada satuan dapat mempengaruhi tampilan data pada seluruh produk terkait.</p>
                        </div>
                        <div class="px-6 py-5 bg-slate-50 border-t border-slate-100 flex gap-3">
                            <button type="button" @click="confirmEdit = false" class="flex-1 py-3 bg-white border border-slate-300 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all shadow-sm">Batal</button>
                            <button type="submit" class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm font-bold text-white transition-all shadow-lg shadow-blue-600/20">Ya, Perbarui</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Deletion Confirmation Modal -->
    <div x-show="deleteModalOpen" class="fixed inset-0 z-[110] flex items-center justify-center" style="display: none;" x-cloak x-transition.opacity>
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="deleteModalOpen = false"></div>
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
