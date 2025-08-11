<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kenaikan_bersyarats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->string('tahun'); 
            $table->integer('jumlah_nilai_kurang')->nullable();
            $table->enum('akhlak', ['Baik', 'Kurang'])->nullable();
            $table->enum('rekomendasi_walas', ['Tidak Naik', 'Ragu-ragu'])->nullable();
            $table->text('keputusan_akhir')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kenaikan_bersyarats');
    }
};
