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
        $suppliers = Supplier::orderBy('company_name')->get();
        // Load products with their units
        $products = Product::with('units')->orderBy('nama')->get();
        $pembelians = Pembelian::with(['supplier', 'details.product'])->latest()->paginate(15);

        return view('pembelian::index', compact('suppliers', 'products', 'pembelians'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:supplier,id',
            'tgl_pembelian' => 'required|date',
            'jatuh_tempo' => 'required|date',
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
                'status' => 'selesai'
            ]);

            // Process Items
            foreach ($request->items as $item) {
                PembelianDetail::create([
                    'pembelian_id' => $pembelian->id,
                    'produk_id' => $item['produk_id'],
                    'satuan' => $item['satuan'],
                    'qty' => $item['qty'],
                    'isi_per_satuan' => $item['isi_per_satuan'],
                    'harga_total' => $item['harga_total']
                ]);

                // Update Stock and calculate MAC
                $product = Product::find($item['produk_id']);
                
                $stokLama = $product->stok;
                $hargaBeliLama = $product->harga_beli;
                $nilaiStokLama = $stokLama * $hargaBeliLama;

                $qtyBaruDalamSatuanUtama = $item['qty'] * $item['isi_per_satuan'];
                $nilaiStokBaru = $item['harga_total'];

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
                    'harga_beli' => round($macBaru) // Bulatkan ke integer terdekat
                ]);
            }

            // Auto-create Tagihan Supplier
            TagihanSupplier::create([
                'supplier_id' => $request->supplier_id,
                'no_invoice' => $no_transaksi,
                'tgl_invoice' => $request->tgl_pembelian,
                'jatuh_tempo' => $request->jatuh_tempo,
                'total' => $totalPembelian,
                'status' => 'belum_bayar',
                'catatan' => 'Auto-generated dari Transaksi Pembelian ' . $no_transaksi . "\n" . $request->catatan
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Transaksi Pembelian berhasil disimpan, stok bertambah, Harga Modal (MAC) diperbarui, dan Tagihan Supplier otomatis dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        // For simplicity, we might not allow deleting a purchase, or if we do, we need to reverse stock and MAC (which is complex).
        // For now, let's just delete the header, details cascade delete. But reversing MAC is hard without history table.
        // Returning an error for safety.
        return redirect()->back()->with('error', 'Transaksi Pembelian tidak dapat dihapus karena mempengaruhi perhitungan nilai aset (MAC).');
    }
}
