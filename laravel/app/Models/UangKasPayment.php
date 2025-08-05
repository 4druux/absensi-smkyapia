<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UangKasPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id',
        'tahun',
        'bulan_slug',
        'minggu',
        'nominal',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}