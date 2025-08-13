<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property \Carbon\Carbon $tanggal
 * @property \Carbon\Carbon|null $paid_at
 */
class UangKasOther extends Model
{
    use HasFactory;

    protected $table = 'uang_kas_others';

    protected $fillable = [
        'iuran_id',
        'siswa_id',
        'nominal',
        'status',
        'tanggal',
        'paid_at',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'tanggal' => 'date',
        'paid_at' => 'datetime',
    ];

    public function iuran()
    {
        return $this->belongsTo(Iuran::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}