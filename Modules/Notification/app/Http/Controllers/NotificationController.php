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
            'stok_rendah' => Notification::where('category', 'stok_rendah')->where('is_read', false)->count(),
            'tagihan'     => Notification::where('category', 'tagihan')->where('is_read', false)->count(),
            'penjualan'   => Notification::where('category', 'penjualan')->where('is_read', false)->count(),
            'sistem'      => Notification::where('category', 'sistem')->where('is_read', false)->count(),
        ];

        $notifications = Notification::latest()->get();
        
        return view('notification::index', compact('notifications', 'counts'));
    }

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
}
