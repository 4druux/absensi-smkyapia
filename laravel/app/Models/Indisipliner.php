<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Indisipliner extends Model
{
    use HasFactory;

    protected $table = 'indisipliner';

    protected $fillable = [
        'siswa_id',
        'tahun',
        'jenis_surat',
        'nomor_surat',
        'tanggal_surat',
    ];
    
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }
    
    public function details(): HasMany
    {
        return $this->hasMany(IndisiplinerDetail::class);
    }
}