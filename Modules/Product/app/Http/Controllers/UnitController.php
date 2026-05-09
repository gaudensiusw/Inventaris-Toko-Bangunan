<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\Satuan;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $units = Satuan::orderBy('kategori', 'asc')->orderBy('nama', 'asc')->get();
        $groupedUnits = $units->groupBy('kategori');
        
        $activeUnit = null;
        $productsUsing = collect();

        if ($request->filled('id')) {
            $activeUnit = Satuan::find($request->id);
        }

        if (!$activeUnit && $units->count() > 0) {
            $activeUnit = $units->first();
        }

        if ($activeUnit) {
            // Find products using this unit name in their units list
            $productsUsing = \Modules\Product\Models\Product::whereHas('units', function($q) use ($activeUnit) {
                $q->where('nama', $activeUnit->nama);
            })->orWhere('unit', $activeUnit->nama)->latest()->take(10)->get();
        }

        return view('product::units.index', compact('units', 'groupedUnits', 'activeUnit', 'productsUsing'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'     => 'required|string|unique:satuan,nama',
            'simbol'   => 'nullable|string',
            'kategori' => 'nullable|string'
        ]);

        Satuan::create($validated);
        return redirect()->back()->with('success', 'Satuan berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $unit = Satuan::findOrFail($id);
        $validated = $request->validate([
            'nama'     => 'required|string|unique:satuan,nama,' . $unit->id,
            'simbol'   => 'nullable|string',
            'kategori' => 'nullable|string'
        ]);

        $unit->update($validated);
        return redirect()->back()->with('success', 'Satuan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $unit = Satuan::findOrFail($id);
        $unit->delete();
        return redirect()->back()->with('success', 'Satuan berhasil dihapus.');
    }
}
