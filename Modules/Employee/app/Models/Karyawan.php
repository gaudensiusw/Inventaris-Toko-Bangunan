<?php

namespace Modules\Employee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Karyawan extends Model
{
    use HasFactory;

    protected $table = 'karyawan';

    protected $fillable = [
        'kode_karyawan',
        'jabatan_id',
        'nama',
        'no_hp',
        'email',
        'alamat',
        'tanggal_masuk',
        'aktif',
        'bonus_tetap'
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
        'aktif' => 'boolean',
    ];

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    public function absensis()
    {
        return $this->hasMany(Absensi::class, 'karyawan_id');
    }
}
