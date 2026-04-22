<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\Product;
use Modules\Supplier\Models\Supplier;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('supplier')->latest()->get();
        $suppliers = Supplier::all();

        $totalProducts = $products->count();
        $lowStockCount = $products->where('stock', '<=', 20)->where('stock', '>', 0)->count();
        $outOfStockCount = $products->where('stock', '<=', 0)->count();

        return view('product::index', compact(
            'products', 
            'suppliers', 
            'totalProducts', 
            'lowStockCount', 
            'outOfStockCount'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'category' => 'nullable|string',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'stock' => 'required|integer|min:0',
            'unit' => 'required|string',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'min_stock' => 'required|integer|min:0',
        ]);

        Product::create($validated);

        return redirect()->back()->with('success', 'Product created successfully.');
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'category' => 'nullable|string',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'stock' => 'required|integer|min:0',
            'unit' => 'required|string',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'min_stock' => 'required|integer|min:0',
        ]);

        $product->update($validated);

        return redirect()->back()->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->back()->with('success', 'Product deleted successfully.');
    }
}
