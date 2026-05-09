<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $table = 'kategori';

    protected $fillable = [
        'nama'
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'kategori_id');
    }

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class, 'kategori_id');
    }
}
