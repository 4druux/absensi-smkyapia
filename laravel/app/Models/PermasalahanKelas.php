<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermasalahanKelas extends Model
{
    use HasFactory;

    protected $table = 'permasalahan_kelas';

    protected $fillable = [
        'kelas_id',
        'tahun',
        'tanggal',
        'masalah',
        'pemecahan',
        'keterangan',
    ];
}