<?php

namespace Modules\TagihanSupplier\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Supplier\Models\Supplier;

class TagihanSupplier extends Model
{
    use HasFactory;

    protected $table = 'tagihan_supplier';

    protected $fillable = [
        'supplier_id',
        'no_invoice',
        'tgl_invoice',
        'jatuh_tempo',
        'total',
        'status',
        'catatan'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
