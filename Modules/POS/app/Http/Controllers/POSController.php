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
        $customers = Customer::select('id', 'nama', 'kode', 'telp')->get();

        return view('pos::index', compact('products', 'categories', 'customers'));
    }

    public function history()
    {
        $user = auth()->user();
        $query = POS::with('pelanggan')->latest();

        // If operator, only show their own transactions
        if ($user->role === 'operator') {
            $query->where('user_id', $user->id);
        }

        $transactions = $query->paginate(20);
        return view('pos::history', compact('transactions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pelanggan'  => 'nullable|string|max:255',
            'telp_pelanggan'  => 'nullable|string|max:20',
            'jatuh_tempo'     => 'nullable|date',
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

            // If payment is Bon, handle debt logic
            $status_pembayaran = 'lunas';
            $jatuh_tempo = null;
            $jumlah_bayar = $request->total_tagihan;
            $pelanggan_id = null;
            $nama_pelanggan = $request->nama_pelanggan ?: 'Umum';

            if ($request->metode_pembayaran === 'Bon') {
                if (!$request->nama_pelanggan) {
                    throw new \Exception("Pembayaran BON wajib menyertakan Nama Pelanggan.");
                }

                // Find existing or create new customer
                $customer = Customer::firstOrCreate(
                    ['nama' => $request->nama_pelanggan],
                    [
                        'nama' => $request->nama_pelanggan,
                        'telp' => $request->telp_pelanggan,
                        'kode' => 'CUST-' . strtoupper(bin2hex(random_bytes(2))),
                        'tenor_bayar' => 30
                    ]
                );

                // If customer exists but has no phone, update it
                if ($request->telp_pelanggan && !$customer->telp) {
                    $customer->update(['telp' => $request->telp_pelanggan]);
                }

                $pelanggan_id = $customer->id;
                $nama_pelanggan = $customer->nama;
                $status_pembayaran = 'belum_bayar';
                $jumlah_bayar = 0; 
                $jatuh_tempo = $request->jatuh_tempo ?: now()->addDays($customer->tenor_bayar ?: 30);
            }

            $no_transaksi = 'TRX-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

            $pos = POS::create([
                'user_id'          => auth()->id(),
                'no_transaksi'     => $no_transaksi,
                'tgl_transaksi'    => now(),
                'pelanggan_id'     => $pelanggan_id,
                'nama_pelanggan'   => $nama_pelanggan,
                'subtotal'         => $request->subtotal,
                'pajak'            => $request->pajak,
                'total_tagihan'    => $request->total_tagihan,
                'jumlah_bayar'    => $jumlah_bayar,
                'jatuh_tempo'      => $jatuh_tempo,
                'metode_pembayaran'=> $request->metode_pembayaran,
                'opsi_pengiriman'  => $request->opsi_pengiriman,
                'catatan'          => $request->catatan,
                'status'           => 'checkout',
                'status_pembayaran'=> $status_pembayaran,
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
