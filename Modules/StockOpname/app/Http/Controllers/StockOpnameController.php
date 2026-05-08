<?php

namespace Modules\StockOpname\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Modules\Product\Models\Product;
use Modules\StockOpname\Models\StockOpname;
use Illuminate\Support\Facades\DB;

class StockOpnameController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('nama')->get();
        return view('stockopname::index', compact('products'));
    }

    public function history()
    {
        $history = StockOpname::with('product')->latest()->paginate(20);
        return view('stockopname::history', compact('history'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'produk_id'  => 'required|exists:produk,id',
            'stok_fisik' => 'required|integer|min:0',
            'keterangan' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $product = Product::findOrFail($request->produk_id);
            $selisih = $request->stok_fisik - $product->stok;

            StockOpname::create([
                'produk_id'   => $request->produk_id,
                'stok_sistem' => $product->stok,
                'stok_fisik'  => $request->stok_fisik,
                'selisih'     => $selisih,
                'keterangan'  => $request->keterangan,
            ]);

            // Update product stock to match physical stock
            $product->update(['stok' => $request->stok_fisik]);

            DB::commit();
            return redirect()->back()->with('success', 'Stock opname berhasil dicatat.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mencatat opname: ' . $e->getMessage());
        }
    }
}
