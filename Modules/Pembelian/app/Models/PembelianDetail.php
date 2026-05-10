<?php

namespace Modules\Pembelian\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Pembelian\Database\Factories\PembelianDetailFactory;

class PembelianDetail extends Model
{
    use HasFactory;

    protected $table = 'pembelian_details';

    protected $fillable = [
        'pembelian_id',
        'produk_id',
        'satuan',
        'qty',
        'isi_per_satuan',
        'harga_total'
    ];

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'pembelian_id');
    }

    public function product()
    {
        return $this->belongsTo(\Modules\Product\Models\Product::class, 'produk_id');
    }
}
