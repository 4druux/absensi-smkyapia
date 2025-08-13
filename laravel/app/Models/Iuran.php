<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Iuran extends Model
{
    use HasFactory;

    protected $fillable = ['kelas_id', 'deskripsi'];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function payments()
    {
        return $this->hasMany(UangKasOther::class, 'iuran_id');
    }
}