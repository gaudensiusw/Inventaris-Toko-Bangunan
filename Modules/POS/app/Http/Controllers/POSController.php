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
        $customers = Customer::select('id', 'nama', 'kode', 'telp', 'tenor_bayar', 'deposit')->get();

        return view('pos::index', compact('products', 'categories', 'customers'));
    }

    public function history(Request $request)
    {
        $user = auth()->user();
        $query = POS::with(['pelanggan', 'details.product', 'refunds'])->latest();

        if ($request->filled('status')) {
            $query->where('status_pembayaran', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_transaksi', 'LIKE', "%{$search}%")
                  ->orWhere('nama_pelanggan', 'LIKE', "%{$search}%");
            });
        }

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
            'biaya_addon'       => 'nullable|numeric|min:0',
            'keterangan_addon'  => 'nullable|string|max:255',
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

            // Find existing or create new customer for ANY payment method if customer name is provided
            $customer = null;
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

            // Handle Deposit Deduction Logic (Flexible & Automatic)
            $potongan_deposit = 0;
            $shouldUseDeposit = $request->boolean('use_deposit') || ($request->metode_pembayaran === 'Bon' && $customer && $customer->deposit > 0);

            if ($shouldUseDeposit && $customer && $customer->deposit > 0) {
                $potongan_deposit = min($customer->deposit, $request->total_tagihan);
                if ($potongan_deposit > 0) {
                    $customer->decrement('deposit', $potongan_deposit);
                }
            }

            $status_pembayaran = 'lunas';
            $jatuh_tempo = null;
            $jumlah_bayar = $request->total_tagihan;

            if ($request->metode_pembayaran === 'Deposit') {
                $jumlah_bayar = $potongan_deposit;
                $status_pembayaran = ($potongan_deposit >= $request->total_tagihan && $request->total_tagihan > 0) ? 'lunas' : 'sebagian';
            } elseif ($request->metode_pembayaran === 'Bon') {
                if (!$request->nama_pelanggan || strtolower($request->nama_pelanggan) === 'umum') {
                    throw new \Exception("Pembayaran BON wajib menyertakan Nama Pelanggan yang spesifik.");
                }
                $jumlah_bayar = $potongan_deposit;
                if ($jumlah_bayar >= $request->total_tagihan && $request->total_tagihan > 0) {
                    $status_pembayaran = 'lunas';
                } elseif ($jumlah_bayar > 0) {
                    $status_pembayaran = 'sebagian';
                } else {
                    $status_pembayaran = 'belum_bayar';
                }
                $jatuh_tempo = $request->jatuh_tempo ?: now()->addDays($customer ? ($customer->tenor_bayar ?: 30) : 30);
            } else {
                // Cash / Transfer / Lainnya
                $status_pembayaran = 'lunas';
                $jumlah_bayar = $request->total_tagihan;
            }

            $catatan = $request->catatan;
            if ($potongan_deposit > 0) {
                $catatan = trim(($catatan ? $catatan . ' | ' : '') . '[Dipotong Deposit: Rp ' . number_format($potongan_deposit, 0, ',', '.') . ']');
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
                'biaya_addon'      => $request->biaya_addon ?? 0,
                'keterangan_addon' => $request->keterangan_addon ?? null,
                'total_tagihan'    => $request->total_tagihan,
                'jumlah_bayar'    => $jumlah_bayar,
                'jatuh_tempo'      => $jatuh_tempo,
                'metode_pembayaran'=> $request->metode_pembayaran,
                'opsi_pengiriman'  => $request->opsi_pengiriman,
                'catatan'          => $catatan,
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
            'pos_id'              => 'required|exists:pos,id',
            'items'               => 'required|array',
            'items.*.detail_id'   => 'required|exists:pos_detail,id',
            'items.*.qty_refund'  => 'nullable|numeric|min:0',
            'items.*.kondisi'     => 'nullable|in:layak,rusak',
            'alasan'              => 'required|string',
            'supervisor_email'    => 'nullable|email',
            'supervisor_password' => 'nullable|string',
        ]);

        // Validasi manual: setiap item yang qty_refund > 0 harus punya kondisi
        foreach ($request->items ?? [] as $idx => $item) {
            $qty = floatval($item['qty_refund'] ?? 0);
            if ($qty > 0 && empty($item['kondisi'])) {
                return redirect()->back()->withErrors([
                    'error' => 'Kondisi barang pada baris ke-' . ($idx + 1) . ' harus dipilih.'
                ])->withInput();
            }
        }

        try {
            DB::beginTransaction();

            $pos = POS::with(['details.product', 'refunds'])->findOrFail($request->pos_id);

            // ── Proteksi retur ganda ──
            if ($pos->details()->count() === 0) {
                throw new \Exception('Transaksi ini sudah pernah diretur secara penuh. Tidak dapat memproses retur ulang.');
            }

            // ── Validasi batas waktu retur: maks 30 hari ──
            $hariSejaksTransaksi = now()->diffInDays($pos->tgl_transaksi, false);
            if ($hariSejaksTransaksi < -30) {
                throw new \Exception('Retur tidak dapat diproses. Transaksi ini sudah lebih dari 30 hari yang lalu (' . abs((int)$hariSejaksTransaksi) . ' hari).');
            }

            // ── Supervisor Approval untuk role Operator ──
            $currentUser = auth()->user();
            if ($currentUser->role === 'operator') {
                if (!$request->filled('supervisor_email') || !$request->filled('supervisor_password')) {
                    throw new \Exception('Proses retur oleh Operator memerlukan persetujuan Supervisor. Masukkan email & password supervisor.');
                }
                $supervisor = \App\Models\User::where('email', $request->supervisor_email)->first();
                if (!$supervisor || !in_array($supervisor->role, ['supervisor', 'owner'])) {
                    throw new \Exception('Email supervisor tidak valid atau bukan akun supervisor/owner.');
                }
                if (!\Illuminate\Support\Facades\Hash::check($request->supervisor_password, $supervisor->password)) {
                    throw new \Exception('Password supervisor salah. Retur dibatalkan.');
                }
            }

            // Generate nomor batch retur unik
            $no_refund_batch = 'RTR-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

            $total_nominal_refund = 0;
            $hasRefund = false;
            $created_refund_ids = [];

            foreach ($request->items as $item) {
                $qty_refund = floatval($item['qty_refund'] ?? 0);
                if ($qty_refund > 0) {
                    $detail = $pos->details()->where('id', $item['detail_id'])->first();
                    if (!$detail) continue;

                    if ($qty_refund > $detail->qty) {
                        $namaProduk = $detail->product?->nama ?? '(Produk tidak ditemukan)';
                        throw new \Exception("Qty retur untuk \"{$namaProduk}\" ({$qty_refund}) melebihi sisa qty yang tersedia ({$detail->qty}).");
                    }

                    $hasRefund = true;
                    $isi   = ($detail->isi ?? 0) > 0 ? $detail->isi : 1;
                    $harga = ($detail->harga_satuan ?? 0) > 0 ? $detail->harga_satuan : ($detail->harga ?? 0);

                    if ($harga <= 0) {
                        throw new \Exception('Harga satuan produk tidak valid (0). Hubungi administrator.');
                    }

                    $nominal_refund = $qty_refund * $harga;
                    $total_nominal_refund += $nominal_refund;

                    $kondisi     = $item['kondisi'] ?? 'layak';
                    $kondisiText = $kondisi === 'layak' ? 'Masuk Stok Jual' : 'Barang Rusak/Cacat';
                    $alasanLengkap = $request->alasan . ' (' . $kondisiText . ')';

                    // 1. Catat ke tabel pos_refunds
                    $ref = \Modules\POS\Models\POSRefund::create([
                        'pos_id'         => $pos->id,
                        'no_refund'      => $no_refund_batch,
                        'no_transaksi'   => $pos->no_transaksi,
                        'produk_id'      => $detail->produk_id,
                        'nama_produk'    => $detail->product ? $detail->product->nama : 'Produk',
                        'satuan_nama'    => $detail->satuan_nama ?? 'Pcs',
                        'qty_refund'     => $qty_refund,
                        'nominal_refund' => $nominal_refund,
                        'alasan'         => $alasanLengkap,
                        'kondisi'        => $kondisi,
                        'tgl_refund'     => now(),
                        'user_id'        => auth()->id(),
                    ]);

                    $created_refund_ids[] = $ref->id;

                    // 2. Jika kondisi LAYAK JUAL -> Kembalikan Stok Produk
                    if ($kondisi === 'layak' && $detail->product) {
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

            // 5. Activity log (sebelum commit agar konsisten)
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'no_transaksi' => $pos->no_transaksi,
                    'no_refund'    => $no_refund_batch,
                    'total_refund' => $total_nominal_refund,
                    'alasan'       => $request->alasan,
                    'jumlah_item'  => count($created_refund_ids),
                ])
                ->log("Retur {$no_refund_batch} untuk transaksi {$pos->no_transaksi}. Total Rp " . number_format($total_nominal_refund, 0, ',', '.'));

            DB::commit();

            return redirect()->route('pos.retur.receipt', $created_refund_ids[0])
                ->with('success', "Retur {$no_refund_batch} berhasil! Total refund: Rp " . number_format($total_nominal_refund, 0, ',', '.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function returReceipt($id)
    {
        $refund = \Modules\POS\Models\POSRefund::with(['pos.pelanggan', 'user', 'product'])->findOrFail($id);

        // Filter batch berdasarkan no_refund jika ada, fallback ke tgl_refund
        if ($refund->no_refund) {
            $batch_refunds = \Modules\POS\Models\POSRefund::with('product')
                ->where('pos_id', $refund->pos_id)
                ->where('no_refund', $refund->no_refund)
                ->get();
        } else {
            // Legacy: data lama tanpa no_refund, filter by timestamp
            $batch_refunds = \Modules\POS\Models\POSRefund::with('product')
                ->where('pos_id', $refund->pos_id)
                ->where('tgl_refund', $refund->tgl_refund)
                ->get();
        }

        return view('pos::retur_receipt', compact('refund', 'batch_refunds'));
    }

    public function updateStatus(Request $request, $id)
    {
        $pos = POS::findOrFail($id);

        $request->validate([
            'status_pembayaran' => 'required|in:lunas,sebagian,belum_bayar',
            'jumlah_bayar'      => 'nullable|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            $status = $request->status_pembayaran;
            $jumlah_bayar = $pos->jumlah_bayar;

            if ($status === 'lunas') {
                $jumlah_bayar = $pos->total_tagihan;
            } elseif ($status === 'belum_bayar') {
                $jumlah_bayar = 0;
            } elseif ($request->filled('jumlah_bayar')) {
                $jumlah_bayar = floatval($request->jumlah_bayar);
                if ($jumlah_bayar >= $pos->total_tagihan) {
                    $status = 'lunas';
                    $jumlah_bayar = $pos->total_tagihan;
                } elseif ($jumlah_bayar > 0) {
                    $status = 'sebagian';
                } else {
                    $status = 'belum_bayar';
                }
            }

            $pos->update([
                'status_pembayaran' => $status,
                'jumlah_bayar'      => $jumlah_bayar
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Status pembayaran transaksi #' . $pos->no_transaksi . ' berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Gagal mengubah status: ' . $e->getMessage()]);
        }
    }
}
