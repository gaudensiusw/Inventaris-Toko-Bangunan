<?php

namespace Modules\Employee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Karyawan extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Karyawan');
    }

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
