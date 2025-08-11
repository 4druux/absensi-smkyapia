<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermasalahanSiswa extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id',
        'tahun',
        'tanggal',
        'masalah',
        'tindakan_walas',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}