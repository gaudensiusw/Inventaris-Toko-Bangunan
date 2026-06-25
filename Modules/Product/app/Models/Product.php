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
        'aktif_grosir',
        'min_qty_grosir',
        'harga_grosir',
        'sku',
        'image'
    ];

    protected $casts = [
        'aktif_grosir' => 'boolean',
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

    public function latestOpname()
    {
        return $this->hasOne(\Modules\StockOpname\Models\StockOpname::class, 'produk_id')->latestOfMany();
    }
}
