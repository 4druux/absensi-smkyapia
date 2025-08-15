<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiswaNoteRekap extends Model
{
    use HasFactory;

    protected $table = 'siswa_note_rekaps';

    protected $fillable = [
        'siswa_id',
        'tahun',
        'bulan_slug',
        'poin_tambahan',
        'keterangan',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}