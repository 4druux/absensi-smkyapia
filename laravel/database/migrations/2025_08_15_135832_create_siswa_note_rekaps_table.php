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
        Schema::create('siswa_note_rekaps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->string('tahun');
            $table->string('bulan_slug');
            $table->integer('poin_tambahan')->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['siswa_id', 'tahun', 'bulan_slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswa_note_rekaps');
    }
};