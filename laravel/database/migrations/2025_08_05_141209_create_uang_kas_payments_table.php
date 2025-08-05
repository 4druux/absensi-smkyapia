<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uang_kas_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->year('tahun');
            $table->string('bulan_slug'); 
            $table->integer('minggu');
            $table->decimal('nominal', 10, 2)->default(0); 
            $table->string('status')->default('unpaid'); 
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['siswa_id', 'tahun', 'bulan_slug', 'minggu'], 'uang_kas_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uang_kas_payments');
    }
};