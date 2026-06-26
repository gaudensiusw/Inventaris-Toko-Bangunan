<?php

namespace Modules\Pembelian\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Pembelian\Models\Pembelian;
use Modules\Pembelian\Models\PembelianDetail;
use Modules\Product\Models\Product;
use Modules\Supplier\Models\Supplier;
use Modules\TagihanSupplier\Models\TagihanSupplier;
use Illuminate\Support\Facades\DB;

class PembelianController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::select('id', 'company_name')->orderBy('company_name')->get();
        // Load products with their units
        $products = Product::select('id', 'nama', 'sku')->with('units:id,produk_id,satuan,isi')->orderBy('nama')->get();
        $pembelians = Pembelian::with(['supplier', 'details.product'])->latest()->paginate(15);

        return view('pembelian::index', compact('suppliers', 'products', 'pembelians'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:supplier,id',
            'tgl_pembelian' => 'required|date',
            'jatuh_tempo' => 'required|date',
            'status' => 'required|in:selesai,pending',
            'items' => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:produk,id',
            'items.*.satuan' => 'required|string',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.isi_per_satuan' => 'required|numeric|min:1',
            'items.*.harga_total' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $totalPembelian = 0;
            foreach ($request->items as $item) {
                $totalPembelian += $item['harga_total'];
            }

            // Generate No Transaksi
            $count = Pembelian::whereDate('created_at', today())->count() + 1;
            $no_transaksi = 'PO-' . date('Ymd') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

            // Create Pembelian
            $pembelian = Pembelian::create([
                'no_transaksi' => $no_transaksi,
                'tgl_pembelian' => $request->tgl_pembelian,
                'supplier_id' => $request->supplier_id,
                'total_pembelian' => $totalPembelian,
                'catatan' => $request->catatan,
                'status' => $request->status
            ]);

            // Process Items
            foreach ($request->items as $item) {
                $detail = PembelianDetail::create([
                    'pembelian_id' => $pembelian->id,
                    'produk_id' => $item['produk_id'],
                    'satuan' => $item['satuan'],
                    'qty' => $item['qty'],
                    'isi_per_satuan' => $item['isi_per_satuan'],
                    'harga_total' => $item['harga_total']
                ]);

                // Update Stock and MAC ONLY if status is 'selesai'
                if ($request->status === 'selesai') {
                    $this->updateStockAndMAC($detail);
                }
            }

            // Auto-create Tagihan Supplier
            TagihanSupplier::create([
                'supplier_id' => $request->supplier_id,
                'no_invoice' => $no_transaksi,
                'tgl_invoice' => $request->tgl_pembelian,
                'jatuh_tempo' => $request->jatuh_tempo,
                'total' => $totalPembelian,
                'status' => 'belum_bayar',
                'catatan' => 'Auto-generated dari Transaksi Pembelian ' . $no_transaksi . ' (Status: ' . $request->status . ")\n" . $request->catatan
            ]);

            DB::commit();
            $msg = $request->status === 'selesai' 
                ? 'Transaksi Pembelian berhasil disimpan, stok bertambah, MAC diperbarui.' 
                : 'Transaksi Pembelian berhasil disimpan (PENDING), stok belum bertambah.';
            
            return redirect()->back()->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function receive($id)
    {
        try {
            DB::beginTransaction();
            $pembelian = Pembelian::with('details')->findOrFail($id);

            if ($pembelian->status === 'selesai') {
                return redirect()->back()->with('error', 'Transaksi ini sudah selesai.');
            }

            foreach ($pembelian->details as $detail) {
                $this->updateStockAndMAC($detail);
            }

            $pembelian->update(['status' => 'selesai']);

            DB::commit();
            return redirect()->back()->with('success', 'Barang telah diterima. Stok dan MAC berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui stok: ' . $e->getMessage());
        }
    }

    private function updateStockAndMAC($detail)
    {
        $product = Product::find($detail->produk_id);
        
        $stokLama = $product->stok;
        $hargaBeliLama = $product->harga_beli;
        $nilaiStokLama = $stokLama * $hargaBeliLama;

        $qtyBaruDalamSatuanUtama = $detail->qty * $detail->isi_per_satuan;
        $nilaiStokBaru = $detail->harga_total;

        // Calculate MAC
        $totalQty = $stokLama + $qtyBaruDalamSatuanUtama;
        if ($totalQty > 0) {
            $macBaru = ($nilaiStokLama + $nilaiStokBaru) / $totalQty;
        } else {
            $macBaru = $hargaBeliLama;
        }

        // Update product
        $product->update([
            'stok' => $totalQty,
            'harga_beli' => round($macBaru)
        ]);
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'owner') {
            return redirect()->back()->with('error', 'Hanya Owner yang diperbolehkan menghapus transaksi.');
        }

        try {
            DB::beginTransaction();

            $pembelian = Pembelian::with('details')->findOrFail($id);

            // 1. Reverse Stock and MAC if status was 'selesai'
            if ($pembelian->status === 'selesai') {
                foreach ($pembelian->details as $detail) {
                    $product = Product::find($detail->produk_id);
                    if (!$product) continue;

                    $qtyDibeli = $detail->qty * $detail->isi_per_satuan;
                    $totalHargaIni = $detail->harga_total;

                    $stokSaatIni = $product->stok;
                    $macSaatIni = $product->harga_beli;

                    // Perhitungan Mundur:
                    // Nilai Aset Lama = (Stok Sekarang * MAC Sekarang) - Total Harga Pembelian Ini
                    // Stok Lama = Stok Sekarang - Qty Pembelian Ini
                    // MAC Lama = Nilai Aset Lama / Stok Lama
                    
                    $stokBaru = $stokSaatIni - $qtyDibeli;
                    
                    // Validasi agar stok tidak minus jika sudah terlanjur terjual
                    if ($stokBaru < 0) {
                        throw new \Exception("Gagal hapus: Stok barang '{$product->nama}' tidak mencukupi untuk dikurangi (sudah terlanjur terjual).");
                    }

                    if ($stokBaru > 0) {
                        $nilaiAsetSekarang = $stokSaatIni * $macSaatIni;
                        $nilaiAsetLama = $nilaiAsetSekarang - $totalHargaIni;
                        $macLama = $nilaiAsetLama / $stokBaru;
                    } else {
                        // Jika stok jadi 0, MAC tidak perlu dihitung ulang secara rumit, biarkan saja
                        $macLama = $macSaatIni;
                    }

                    $product->update([
                        'stok' => $stokBaru,
                        'harga_beli' => round(max(0, $macLama))
                    ]);
                }
            }

            // 2. Delete associated Tagihan Supplier
            TagihanSupplier::where('no_invoice', $pembelian->no_transaksi)->delete();

            // 3. Delete Pembelian (details will cascade delete if set up in DB, otherwise manual delete)
            $pembelian->details()->delete();
            $pembelian->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Transaksi Pembelian berhasil dihapus. Stok, MAC, dan Tagihan Supplier telah dikoreksi.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }
}
