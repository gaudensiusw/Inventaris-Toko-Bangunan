<?php

namespace Modules\Customer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'pelanggan';

    protected $fillable = [
        'kode',
        'nama',
        'email',
        'kategori',
        'telp',
        'alamat',
        'limit_kredit',
        'tenor_bayar',
        'status'
    ];

    public function transactions()
    {
        return $this->hasMany(\Modules\POS\Models\POS::class, 'pelanggan_id');
    }
}
