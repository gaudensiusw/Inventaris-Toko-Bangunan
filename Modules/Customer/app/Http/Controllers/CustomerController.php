<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Modules\Customer\Models\Customer;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::withSum(['transactions as total_hutang' => function($query) {
            $query->where('status_pembayaran', '!=', 'lunas');
        }], 'total_tagihan')
        ->withSum(['transactions as total_dibayar' => function($query) {
            $query->where('status_pembayaran', '!=', 'lunas');
        }], 'jumlah_bayar');

        if ($request->get('filter') === 'hutang') {
            $query->whereHas('transactions', function($q) {
                $q->where('status_pembayaran', '!=', 'lunas');
            });
        }

        $customers = $query->latest()->get();

        // Calculate global stats
        $stats = [
            'total_piutang' => \Modules\POS\Models\POS::where('status_pembayaran', '!=', 'lunas')->sum(\DB::raw('total_tagihan - jumlah_bayar')),
            'jatuh_tempo'   => \Modules\POS\Models\POS::where('status_pembayaran', '!=', 'lunas')
                                ->where('jatuh_tempo', '<', now())
                                ->sum(\DB::raw('total_tagihan - jumlah_bayar')),
            'belum_bayar_count' => \Modules\POS\Models\POS::where('status_pembayaran', 'belum_bayar')->count(),
            'sebagian_count'    => \Modules\POS\Models\POS::where('status_pembayaran', 'sebagian')->count(),
            'aktif_count'       => Customer::whereHas('transactions', function($q) {
                $q->where('status_pembayaran', '!=', 'lunas');
            })->count(),
            
            // General Stats
            'total_pelanggan' => Customer::count(),
            'kontraktor_count' => Customer::where('kategori', 'Kontraktor')->count(),
            'tukang_count'     => Customer::where('kategori', 'Tukang')->count(),
            'umum_retail_count' => Customer::where(function($q) {
                                    $q->whereIn('kategori', ['Umum', 'Retail'])
                                      ->orWhereNull('kategori');
                                  })->count(),
        ];

        $selected_id = $request->id ?: ($customers->first()->id ?? null);
        $selected_customer = $selected_id ? Customer::with(['transactions' => function($q) {
            $q->latest();
        }, 'transactions.details.product', 'transactions.refunds'])->find($selected_id) : null;

        $products = \Modules\Product\Models\Product::select('id', 'nama', 'unit', 'harga_jual', 'stok')->orderBy('nama')->get();

        return view('customer::index', compact('customers', 'stats', 'selected_customer', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'         => 'required|string|max:255',
            'email'        => 'nullable|email|max:255',
            'kategori'     => 'nullable|string|max:255',
            'telp'         => 'nullable|string|max:20',
            'alamat'       => 'nullable|string',
            'limit_kredit' => 'nullable|numeric|min:0',
            'tenor_bayar'  => 'nullable|integer|min:0',
        ]);

        $validated['kode'] = 'CUST-' . strtoupper(bin2hex(random_bytes(2)));

        Customer::create($validated);
        return redirect()->back()->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $validated = $request->validate([
            'nama'         => 'required|string|max:255',
            'email'        => 'nullable|email|max:255',
            'kategori'     => 'nullable|string|max:255',
            'telp'         => 'nullable|string|max:20',
            'alamat'       => 'nullable|string',
            'limit_kredit' => 'nullable|numeric|min:0',
            'tenor_bayar'  => 'nullable|integer|min:0',
        ]);

        $customer->update($validated);
        return redirect()->back()->with('success', 'Pelanggan berhasil diperbarui.');
    }

    public function payTransaction(Request $request, $id)
    {
        $pos = \Modules\POS\Models\POS::findOrFail($id);
        $request->validate([
            'jumlah_bayar' => 'required|numeric|min:0',
            'catatan'      => 'nullable|string'
        ]);

        $newTotalBayar = $pos->jumlah_bayar + $request->jumlah_bayar;
        
        if ($newTotalBayar >= $pos->total_tagihan) {
            $pos->update([
                'jumlah_bayar' => $pos->total_tagihan,
                'status_pembayaran' => 'lunas'
            ]);
        } else {
            $pos->update([
                'jumlah_bayar' => $newTotalBayar,
                'status_pembayaran' => 'sebagian'
            ]);
        }

        return redirect()->back()->with('success', 'Pembayaran berhasil dicatat.');
    }

    public function topUpDeposit(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $request->validate([
            'nominal' => 'required|numeric|min:1',
            'catatan' => 'nullable|string',
        ]);

        $customer->increment('deposit', $request->nominal);

        return redirect()->back()->with('success', 'Deposit sebesar Rp ' . number_format($request->nominal, 0, ',', '.') . ' berhasil ditambahkan untuk ' . $customer->nama . '.');
    }

    public function payWithDeposit(Request $request, $id)
    {
        $pos = \Modules\POS\Models\POS::with('pelanggan')->findOrFail($id);
        $customer = $pos->pelanggan;

        if (!$customer) {
            return redirect()->back()->withErrors(['error' => 'Pelanggan tidak ditemukan.']);
        }

        $sisaHutang = $pos->total_tagihan - $pos->jumlah_bayar;
        if ($sisaHutang <= 0) {
            return redirect()->back()->withErrors(['error' => 'Transaksi ini sudah lunas.']);
        }

        $jumlahBayar = min($customer->deposit, $sisaHutang);
        if ($jumlahBayar <= 0) {
            return redirect()->back()->withErrors(['error' => 'Saldo deposit pelanggan Rp 0 atau tidak mencukupi.']);
        }

        \DB::transaction(function() use ($customer, $pos, $jumlahBayar) {
            $customer->decrement('deposit', $jumlahBayar);

            $newTotalBayar = $pos->jumlah_bayar + $jumlahBayar;
            if ($newTotalBayar >= $pos->total_tagihan) {
                $pos->update([
                    'jumlah_bayar' => $pos->total_tagihan,
                    'status_pembayaran' => 'lunas'
                ]);
            } else {
                $pos->update([
                    'jumlah_bayar' => $newTotalBayar,
                    'status_pembayaran' => 'sebagian'
                ]);
            }
        });

        return redirect()->back()->with('success', 'Pembayaran sebesar Rp ' . number_format($jumlahBayar, 0, ',', '.') . ' dari deposit berhasil dipotongkan ke Bon #' . $pos->no_transaksi . '.');
    }

    public function updateTransaction(Request $request, $id)
    {
        $pos = \Modules\POS\Models\POS::with('details.product')->findOrFail($id);

        $request->validate([
            'items'             => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:produk,id',
            'items.*.qty'       => 'required|numeric|min:0.01',
            'items.*.harga'     => 'required|numeric|min:0',
        ]);

        try {
            \DB::beginTransaction();

            // 1. Kembalikan stok lama
            foreach ($pos->details as $oldDetail) {
                if ($oldDetail->product) {
                    $isi = $oldDetail->isi > 0 ? $oldDetail->isi : 1;
                    $stok_kembali = $oldDetail->qty * $isi;
                    $oldDetail->product->increment('stok', $stok_kembali);
                }
            }

            // 2. Hapus detail lama
            $pos->details()->delete();

            // 3. Simpan detail baru & kurangi stok produk pengganti / baru
            $subtotal = 0;
            foreach ($request->items as $item) {
                $product = \Modules\Product\Models\Product::findOrFail($item['produk_id']);
                
                $isi = 1;
                $satuan_nama = $product->unit ?: 'Pcs';
                $stok_kurang = $item['qty'] * $isi;

                if ($product->stok < $stok_kurang) {
                    throw new \Exception("Stok produk {$product->nama} tidak mencukupi untuk perubahan ini (Sisa: {$product->stok}).");
                }

                $item_subtotal = $item['qty'] * $item['harga'];
                $subtotal += $item_subtotal;

                \Modules\POS\Models\POSDetail::create([
                    'pos_id'       => $pos->id,
                    'produk_id'    => $product->id,
                    'satuan_nama'  => $satuan_nama,
                    'isi'          => $isi,
                    'harga_satuan' => $item['harga'],
                    'diskon_rp'    => 0,
                    'qty'          => $item['qty'],
                    'harga'        => $item['harga'],
                    'subtotal'     => $item_subtotal,
                ]);

                $product->decrement('stok', $stok_kurang);
            }

            // 4. Hitung ulang total tagihan
            $total_tagihan = $subtotal + ($pos->pajak ?: 0) + ($pos->ongkos_kirim ?: 0);

            // 5. Sesuaikan status pembayaran dan jumlah bayar
            $jumlah_bayar = $pos->jumlah_bayar;
            $status_pembayaran = $pos->status_pembayaran;

            if ($pos->metode_pembayaran === 'Bon') {
                if ($jumlah_bayar >= $total_tagihan && $total_tagihan > 0) {
                    $status_pembayaran = 'lunas';
                    if ($jumlah_bayar > $total_tagihan) {
                        $jumlah_bayar = $total_tagihan;
                    }
                } elseif ($jumlah_bayar > 0) {
                    $status_pembayaran = 'sebagian';
                } else {
                    $status_pembayaran = 'belum_bayar';
                }
            } else {
                $jumlah_bayar = $total_tagihan;
                $status_pembayaran = 'lunas';
            }

            $pos->update([
                'subtotal'          => $subtotal,
                'total_tagihan'     => $total_tagihan,
                'jumlah_bayar'      => $jumlah_bayar,
                'status_pembayaran' => $status_pembayaran,
            ]);

            \DB::commit();

            return redirect()->back()->with('success', 'Transaksi #'.$pos->no_transaksi.' berhasil diperbarui (stok dan tagihan otomatis disesuaikan).');
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Gagal mengubah transaksi: ' . $e->getMessage()]);
        }
    }

    public function processRefund(Request $request, $id)
    {
        $pos = \Modules\POS\Models\POS::with(['details.product', 'refunds'])->findOrFail($id);

        $request->validate([
            'items'            => 'required|array',
            'items.*.qty_refund' => 'nullable|numeric|min:0',
            'alasan'           => 'nullable|string',
        ]);

        try {
            \DB::beginTransaction();

            $total_nominal_refund = 0;
            $hasRefundItem = false;

            foreach ($request->items as $detail_id => $itemData) {
                $qty_refund = floatval($itemData['qty_refund'] ?? 0);
                if ($qty_refund > 0) {
                    $detail = $pos->details()->where('id', $detail_id)->first();
                    if (!$detail) continue;

                    if ($qty_refund > $detail->qty) {
                        throw new \Exception("Qty refund untuk produk " . ($detail->product->nama ?? '') . " tidak boleh melebihi qty pembelian ({$detail->qty}).");
                    }

                    $hasRefundItem = true;
                    $isi = $detail->isi > 0 ? $detail->isi : 1;
                    $harga = $detail->harga_satuan > 0 ? $detail->harga_satuan : $detail->harga;
                    $nominal_refund = $qty_refund * $harga;
                    $total_nominal_refund += $nominal_refund;

                    // 1. Catat ke tabel pos_refunds
                    \Modules\POS\Models\POSRefund::create([
                        'pos_id'         => $pos->id,
                        'no_transaksi'   => $pos->no_transaksi,
                        'produk_id'      => $detail->produk_id,
                        'nama_produk'    => $detail->product ? $detail->product->nama : 'Produk',
                        'qty_refund'     => $qty_refund,
                        'nominal_refund' => $nominal_refund,
                        'alasan'         => $request->alasan ?: 'Salah Beli / Retur',
                        'tgl_refund'     => now(),
                        'user_id'        => auth()->id(),
                    ]);

                    // 2. Kembalikan stok
                    if ($detail->product) {
                        $detail->product->increment('stok', $qty_refund * $isi);
                    }

                    // 3. Kurangi Qty & Subtotal di Detail
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

            if (!$hasRefundItem) {
                throw new \Exception("Silakan isi minimal 1 barang dengan jumlah Qty yang ingin di-refund.");
            }

            // 4. Hitung ulang total transaksi POS
            $new_subtotal = $pos->details()->sum('subtotal');
            $new_total_tagihan = $new_subtotal + ($pos->pajak ?: 0) + ($pos->ongkos_kirim ?: 0);

            // 5. Sesuaikan pembayaran / sisa hutang
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
                'status_pembayaran' => $status_pembayaran
            ]);

            \DB::commit();
            return redirect()->back()->with('success', 'Refund / Retur sebesar Rp ' . number_format($total_nominal_refund, 0, ',', '.') . ' berhasil diproses dan dicatat.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Gagal memproses refund: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
        return redirect()->back()->with('success', 'Pelanggan berhasil dihapus.');
    }
}
