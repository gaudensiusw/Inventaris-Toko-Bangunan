<?php

namespace Modules\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Customer\Models\Customer;

class POS extends Model
{
    use HasFactory;

    protected $table = 'pos';

    protected $fillable = [
        'user_id',
        'no_transaksi',
        'tgl_transaksi',
        'pelanggan_id',
        'nama_pelanggan',
        'subtotal',
        'pajak',
        'ongkos_kirim',
        'total_tagihan',
        'jumlah_bayar',
        'jatuh_tempo',
        'metode_pembayaran',
        'opsi_pengiriman',
        'catatan',
        'status',
        'status_pembayaran'
    ];

    protected $casts = [
        'tgl_transaksi' => 'datetime',
        'jatuh_tempo'   => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function details()
    {
        return $this->hasMany(POSDetail::class, 'pos_id');
    }

    public function pelanggan()
    {
        return $this->belongsTo(Customer::class, 'pelanggan_id');
    }

    public function refunds()
    {
        return $this->hasMany(POSRefund::class, 'pos_id');
    }
}
