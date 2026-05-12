<?php

namespace Modules\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Product\Models\Product;

class POSDetail extends Model
{
    use HasFactory;

    protected $table = 'pos_detail';

    protected $fillable = [
        'pos_id',
        'produk_id',
        'satuan_nama',
        'isi',
        'harga_satuan',
        'diskon_rp',
        'qty',
        'harga',
        'subtotal'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'produk_id');
    }

    public function pos()
    {
        return $this->belongsTo(POS::class, 'pos_id');
    }
}
