<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\Product\Models\Product;
use Modules\Product\Models\Category;
use Modules\Product\Models\SubCategory;
use Modules\Product\Models\Satuan;
use Modules\Supplier\Models\Supplier;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['supplier', 'category', 'subCategory', 'units']);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('kategori_id', $request->get('category'));
        }

        if ($request->filled('supplier')) {
            $query->where('supplier_id', $request->get('supplier'));
        }

        $totalProducts  = Cache::remember('products_total_count', 30, fn() => Product::count());
        $lowStockCount  = Cache::remember('products_low_stock_count', 30, fn() => Product::whereRaw('stok <= min_stok')->where('stok', '>', 0)->count());
        $outOfStockCount = Cache::remember('products_out_of_stock_count', 30, fn() => Product::where('stok', '<=', 0)->count());

        $perPage  = $request->get('per_page', 15);
        $products = $query->latest()->paginate($perPage)->withQueryString();
        
        $suppliers = Cache::remember('suppliers_all', 30, fn() => Supplier::all());
        $categories = Cache::remember('categories_all', 30, fn() => Category::all());
        $subCategories = Cache::remember('sub_categories_all', 30, fn() => SubCategory::all());
        $availableUnits = Cache::remember('satuan_all', 30, fn() => Satuan::all());

        return view('product::index', compact(
            'products',
            'suppliers',
            'categories',
            'subCategories',
            'availableUnits',
            'totalProducts',
            'lowStockCount',
            'outOfStockCount'
        ));
    }

    public function store(Request $request)
    {
        // Filter empty units
        if ($request->has('units') && is_array($request->units)) {
            $filteredUnits = array_filter($request->units, function($u) {
                return !empty($u['nama']);
            });
            $request->merge(['units' => array_values($filteredUnits)]);
        }

        $validated = $request->validate([
            'nama'            => 'required|string|max:255',
            'merk'            => 'nullable|string|max:255',
            'sku'             => 'nullable|string|unique:produk,sku',
            'kategori_id'     => 'nullable|exists:kategori,id',
            'sub_kategori_id' => 'nullable|exists:sub_kategori,id',
            'supplier_id'     => 'nullable|exists:supplier,id',
            'stok'            => 'required|numeric|min:0',
            'unit'            => 'required|string',
            'harga_beli'      => 'required|numeric|min:0',
            'harga_jual'      => 'required|numeric|min:0',
            'min_stok'        => 'required|numeric|min:0',
            'aktif_grosir'    => 'nullable|boolean',
            'min_qty_grosir'  => 'nullable|integer|min:1',
            'harga_grosir'    => 'nullable|numeric|min:0',
            'units'           => 'nullable|array',
            'units.*.nama'    => 'required|string',
            'units.*.isi'     => 'required|numeric|min:0.01',
            'units.*.harga_jual' => 'required|numeric|min:0',
            'units.*.is_base' => 'nullable',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle checkbox (tidak terkirim jika unchecked)
        $validated['aktif_grosir'] = $request->boolean('aktif_grosir');
        if (!$validated['aktif_grosir']) {
            $validated['min_qty_grosir'] = null;
            $validated['harga_grosir'] = null;
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = $path;
        }

        if (empty($validated['sku']) || $validated['sku'] === '[Otomatis]') {
            $kategoriId = $request->kategori_id;
            $prefix = $kategoriId ? (string)$kategoriId : '99';
            
            $counter = 1;
            do {
                $lastSku = Product::where('sku', 'LIKE', $prefix . '%')
                    ->orderByRaw('CAST(sku AS UNSIGNED) DESC')
                    ->value('sku');

                if ($lastSku) {
                    $prefixLength = strlen($prefix);
                    $serial = (int)substr($lastSku, $prefixLength);
                    $newSerial = str_pad($serial + $counter, 5, '0', STR_PAD_LEFT);
                } else {
                    $newSerial = str_pad($counter, 5, '0', STR_PAD_LEFT);
                }
                $potentialSku = $prefix . $newSerial;
                $counter++;
            } while (Product::where('sku', $potentialSku)->exists());
            
            $validated['sku'] = $potentialSku;
        }

        $product = Product::create($validated);

        if ($request->filled('units')) {
            foreach ($request->units as $u) {
                $isBase = isset($u['is_base']) && ($u['is_base'] == '1' || $u['is_base'] === true);
                
                $product->units()->create([
                    'nama' => $u['nama'],
                    'isi'  => $u['isi'],
                    'harga_jual' => $u['harga_jual'],
                    'is_base' => $isBase
                ]);

                // Sync back to product if it's the base unit
                if ($isBase) {
                    $product->update([
                        'unit' => $u['nama'],
                        'harga_jual' => $u['harga_jual']
                    ]);
                }
            }
        }

        // Log aktivitas CREATED
        if (function_exists('activity')) {
            activity('DataBarang')
                ->performedOn($product)
                ->causedBy(auth()->user())
                ->event('created')
                ->withProperties(['attributes' => $product->only(['nama', 'merk', 'sku', 'stok', 'unit', 'harga_beli', 'harga_jual', 'min_stok'])])
                ->log("Produk baru '{$product->nama}' (SKU: {$product->sku}) berhasil ditambahkan.");
        }

        return redirect()->back()->with('success', 'Produk berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        // Filter empty units
        if ($request->has('units') && is_array($request->units)) {
            $filteredUnits = array_filter($request->units, function($u) {
                return !empty($u['nama']);
            });
            $request->merge(['units' => array_values($filteredUnits)]);
        }

        $validated = $request->validate([
            'nama'            => 'required|string|max:255',
            'merk'            => 'nullable|string|max:255',
            'sku'             => 'nullable|string|unique:produk,sku,' . $product->id,
            'kategori_id'     => 'nullable|exists:kategori,id',
            'sub_kategori_id' => 'nullable|exists:sub_kategori,id',
            'supplier_id'     => 'nullable|exists:supplier,id',
            'stok'            => 'required|numeric|min:0',
            'unit'            => 'required|string',
            'harga_beli'      => 'required|numeric|min:0',
            'harga_jual'      => 'required|numeric|min:0',
            'min_stok'        => 'required|numeric|min:0',
            'aktif_grosir'    => 'nullable|boolean',
            'min_qty_grosir'  => 'nullable|integer|min:1',
            'harga_grosir'    => 'nullable|numeric|min:0',
            'units'           => 'nullable|array',
            'units.*.nama'    => 'required|string',
            'units.*.isi'     => 'required|numeric|min:0.01',
            'units.*.harga_jual' => 'required|numeric|min:0',
            'units.*.is_base' => 'nullable',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle checkbox (tidak terkirim jika unchecked)
        $validated['aktif_grosir'] = $request->boolean('aktif_grosir');
        if (!$validated['aktif_grosir']) {
            $validated['min_qty_grosir'] = null;
            $validated['harga_grosir'] = null;
        }

        if ($request->hasFile('image')) {
            if ($product->image && \Storage::disk('public')->exists($product->image)) {
                \Storage::disk('public')->delete($product->image);
            }
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = $path;
        }

        if (empty($validated['sku']) || $validated['sku'] === '[Otomatis]') {
            $kategoriId = $request->kategori_id;
            $prefix = $kategoriId ? (string)$kategoriId : '99';
            
            $counter = 1;
            do {
                $lastSku = Product::where('sku', 'LIKE', $prefix . '%')
                    ->orderByRaw('CAST(sku AS UNSIGNED) DESC')
                    ->value('sku');

                if ($lastSku) {
                    $prefixLength = strlen($prefix);
                    $serial = (int)substr($lastSku, $prefixLength);
                    $newSerial = str_pad($serial + $counter, 5, '0', STR_PAD_LEFT);
                } else {
                    $newSerial = str_pad($counter, 5, '0', STR_PAD_LEFT);
                }
                $potentialSku = $prefix . $newSerial;
                $counter++;
            } while (Product::where('sku', $potentialSku)->exists());
            
            $validated['sku'] = $potentialSku;
        }

        // Snapshot data SEBELUM update untuk log 'old'
        $beforeSnapshot = $product->only(['nama', 'merk', 'sku', 'stok', 'unit', 'harga_beli', 'harga_jual', 'min_stok']);

        $product->update($validated);

        // Sync Units
        $product->units()->delete();
        if ($request->filled('units')) {
            foreach ($request->units as $u) {
                $isBase = isset($u['is_base']) && ($u['is_base'] == '1' || $u['is_base'] === true);

                $product->units()->create([
                    'nama' => $u['nama'],
                    'isi'  => $u['isi'],
                    'harga_jual' => $u['harga_jual'],
                    'is_base' => $isBase
                ]);

                // Sync back to product if it's the base unit
                if ($isBase) {
                    $product->update([
                        'unit' => $u['nama'],
                        'harga_jual' => $u['harga_jual']
                    ]);
                }
            }
        }

        // Log aktivitas UPDATED
        if (function_exists('activity')) {
            $afterSnapshot = $product->fresh()->only(['nama', 'merk', 'sku', 'stok', 'unit', 'harga_beli', 'harga_jual', 'min_stok']);
            activity('DataBarang')
                ->performedOn($product)
                ->causedBy(auth()->user())
                ->event('updated')
                ->withProperties(['old' => $beforeSnapshot, 'attributes' => $afterSnapshot])
                ->log("Produk '{$product->nama}' (SKU: {$product->sku}) berhasil diperbarui.");
        }

        return redirect()->back()->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Snapshot data SEBELUM dihapus untuk log 'old'
        $beforeSnapshot = $product->only(['nama', 'merk', 'sku', 'stok', 'unit', 'harga_beli', 'harga_jual', 'min_stok']);

        if ($product->image && \Storage::disk('public')->exists($product->image)) {
            \Storage::disk('public')->delete($product->image);
        }
        $product->delete();

        // Log aktivitas DELETED
        if (function_exists('activity')) {
            activity('DataBarang')
                ->causedBy(auth()->user())
                ->event('deleted')
                ->withProperties(['old' => $beforeSnapshot])
                ->log("Produk '{$beforeSnapshot['nama']}' (SKU: {$beforeSnapshot['sku']}) berhasil dihapus.");
        }

        return redirect()->back()->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * AJAX: Get subcategories by category ID
     */
    public function getSubCategories(Request $request)
    {
        $subs = SubCategory::where('kategori_id', $request->category_id)->get(['id', 'nama']);
        return response()->json($subs);
    }
}
