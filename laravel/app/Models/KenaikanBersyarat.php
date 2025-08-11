<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KenaikanBersyarat extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id',
        'tahun',
        'jumlah_nilai_kurang',
        'akhlak',
        'rekomendasi_walas',
        'keputusan_akhir',
    ];
}