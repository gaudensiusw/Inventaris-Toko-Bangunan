<?php

namespace Modules\StockOpname\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Modules\Product\Models\Product;
use Modules\StockOpname\Models\StockOpname;
use Illuminate\Support\Facades\DB;

class StockOpnameController extends Controller
{
    public function index(Request $request)
    {
        $categories = \Modules\Product\Models\Category::orderBy('nama')->get();
        
        $stats = [
            'total' => Product::count(),
            'tersedia' => Product::where('stok', '>', 0)->count(),
            'kosong' => Product::where('stok', '<=', 0)->count(),
            'menipis' => Product::where('stok', '<=', 5)->where('stok', '>', 0)->count(),
        ];
        
        $query = Product::with(['category', 'latestOpname.causer']);

        if ($request->search) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        if ($request->category_id) {
            $query->where('kategori_id', $request->category_id);
        }

        $products = $query->orderBy('nama')->paginate(15)->withQueryString();
        
        return view('stockopname::index', compact('products', 'stats', 'categories'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'opname_data' => 'required|array',
            'opname_data.*.id' => 'required|exists:produk,id',
            'opname_data.*.stok_fisik' => 'nullable|integer|min:0',
            'opname_data.*.keterangan' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $processedCount = 0;
            $user = auth()->user();
            $status = in_array($user->role, ['owner', 'supervisor']) ? 'approved' : 'pending';

            foreach ($request->opname_data as $data) {
                if ($data['stok_fisik'] === null || $data['stok_fisik'] === '') continue;

                $product = Product::findOrFail($data['id']);
                $stokFisik = (int)$data['stok_fisik'];
                $selisih = $stokFisik - $product->stok;

                // Create Opname Record
                $opnameRecord = StockOpname::create([
                    'produk_id'   => $product->id,
                    'user_id'     => $user->id,
                    'stok_sistem' => $product->stok,
                    'stok_fisik'  => $stokFisik,
                    'selisih'     => $selisih,
                    'keterangan'  => $data['keterangan'] ?? 'Bulk Opname',
                    'status'      => $status,
                ]);

                // Sync System Stock ONLY IF Approved (Owner/Supervisor)
                if ($status === 'approved') {
                    $product->update(['stok' => $stokFisik]);
                }

                // Log aktivitas CREATED per produk
                if (function_exists('activity')) {
                    activity('StockOpname')
                        ->performedOn($opnameRecord)
                        ->causedBy($user)
                        ->event('created')
                        ->withProperties([
                            'attributes' => [
                                'produk'      => $product->nama,
                                'stok_sistem' => $opnameRecord->stok_sistem,
                                'stok_fisik'  => $stokFisik,
                                'selisih'     => $selisih,
                                'status'      => $status,
                                'keterangan'  => $opnameRecord->keterangan,
                            ]
                        ])
                        ->log("Stock Opname '{$product->nama}': sistem={$opnameRecord->stok_sistem}, fisik={$stokFisik}, selisih={$selisih} [{$status}]");
                }
                
                $processedCount++;
            }

            if ($processedCount === 0) {
                throw new \Exception('Tidak ada data stok fisik yang diisi.');
            }

            DB::commit();
            
            $msg = $status === 'approved' 
                ? "$processedCount produk berhasil diperbarui." 
                : "$processedCount pengajuan audit stok telah dikirim ke Supervisor.";
                
            return redirect()->back()->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses: ' . $e->getMessage());
        }
    }

    public function history()
    {
        $user = auth()->user();
        $query = StockOpname::with(['product', 'causer']);

        if (in_array($user->role, ['owner', 'supervisor'])) {
            // Only show approved opnames (actual item changes)
            $query->where('status', 'approved');
        } else {
            // Operator sees their own history, including pending
            $query->where('user_id', $user->id);
        }

        $history = $query->orderBy('updated_at', 'desc')->paginate(20);
            
        return view('stockopname::history', compact('history'));
    }

    public function approval()
    {
        $pending = StockOpname::with(['product', 'causer'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();
            
        $history = StockOpname::with(['product', 'causer'])
            ->whereIn('status', ['approved', 'rejected'])
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        return view('stockopname::approval', compact('pending', 'history'));
    }

    public function approve($id)
    {
        DB::beginTransaction();
        try {
            $opname = StockOpname::findOrFail($id);
            if ($opname->status !== 'pending') {
                return redirect()->back()->with('error', 'Data ini sudah diproses.');
            }

            // Sync stock
            $product = $opname->product;
            $stokLama = $product->stok;
            $product->update(['stok' => $opname->stok_fisik]);
            
            $opname->update(['status' => 'approved']);

            // Log aktivitas APPROVE
            if (function_exists('activity')) {
                activity('StockOpname')
                    ->performedOn($opname)
                    ->causedBy(auth()->user())
                    ->event('updated')
                    ->withProperties([
                        'old'        => ['status' => 'pending', 'stok_produk' => $stokLama],
                        'attributes' => ['status' => 'approved', 'stok_produk' => $opname->stok_fisik],
                    ])
                    ->log("Pengajuan Stock Opname untuk '{$product->nama}' disetujui. Stok diperbarui: {$stokLama} → {$opname->stok_fisik}.");
            }
            
            DB::commit();
            return redirect()->back()->with('success', 'Pengajuan audit berhasil disetujui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyetujui: ' . $e->getMessage());
        }
    }

    public function reject($id)
    {
        try {
            $opname = StockOpname::findOrFail($id);
            $productName = $opname->product->nama ?? 'Produk #' . $opname->produk_id;
            $opname->update(['status' => 'rejected']);

            // Log aktivitas REJECT
            if (function_exists('activity')) {
                activity('StockOpname')
                    ->performedOn($opname)
                    ->causedBy(auth()->user())
                    ->event('updated')
                    ->withProperties([
                        'old'        => ['status' => 'pending'],
                        'attributes' => ['status' => 'rejected'],
                    ])
                    ->log("Pengajuan Stock Opname untuk '{$productName}' ditolak.");
            }

            return redirect()->back()->with('success', 'Pengajuan audit telah ditolak.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menolak.');
        }
    }
}
