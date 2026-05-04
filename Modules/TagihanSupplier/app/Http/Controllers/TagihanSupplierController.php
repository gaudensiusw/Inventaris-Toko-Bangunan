<?php

namespace Modules\TagihanSupplier\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Supplier\Models\Supplier;
use Modules\TagihanSupplier\Models\TagihanSupplier;

class TagihanSupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('company_name')->get();
        $bills = TagihanSupplier::with('supplier')->latest()->get();
        
        return view('tagihansupplier::index', compact('suppliers', 'bills'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:supplier,id',
            'no_invoice'  => 'required|string|max:50',
            'tgl_invoice' => 'required|date',
            'jatuh_tempo' => 'required|date',
            'total'       => 'required|numeric|min:0',
            'status'      => 'required|in:belum_bayar,cicilan,lunas',
            'catatan'     => 'nullable|string',
        ]);

        TagihanSupplier::create($validated);
        return redirect()->back()->with('success', 'Tagihan supplier berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $bill = TagihanSupplier::findOrFail($id);
        $validated = $request->validate([
            'supplier_id' => 'required|exists:supplier,id',
            'no_invoice'  => 'required|string|max:50',
            'tgl_invoice' => 'required|date',
            'jatuh_tempo' => 'required|date',
            'total'       => 'required|numeric|min:0',
            'status'      => 'required|in:belum_bayar,cicilan,lunas',
            'catatan'     => 'nullable|string',
        ]);

        $bill->update($validated);
        return redirect()->back()->with('success', 'Tagihan supplier berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $bill = TagihanSupplier::findOrFail($id);
        $bill->delete();
        return redirect()->back()->with('success', 'Tagihan supplier berhasil dihapus.');
    }
}
