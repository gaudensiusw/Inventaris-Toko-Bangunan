<?php

namespace Modules\Employee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Jabatan extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Jabatan');
    }

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
