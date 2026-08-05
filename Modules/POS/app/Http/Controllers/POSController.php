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
        $products = Product::with([
            'category:id,nama',
            'units:id,produk_id,nama,isi,harga_jual,is_base'
        ])
        ->select('id', 'nama', 'sku', 'merk', 'kategori_id', 'unit', 'harga_jual', 'stok', 'min_stok', 'aktif_grosir', 'min_qty_grosir', 'harga_grosir', 'image')
        ->where('stok', '>', 0)
        ->get();

        $categories = Category::select('id', 'nama')->get();
        $customers = Customer::select('id', 'nama', 'kode', 'telp', 'tenor_bayar')->get();

        return view('pos::index', compact('products', 'categories', 'customers'));
    }

    public function history()
    {
        $user = auth()->user();
        $query = POS::with(['pelanggan', 'details.product'])->latest();

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
            'nama_pelanggan'    => 'nullable|string|max:255',
            'telp_pelanggan'    => 'nullable|string|max:20',
            'jatuh_tempo'       => 'nullable|date',
            'items'             => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:produk,id',
            'items.*.qty'       => 'required|numeric|min:0.01',
            'items.*.satuan_nama' => 'required|string',
            'items.*.isi'       => 'required|numeric|min:0.01',
            'items.*.harga'     => 'required|numeric|min:0',
            'items.*.diskon_rp' => 'nullable|numeric|min:0',
            'subtotal'          => 'required|numeric',
            'pajak'             => 'required|numeric',
            'ongkos_kirim'      => 'nullable|numeric|min:0',
            'total_tagihan'     => 'required|numeric',
            'metode_pembayaran' => 'required|string',
            'opsi_pengiriman'   => 'required|string',
            'supervisor_email'  => 'nullable|string|email',
            'supervisor_password' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Check if any item breaches min_stok
            $requiresApproval = false;
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['produk_id']);
                $stok_pengurang = $item['qty'] * $item['isi'];
                if ($product->stok - $stok_pengurang < $product->min_stok) {
                    $requiresApproval = true;
                    break;
                }
            }

            $user = auth()->user();
            if ($requiresApproval && !in_array($user->role, ['owner', 'supervisor'])) {
                if (!$request->filled('supervisor_email') || !$request->filled('supervisor_password')) {
                    throw new \Exception("Persetujuan Supervisor diperlukan karena pembelian ini menembus batas minimum stok.");
                }
                $supervisor = \App\Models\User::where('email', $request->supervisor_email)->first();
                if (!$supervisor || !in_array($supervisor->role, ['supervisor', 'owner'])) {
                    throw new \Exception("Kredensial Supervisor tidak valid atau bukan supervisor.");
                }
                if (!\Illuminate\Support\Facades\Hash::check($request->supervisor_password, $supervisor->password)) {
                    throw new \Exception("Password Supervisor salah.");
                }
            }

            // If payment is Bon, handle debt logic
            $status_pembayaran = 'lunas';
            $jatuh_tempo = null;
            $jumlah_bayar = $request->total_tagihan;
            $pelanggan_id = null;
            $nama_pelanggan = $request->nama_pelanggan ?: 'Umum';

            if ($request->metode_pembayaran === 'Bon') {
                if (!$request->nama_pelanggan || strtolower($request->nama_pelanggan) === 'umum') {
                    throw new \Exception("Pembayaran BON wajib menyertakan Nama Pelanggan yang spesifik.");
                }
                $status_pembayaran = 'belum_bayar';
                $jumlah_bayar = 0; 
                $jatuh_tempo = $request->jatuh_tempo ?: now()->addDays(30);
            }

            // Find existing or create new customer for ANY payment method if customer name is provided
            if ($request->filled('nama_pelanggan') && strtolower($request->nama_pelanggan) !== 'umum') {
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

                // Adjust jatuh_tempo for Bon based on customer preference
                if ($request->metode_pembayaran === 'Bon') {
                    $jatuh_tempo = $request->jatuh_tempo ?: now()->addDays($customer->tenor_bayar ?: 30);
                }
            } else {
                // If customer name is empty or is 'umum', link to the default 'Umum' customer
                $umumCustomer = Customer::firstOrCreate(
                    ['kode' => 'CUST-UMUM'],
                    [
                        'nama' => 'Umum',
                        'kategori' => 'Umum',
                        'limit_kredit' => 0,
                        'tenor_bayar' => 30,
                        'status' => 'aktif'
                    ]
                );
                $pelanggan_id = $umumCustomer->id;
                $nama_pelanggan = 'Umum';
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
                'ongkos_kirim'     => $request->ongkos_kirim ?? 0,
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
                
                // Calculate stock decrement in base unit
                $stok_pengurang = $item['qty'] * $item['isi'];

                if ($product->stok < $stok_pengurang) {
                    throw new \Exception("Stok produk {$product->nama} tidak mencukupi. Sisa stok (dasar): {$product->stok}");
                }

                $diskon_rp = $item['diskon_rp'] ?? 0;
                $harga_final = max(0, $item['harga'] - $diskon_rp);

                POSDetail::create([
                    'pos_id'       => $pos->id,
                    'produk_id'    => $item['produk_id'],
                    'satuan_nama'  => $item['satuan_nama'],
                    'isi'          => $item['isi'],
                    'harga_satuan' => $item['harga'],
                    'diskon_rp'    => $diskon_rp,
                    'qty'          => $item['qty'],
                    'harga'        => $harga_final, // this is the final selling price per unit chosen
                    'subtotal'     => $item['qty'] * $harga_final,
                ]);

                $product->decrement('stok', $stok_pengurang);
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

    public function getRecommendations(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required|string'
        ]);

        $rekomendasi = DB::table('t_rekomendasi')
            ->where('barang_pemicu', $request->nama_produk)
            ->orderBy('confidence', 'desc')
            ->limit(3)
            ->get();

        if ($rekomendasi->count() > 0) {
            return response()->json([
                'success' => true,
                'message' => 'Rekomendasi ditemukan',
                'data'    => $rekomendasi
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada rekomendasi untuk produk ini'
            ]);
        }
    }

    public function returIndex(Request $request)
    {
        $selected_trx = null;
        $search = $request->get('search') ?: $request->get('trx_id');

        if ($search) {
            $selected_trx = POS::with(['pelanggan', 'details.product', 'refunds'])
                ->where('id', $search)
                ->orWhere('no_transaksi', 'LIKE', "%{$search}%")
                ->first();
        }

        $recent_transactions = POS::with('pelanggan')->latest()->limit(10)->get();
        $recent_refunds = \Modules\POS\Models\POSRefund::with(['pos.pelanggan', 'user', 'product'])->latest()->paginate(15);

        return view('pos::retur', compact('selected_trx', 'recent_transactions', 'recent_refunds', 'search'));
    }

    public function processRetur(Request $request)
    {
        $request->validate([
            'pos_id'             => 'required|exists:pos,id',
            'items'              => 'required|array',
            'items.*.detail_id'  => 'required|exists:pos_detail,id',
            'items.*.qty_refund' => 'nullable|numeric|min:0',
            'items.*.kondisi'    => 'required|in:layak,rusak',
            'alasan'             => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $pos = POS::with(['details.product', 'refunds'])->findOrFail($request->pos_id);
            $total_nominal_refund = 0;
            $hasRefund = false;
            $created_refund_ids = [];

            foreach ($request->items as $item) {
                $qty_refund = floatval($item['qty_refund'] ?? 0);
                if ($qty_refund > 0) {
                    $detail = $pos->details()->where('id', $item['detail_id'])->first();
                    if (!$detail) continue;

                    if ($qty_refund > $detail->qty) {
                        throw new \Exception("Qty refund untuk {$detail->product->nama} melebihi jumlah pembelian.");
                    }

                    $hasRefund = true;
                    $isi = $detail->isi > 0 ? $detail->isi : 1;
                    $harga = $detail->harga_satuan > 0 ? $detail->harga_satuan : $detail->harga;
                    $nominal_refund = $qty_refund * $harga;
                    $total_nominal_refund += $nominal_refund;

                    $kondisiText = $item['kondisi'] === 'layak' ? 'Masuk Stok Jual' : 'Barang Rusak/Cacat';
                    $alasanLengkap = $request->alasan . ' (' . $kondisiText . ')';

                    // 1. Catat ke tabel pos_refunds
                    $ref = \Modules\POS\Models\POSRefund::create([
                        'pos_id'         => $pos->id,
                        'no_transaksi'   => $pos->no_transaksi,
                        'produk_id'      => $detail->produk_id,
                        'nama_produk'    => $detail->product ? $detail->product->nama : 'Produk',
                        'qty_refund'     => $qty_refund,
                        'nominal_refund' => $nominal_refund,
                        'alasan'         => $alasanLengkap,
                        'tgl_refund'     => now(),
                        'user_id'        => auth()->id(),
                    ]);

                    $created_refund_ids[] = $ref->id;

                    // 2. Jika kondisi LAYAK JUAL -> Kembalikan Stok Produk
                    if ($item['kondisi'] === 'layak' && $detail->product) {
                        $detail->product->increment('stok', $qty_refund * $isi);
                    }

                    // 3. Kurangi Qty di detail POS
                    $sisa_qty = $detail->qty - $qty_refund;
                    if ($sisa_qty <= 0) {
                        $detail->delete();
                    } else {
                        $detail->update([
                            'qty'      => $sisa_qty,
                            'subtotal' => $sisa_qty * $harga
                        ]);
                    }
                }
            }

            if (!$hasRefund) {
                throw new \Exception("Centang dan masukkan Qty retur pada minimal 1 barang.");
            }

            // 4. Update total transaksi POS
            $new_subtotal = $pos->details()->sum('subtotal');
            $new_total_tagihan = $new_subtotal + ($pos->pajak ?: 0) + ($pos->ongkos_kirim ?: 0);

            $jumlah_bayar = $pos->jumlah_bayar;
            $status_pembayaran = $pos->status_pembayaran;

            if ($pos->metode_pembayaran === 'Bon') {
                if ($jumlah_bayar >= $new_total_tagihan && $new_total_tagihan > 0) {
                    $status_pembayaran = 'lunas';
                    if ($jumlah_bayar > $new_total_tagihan) {
                        $jumlah_bayar = $new_total_tagihan;
                    }
                } elseif ($jumlah_bayar > 0) {
                    $status_pembayaran = 'sebagian';
                } else {
                    $status_pembayaran = 'belum_bayar';
                }
            } else {
                $jumlah_bayar = $new_total_tagihan;
                $status_pembayaran = 'lunas';
            }

            $pos->update([
                'subtotal'          => $new_subtotal,
                'total_tagihan'     => $new_total_tagihan,
                'jumlah_bayar'      => $jumlah_bayar,
                'status_pembayaran' => $status_pembayaran,
            ]);

            DB::commit();

            return redirect()->route('pos.retur.receipt', $created_refund_ids[0])
                ->with('success', 'Retur barang berhasil diproses! Total refund: Rp ' . number_format($total_nominal_refund, 0, ',', '.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function returReceipt($id)
    {
        $refund = \Modules\POS\Models\POSRefund::with(['pos.pelanggan', 'user', 'product'])->findOrFail($id);
        $batch_refunds = \Modules\POS\Models\POSRefund::with('product')
            ->where('pos_id', $refund->pos_id)
            ->where('tgl_refund', $refund->tgl_refund)
            ->get();

        return view('pos::retur_receipt', compact('refund', 'batch_refunds'));
    }
}
