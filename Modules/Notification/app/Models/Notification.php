<?php

namespace Modules\Notification\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'message',
        'is_read',
        'category'
    ];

    protected $casts = [
        'is_read' => 'boolean'
    ];
}
