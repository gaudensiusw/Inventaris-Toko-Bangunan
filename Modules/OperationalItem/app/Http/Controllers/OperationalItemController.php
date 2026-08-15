<?php

namespace Modules\OperationalItem\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Modules\OperationalItem\Models\ItemOperasional;

class OperationalItemController extends Controller
{
    public function index(Request $request)
    {
        $bulan = sprintf('%02d', $request->get('bulan', date('m')));
        $tahun = $request->get('tahun', date('Y'));

        // Query item operasional per bulan & tahun (fallback ke created_at jika tanggal_pembelian null)
        $itemsBulanIni = ItemOperasional::where(function($q) use ($bulan, $tahun) {
                $q->whereMonth(\DB::raw('COALESCE(tanggal_pembelian, created_at)'), $bulan)
                  ->whereYear(\DB::raw('COALESCE(tanggal_pembelian, created_at)'), $tahun);
            })
            ->latest('tanggal_pembelian')
            ->get();

        $totalBulanIni = $itemsBulanIni->sum(fn($i) => $i->jumlah * $i->harga);
        $totalKeseluruhan = ItemOperasional::all()->sum(fn($i) => $i->jumlah * $i->harga);

        $items = ItemOperasional::latest()->get();

        $listBulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];

        return view('operationalitem::index', compact(
            'items',
            'itemsBulanIni',
            'totalBulanIni',
            'totalKeseluruhan',
            'bulan',
            'tahun',
            'listBulan'
        ));
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
