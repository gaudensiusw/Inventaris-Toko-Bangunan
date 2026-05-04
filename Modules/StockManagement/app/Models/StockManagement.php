<?php

namespace Modules\StockManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Product\Models\Product;

class StockManagement extends Model
{
    use HasFactory;

    protected $table = 'stock_management';

    protected $fillable = [
        'produk_id',
        'tipe',
        'qty',
        'keterangan'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'produk_id');
    }
}
