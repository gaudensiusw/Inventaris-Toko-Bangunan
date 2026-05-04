<?php

namespace Modules\Notification\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\Product;
use Modules\TagihanSupplier\Models\TagihanSupplier;
use Modules\POS\Models\POS;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function index()
    {
        // 1. Calculate Real-time Counts
        $counts = [
            'stok_rendah' => Product::whereRaw('stok <= min_stok')->where('stok', '>', 0)->count(),
            'tagihan'     => TagihanSupplier::where('status', '!=', 'lunas')->count(),
            'penjualan'   => POS::whereDate('created_at', Carbon::today())->count(),
            'sistem'      => 2, // Hardcoded for now
        ];

        // 2. Generate Notification Items (Semi-Dynamic)
        $notifications = [];

        // Add Low Stock Notifications
        $lowStockProducts = Product::whereRaw('stok <= min_stok')->where('stok', '>', 0)->limit(5)->get();
        foreach ($lowStockProducts as $p) {
            $notifications[] = [
                'type' => 'Stok Rendah',
                'message' => "Stok {$p->nama} menipis (Sisa: {$p->stok} {$p->unit}). Segera lakukan reorder!",
                'time' => $p->updated_at->diffForHumans(),
                'unread' => true,
                'category' => 'stok_rendah'
            ];
        }

        // Add Bill Notifications
        $pendingBills = TagihanSupplier::where('status', '!=', 'lunas')->orderBy('jatuh_tempo', 'asc')->limit(5)->get();
        foreach ($pendingBills as $b) {
            $isOverdue = Carbon::parse($b->jatuh_tempo)->isPast();
            $notifications[] = [
                'type' => 'Tagihan',
                'message' => "Tagihan {$b->no_invoice} (" . ($b->supplier->company_name ?? 'Supplier') . ") " . ($isOverdue ? 'telah melewati jatuh tempo!' : 'jatuh tempo pada ' . Carbon::parse($b->jatuh_tempo)->format('d M Y')),
                'time' => $b->created_at->diffForHumans(),
                'unread' => true,
                'category' => 'tagihan'
            ];
        }

        // Add Sales Notifications
        $recentSales = POS::latest()->limit(5)->get();
        foreach ($recentSales as $s) {
            $notifications[] = [
                'type' => 'Penjualan',
                'message' => "Transaksi baru {$s->no_transaksi} oleh " . ($s->nama_pelanggan ?: 'Umum') . " sebesar Rp " . number_format($s->total_tagihan, 0, ',', '.'),
                'time' => $s->created_at->diffForHumans(),
                'unread' => false,
                'category' => 'penjualan'
            ];
        }

        // Add System Notifications
        $notifications[] = [
            'type' => 'Sistem',
            'message' => 'Backup data harian telah berhasil dijalankan secara otomatis pada pukul 00:00.',
            'time' => '12 jam yang lalu',
            'unread' => false,
            'category' => 'sistem'
        ];
        $notifications[] = [
            'type' => 'Sistem',
            'message' => 'Pembaruan sistem IMS v1.1 telah tersedia. Silakan cek menu pemeliharaan.',
            'time' => 'Kemarin',
            'unread' => false,
            'category' => 'sistem'
        ];

        // Ensure sequential array for JSON
        $notifications = array_values($notifications);
        
        return view('notification::index', compact('notifications', 'counts'));
    }
}
