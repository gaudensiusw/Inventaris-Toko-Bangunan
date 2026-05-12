<?php

namespace Modules\StockOpname\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Product\Models\Product;

class StockOpname extends Model
{
    use HasFactory;

    protected $table = 'stock_opname';

    protected $fillable = [
        'produk_id',
        'user_id',
        'stok_sistem',
        'stok_fisik',
        'selisih',
        'keterangan',
        'status'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'produk_id');
    }

    public function causer()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
