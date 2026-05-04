<?php

namespace Modules\OperationalItem\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemOperasional extends Model
{
    use HasFactory;

    protected $table = 'item_operasional';

    protected $fillable = [
        'karyawan_id',
        'nama',
        'kategori',
        'jumlah',
        'satuan',
        'harga',
        'deskripsi',
        'tanggal_penggunaan',
        'tanggal_pembelian',
        'status'
    ];
}
