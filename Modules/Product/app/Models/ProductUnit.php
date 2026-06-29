<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductUnit extends Model
{
    use HasFactory;

    protected $table = 'produk_satuan';

    protected $fillable = [
        'produk_id',
        'nama',
        'isi',
        'target_satuan',
        'target_isi',
        'harga_jual',
        'is_base'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'produk_id');
    }
}
