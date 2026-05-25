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
        }, 'transactions.details.product'])->find($selected_id) : null;

        return view('customer::index', compact('customers', 'stats', 'selected_customer'));
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

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
        return redirect()->back()->with('success', 'Pelanggan berhasil dihapus.');
    }
}
