<?php

namespace Modules\Supplier\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Supplier\Models\Supplier;
use Modules\Supplier\Http\Requests\StoreSupplierRequest;
use Modules\Supplier\Http\Requests\UpdateSupplierRequest;
use Modules\Product\Models\Product;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::withCount('products')
            ->orderBy('company_name', 'asc')
            ->paginate(15);
            
        $totalSuppliers = Supplier::count();
        $activeProducts = Product::whereNotNull('supplier_id')->count();
        $avgProducts = $totalSuppliers > 0 ? round($activeProducts / $totalSuppliers, 1) : 0;

        return view('supplier::index', compact('suppliers', 'totalSuppliers', 'activeProducts', 'avgProducts'));
    }

    public function store(StoreSupplierRequest $request)
    {
        Supplier::create($request->validated());
        return redirect()->route('supplier.index')->with('success', 'Supplier created successfully.');
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $supplier->update($request->validated());
        return redirect()->route('supplier.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        // soft delete jika ada produk, but standard delete for now.
        // Usually we would check $supplier->products()->count() > 0
        if ($supplier->products()->count() > 0) {
            // we could perform soft delete here if SoftDeletes was added,
            // or just disallow
            return redirect()->route('supplier.index')->with('error', 'Cannot delete supplier with active products.');
        }

        $supplier->delete();
        return redirect()->route('supplier.index')->with('success', 'Supplier deleted successfully.');
    }
}
