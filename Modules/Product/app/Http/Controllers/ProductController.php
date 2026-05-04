<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\Product;
use Modules\Product\Models\Category;
use Modules\Supplier\Models\Supplier;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['supplier', 'category']);

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

        return view('product::index', compact(
            'products', 
            'suppliers', 
            'categories',
            'totalProducts', 
            'lowStockCount', 
            'outOfStockCount'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'        => 'required|string|max:255',
            'merk'        => 'nullable|string|max:255',
            'sku'         => 'nullable|string|unique:produk,sku',
            'kategori_id' => 'nullable|exists:kategori,id',
            'supplier_id' => 'nullable|exists:supplier,id',
            'stok'        => 'required|integer|min:0',
            'unit'        => 'required|string',
            'harga_beli'  => 'required|numeric|min:0',
            'harga_jual'  => 'required|numeric|min:0',
            'min_stok'    => 'required|integer|min:0',
        ]);

        Product::create($validated);

        return redirect()->back()->with('success', 'Produk berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        
        $validated = $request->validate([
            'nama'        => 'required|string|max:255',
            'merk'        => 'nullable|string|max:255',
            'sku'         => 'nullable|string|unique:produk,sku,' . $product->id,
            'kategori_id' => 'nullable|exists:kategori,id',
            'supplier_id' => 'nullable|exists:supplier,id',
            'stok'        => 'required|integer|min:0',
            'unit'        => 'required|string',
            'harga_beli'  => 'required|numeric|min:0',
            'harga_jual'  => 'required|numeric|min:0',
            'min_stok'    => 'required|integer|min:0',
        ]);

        $product->update($validated);

        return redirect()->back()->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return redirect()->back()->with('success', 'Produk berhasil dihapus.');
    }
}
