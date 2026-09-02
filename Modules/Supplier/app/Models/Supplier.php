<?php

namespace Modules\Supplier\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Product\Models\Product;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'supplier';

    protected $fillable = [
        'company_name',
        'contact_person',
        'phone',
        'email',
        'city',
        'province',
        'address'
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'supplier_id');
    }

    public function pembelians()
    {
        return $this->hasMany(\Modules\Pembelian\Models\Pembelian::class, 'supplier_id');
    }
}
