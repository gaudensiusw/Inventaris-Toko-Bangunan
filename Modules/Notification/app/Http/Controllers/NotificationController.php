<?php

namespace Modules\Notification\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\Product;
use Modules\TagihanSupplier\Models\TagihanSupplier;
use Modules\POS\Models\POS;
use Carbon\Carbon;

use Modules\Notification\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        // Populate dummy notifications if table is empty
        if (Notification::count() === 0) {
            $this->seedInitialNotifications();
        }

        $counts = [
<<<<<<< HEAD
            'stok_rendah' => Notification::where('category', 'stok_rendah')->where('is_read', false)->count(),
            'tagihan'     => Notification::where('category', 'tagihan')->where('is_read', false)->count(),
            'penjualan'   => Notification::where('category', 'penjualan')->where('is_read', false)->count(),
            'sistem'      => Notification::where('category', 'sistem')->where('is_read', false)->count(),
        ];

        $notifications = Notification::latest()->get();
=======
            'stok_rendah' => Product::whereRaw('stok <= min_stok')->where('stok', '>', 0)->count(),
            'tagihan'     => TagihanSupplier::where('status', '!=', 'lunas')->count(),
            'penjualan'   => POS::whereDate('created_at', Carbon::today())->count(),
            'audit'       => \Modules\StockOpname\Models\StockOpname::where('status', 'pending')->count(),
            'sistem'      => 0,
        ];

        // 2. Generate Notification Items (Semi-Dynamic)
        $notifications = [];

        // Add Pending Audit Notifications
        $pendingAudits = \Modules\StockOpname\Models\StockOpname::where('status', 'pending')->with('product')->get();
        foreach ($pendingAudits as $a) {
            $notifications[] = [
                'type' => 'Persetujuan Audit',
                'message' => "Pengajuan audit baru untuk " . ($a->product->nama ?? 'Produk') . " dengan selisih " . $a->selisih . ". Perlu verifikasi Owner.",
                'time' => $a->created_at->diffForHumans(),
                'unread' => true,
                'category' => 'audit',
                'url' => route('stockopname.approval')
            ];
        }

        // Add Low Stock Notifications
        $lowStockProducts = Product::whereRaw('stok <= min_stok')->where('stok', '>', 0)->get();
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
        $pendingBills = TagihanSupplier::where('status', '!=', 'lunas')->orderBy('jatuh_tempo', 'asc')->get();
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
        $recentSales = POS::latest()->get();
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
>>>>>>> a9d62d13c233a530489e43b58f979081d8b92444
        
        return view('notification::index', compact('notifications', 'counts'));
    }

<<<<<<< HEAD
    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    private function seedInitialNotifications()
    {
        Notification::create([
            'type' => 'Stok Rendah',
            'message' => 'Stok Semen Portland - Gresik menipis (Sisa: 5 Sak). Segera lakukan reorder!',
            'category' => 'stok_rendah'
        ]);

        Notification::create([
            'type' => 'Tagihan',
            'message' => 'Tagihan INV-2026-018 (PT Semen Indonesia) jatuh tempo pada 10 Mei 2026',
            'category' => 'tagihan'
        ]);

        Notification::create([
            'type' => 'Penjualan',
            'message' => 'Transaksi baru TRX-20260507-A1B2 oleh Budi Santoso sebesar Rp 750.000',
            'category' => 'penjualan'
        ]);

        Notification::create([
            'type' => 'Sistem',
            'message' => 'Backup data harian telah berhasil dijalankan secara otomatis.',
            'category' => 'sistem'
        ]);
    }
=======
    public function markAllAsRead()
    {
        auth()->user()->update([
            'last_read_notifications_at' => now()
        ]);

        return response()->json(['success' => true]);
    }
>>>>>>> a9d62d13c233a530489e43b58f979081d8b92444
}
