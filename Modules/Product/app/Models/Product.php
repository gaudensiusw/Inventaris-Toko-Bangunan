<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Supplier\Models\Supplier;

class Product extends Model
{
    use HasFactory;

    protected $table = 'produk';

    protected $fillable = [
        'nama',
        'merk',
        'kategori_id',
        'sub_kategori_id',
        'supplier_id',
        'stok',
        'unit',
        'min_stok',
        'harga_beli',
        'harga_jual',
        'sku',
        'image'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'kategori_id');
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class, 'sub_kategori_id');
    }

    public function units()
    {
        return $this->hasMany(ProductUnit::class, 'produk_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
