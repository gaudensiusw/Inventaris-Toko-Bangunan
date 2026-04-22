<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Supplier\Models\Supplier;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'name',
        'sku',
        'category',
        'stock',
        'unit',
        'purchase_price',
        'selling_price',
        'min_stock'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
