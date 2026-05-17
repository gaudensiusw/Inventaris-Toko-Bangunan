<?php

namespace Modules\StockManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Modules\Product\Models\Product;
use Modules\StockManagement\Models\StockManagement;
use Illuminate\Support\Facades\DB;

class StockManagementController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('nama')->get();
        $transactions = StockManagement::with('product')->latest()->limit(100)->get();

        return view('stockmanagement::index', compact('products', 'transactions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'produk_id'  => 'required|exists:produk,id',
            'tipe'       => 'required|in:in,out',
            'qty'        => 'required|integer|min:1',
            'keterangan' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $product = Product::findOrFail($request->produk_id);

            if ($request->tipe === 'out' && $product->stok < $request->qty) {
                return redirect()->back()->with('error', 'Stok tidak mencukupi untuk pengeluaran ini.');
            }

            StockManagement::create($validated);

            if ($request->tipe === 'in') {
                $product->increment('stok', $request->qty);
            } else {
                $product->decrement('stok', $request->qty);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Pergerakan stok berhasil dicatat.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mencatat stok: ' . $e->getMessage());
        }
    }

    public function updateNote(Request $request, $id)
    {
        $request->validate([
            'keterangan' => 'nullable|string|max:255',
        ]);

        try {
            $transaction = StockManagement::findOrFail($id);
            $transaction->update([
                'keterangan' => $request->keterangan
            ]);

            return redirect()->back()->with('success', 'Catatan berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui catatan: ' . $e->getMessage());
        }
    }
}
