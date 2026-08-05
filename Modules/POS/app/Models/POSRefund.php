<?php

namespace Modules\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Product\Models\Product;

class POSRefund extends Model
{
    use HasFactory;

    protected $table = 'pos_refunds';

    protected $fillable = [
        'pos_id',
        'no_transaksi',
        'produk_id',
        'nama_produk',
        'qty_refund',
        'nominal_refund',
        'alasan',
        'tgl_refund',
        'user_id'
    ];

    protected $casts = [
        'tgl_refund' => 'datetime',
        'qty_refund' => 'float',
        'nominal_refund' => 'float'
    ];

    public function pos()
    {
        return $this->belongsTo(POS::class, 'pos_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'produk_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
