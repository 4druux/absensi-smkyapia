<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class IndisiplinerDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'indisipliner_id',
        'jenis_pelanggaran',
        'alasan',
        'poin',
    ];

    public function indisipliner(): BelongsTo
    {
        return $this->belongsTo(Indisipliner::class);
    }
}