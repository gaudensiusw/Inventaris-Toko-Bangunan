<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\Product;
use Modules\Product\Models\Category;
use Modules\Product\Models\Satuan;
use Modules\Supplier\Models\Supplier;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['supplier', 'category', 'subCategory', 'units']);

        // Search by Name or SKU
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%");
            });
        }

        // Filter by Category
        if ($request->filled('category')) {
            $query->where('kategori_id', $request->get('category'));
        }

        // Filter by Supplier
        if ($request->filled('supplier')) {
            $query->where('supplier_id', $request->get('supplier'));
        }

        // Statistics (based on total inventory, not just the search result)
        $totalProducts = Product::count();
        $lowStockCount = Product::whereRaw('stok <= min_stok')->where('stok', '>', 0)->count();
        $outOfStockCount = Product::where('stok', '<=', 0)->count();

        // Paginate results (Dynamic per page)
        $perPage = $request->get('per_page', 15);
        $products = $query->latest()->paginate($perPage)->withQueryString();
        
        $suppliers = Supplier::all();
        $categories = Category::all();
        $availableUnits = Satuan::all();

        return view('product::index', compact(
            'products', 
            'suppliers', 
            'categories',
            'availableUnits',
            'totalProducts', 
            'lowStockCount', 
            'outOfStockCount'
        ));
    }

    public function store(Request $request)
    {
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
            'units'           => 'nullable|array',
            'units.*.nama'    => 'required|string',
            'units.*.isi'     => 'required|numeric|min:0.01',
            'units.*.harga_jual' => 'required|numeric|min:0',
            'units.*.is_base' => 'nullable',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = $path;
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

        return redirect()->back()->with('success', 'Produk berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        
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
            'units'           => 'nullable|array',
            'units.*.nama'    => 'required|string',
            'units.*.isi'     => 'required|numeric|min:0.01',
            'units.*.harga_jual' => 'required|numeric|min:0',
            'units.*.is_base' => 'nullable',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image && \Storage::disk('public')->exists($product->image)) {
                \Storage::disk('public')->delete($product->image);
            }
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = $path;
        }

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

        return redirect()->back()->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        if ($product->image && \Storage::disk('public')->exists($product->image)) {
            \Storage::disk('public')->delete($product->image);
        }
        $product->delete();
        return redirect()->back()->with('success', 'Produk berhasil dihapus.');
    }
}
