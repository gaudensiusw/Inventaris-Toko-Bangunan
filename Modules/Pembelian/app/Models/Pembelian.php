<?php

namespace Modules\Pembelian\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Pembelian\Database\Factories\PembelianFactory;

class Pembelian extends Model
{
    use HasFactory;

    protected $table = 'pembelians';

    protected $fillable = [
        'no_transaksi',
        'tgl_pembelian',
        'supplier_id',
        'total_pembelian',
        'status',
        'catatan'
    ];

    public function supplier()
    {
        return $this->belongsTo(\Modules\Supplier\Models\Supplier::class, 'supplier_id');
    }

    public function details()
    {
        return $this->hasMany(PembelianDetail::class, 'pembelian_id');
    }
}
