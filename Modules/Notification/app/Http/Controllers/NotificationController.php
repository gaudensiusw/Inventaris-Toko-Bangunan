<?php

namespace Modules\Notification\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = [
            [
                'type' => 'Stok Rendah',
                'message' => 'Stok Semen Gresik sisa 5 sak. Segera lakukan reorder!',
                'time' => '2 jam yang lalu',
                'unread' => true
            ],
            [
                'type' => 'Tagihan',
                'message' => 'Tagihan Supplier PT Karya Besi jatuh tempo dalam 2 hari.',
                'time' => '5 jam yang lalu',
                'unread' => true
            ],
            [
                'type' => 'Sistem',
                'message' => 'Backup data otomatis berhasil diselesaikan.',
                'time' => 'Kemarin, 23:00',
                'unread' => false
            ],
            [
                'type' => 'Penjualan',
                'message' => 'Transaksi baru berhasil dilakukan oleh Ahmad Faisal (INV-001).',
                'time' => '17 Mar, 08:30',
                'unread' => false
            ],
        ];

        return view('notification::index', compact('notifications'));
    }
}
