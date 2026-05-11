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
        $allProducts = Product::orderBy('nama')->get();
        
        $query = Product::with('category');

        if ($request->search) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        if ($request->category_id) {
            $query->where('kategori_id', $request->category_id);
        }

        $products = $query->orderBy('nama')->paginate(15)->withQueryString();
        
        return view('stockopname::index', compact('products', 'allProducts', 'categories'));
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
            $status = in_array($user->role, ['owner', 'admin']) ? 'approved' : 'pending';

            foreach ($request->opname_data as $data) {
                if ($data['stok_fisik'] === null || $data['stok_fisik'] === '') continue;

                $product = Product::findOrFail($data['id']);
                $stokFisik = (int)$data['stok_fisik'];
                $selisih = $stokFisik - $product->stok;

                // Create Opname Record
                StockOpname::create([
                    'produk_id'   => $product->id,
                    'user_id'     => $user->id,
                    'stok_sistem' => $product->stok,
                    'stok_fisik'  => $stokFisik,
                    'selisih'     => $selisih,
                    'keterangan'  => $data['keterangan'] ?? 'Bulk Opname',
                    'status'      => $status,
                ]);

                // Sync System Stock ONLY IF Approved (Owner/Admin)
                if ($status === 'approved') {
                    $product->update(['stok' => $stokFisik]);
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
        $history = StockOpname::with(['product', 'causer'])
            ->whereIn('status', ['approved', 'rejected'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('stockopname::history', compact('history'));
    }

    public function approval()
    {
        $pending = StockOpname::with(['product', 'causer'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();
            
        return view('stockopname::approval', compact('pending'));
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
            $product->update(['stok' => $opname->stok_fisik]);
            
            $opname->update(['status' => 'approved']);
            
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
            $opname->update(['status' => 'rejected']);
            return redirect()->back()->with('success', 'Pengajuan audit telah ditolak.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menolak.');
        }
    }
}
