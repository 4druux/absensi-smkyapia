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
        Schema::table('siswa_note_rekaps', function (Blueprint $table) {
            $table->float('poin_tambahan')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswa_note_rekaps', function (Blueprint $table) {
            $table->integer('poin_tambahan')->default(0)->change();
        });
    }
};