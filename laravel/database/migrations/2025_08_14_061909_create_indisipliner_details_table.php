<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indisipliner_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indisipliner_id')->constrained('indisipliner')->onDelete('cascade');
            $table->string('jenis_pelanggaran');
            $table->text('alasan')->nullable(); 
            $table->integer('poin'); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indisipliner_details');
    }
};