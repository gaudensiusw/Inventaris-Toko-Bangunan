<?php

namespace Modules\OperationalItem\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Modules\OperationalItem\Models\ItemOperasional;

class OperationalItemController extends Controller
{
    public function index()
    {
        $items = ItemOperasional::latest()->get();
        return view('operationalitem::index', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'               => 'required|string|max:255',
            'kategori'           => 'nullable|string|max:100',
            'jumlah'             => 'required|numeric|min:0',
            'satuan'             => 'nullable|string|max:50',
            'harga'              => 'required|numeric|min:0',
            'deskripsi'          => 'nullable|string',
            'tanggal_penggunaan' => 'nullable|date',
            'tanggal_pembelian'  => 'nullable|date',
            'status'             => 'required|in:aktif,habis,rusak',
        ]);

        // Default karyawan_id to current user if needed, or 1 for now
        $validated['karyawan_id'] = auth()->user()->karyawan_id ?: 1;

        ItemOperasional::create($validated);
        return redirect()->back()->with('success', 'Item operasional berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $item = ItemOperasional::findOrFail($id);
        $validated = $request->validate([
            'nama'               => 'required|string|max:255',
            'kategori'           => 'nullable|string|max:100',
            'jumlah'             => 'required|numeric|min:0',
            'satuan'             => 'nullable|string|max:50',
            'harga'              => 'required|numeric|min:0',
            'deskripsi'          => 'nullable|string',
            'tanggal_penggunaan' => 'nullable|date',
            'tanggal_pembelian'  => 'nullable|date',
            'status'             => 'required|in:aktif,habis,rusak',
        ]);

        $item->update($validated);
        return redirect()->back()->with('success', 'Item operasional berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = ItemOperasional::findOrFail($id);
        $item->delete();
        return redirect()->back()->with('success', 'Item operasional berhasil dihapus.');
    }
}
