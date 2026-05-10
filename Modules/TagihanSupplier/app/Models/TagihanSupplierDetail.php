<?php

namespace Modules\TagihanSupplier\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\TagihanSupplier\Database\Factories\TagihanSupplierDetailFactory;

class TagihanSupplierDetail extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): TagihanSupplierDetailFactory
    // {
    //     // return TagihanSupplierDetailFactory::new();
    // }
}
