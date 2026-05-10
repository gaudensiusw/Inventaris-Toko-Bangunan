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

    public function details()
    {
        return $this->hasMany(POSDetail::class, 'pos_id');
    }

    public function pelanggan()
    {
        return $this->belongsTo(Customer::class, 'pelanggan_id');
    }
}
