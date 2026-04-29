<?php

namespace Modules\POS\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\Product;
use Modules\Product\Models\Category;
use Modules\Customer\Models\Customer;
use Modules\POS\Models\POS;
use Modules\POS\Models\POSDetail;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->where('stok', '>', 0)->get();
        $categories = Category::all();

        return view('pos::index', compact('products', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pelanggan'  => 'nullable|string|max:255',
            'items'           => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:produk,id',
            'items.*.qty'     => 'required|integer|min:1',
            'subtotal'        => 'required|numeric',
            'pajak'           => 'required|numeric',
            'total_tagihan'   => 'required|numeric',
            'metode_pembayaran' => 'required|string',
            'opsi_pengiriman' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            // If payment is Bon, auto-save customer record
            $pelanggan_id = null;
            $nama_pelanggan = $request->nama_pelanggan ?: 'Umum';

            if ($request->metode_pembayaran === 'Bon' && $request->nama_pelanggan) {
                // Find existing or create new customer
                $customer = Customer::firstOrCreate(
                    ['nama' => $request->nama_pelanggan],
                    ['nama' => $request->nama_pelanggan]
                );
                $pelanggan_id = $customer->id;
            }

            $no_transaksi = 'TRX-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

            $pos = POS::create([
                'no_transaksi'     => $no_transaksi,
                'tgl_transaksi'    => now(),
                'pelanggan_id'     => $pelanggan_id,
                'nama_pelanggan'   => $nama_pelanggan,
                'subtotal'         => $request->subtotal,
                'pajak'            => $request->pajak,
                'total_tagihan'    => $request->total_tagihan,
                'metode_pembayaran'=> $request->metode_pembayaran,
                'opsi_pengiriman'  => $request->opsi_pengiriman,
                'catatan'          => $request->catatan,
                'status'           => 'checkout',
            ]);

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['produk_id']);

                if ($product->stok < $item['qty']) {
                    throw new \Exception("Stok produk {$product->nama} tidak mencukupi.");
                }

                POSDetail::create([
                    'pos_id'    => $pos->id,
                    'produk_id' => $item['produk_id'],
                    'qty'       => $item['qty'],
                    'harga'     => $product->harga_jual,
                    'subtotal'  => $item['qty'] * $product->harga_jual,
                ]);

                $product->decrement('stok', $item['qty']);
            }

            DB::commit();

            return response()->json([
                'success'    => true,
                'message'    => 'Transaksi berhasil disimpan.',
                'pos_id'     => $pos->id,
                'receipt_url'=> route('pos.receipt', $pos->id),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function receipt($id)
    {
        $pos = POS::with(['details.product', 'pelanggan'])->findOrFail($id);
        return view('pos::receipt', compact('pos'));
    }
}
