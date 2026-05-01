<?php

namespace Modules\Employee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Jabatan extends Model
{
    use HasFactory;

    protected $table = 'jabatan';

    protected $fillable = [
        'nama_jabatan',
        'gaji_harian',
        'uang_makan',
        'uang_pulsa'
    ];

    public function karyawans()
    {
        return $this->hasMany(Karyawan::class, 'jabatan_id');
    }
}
