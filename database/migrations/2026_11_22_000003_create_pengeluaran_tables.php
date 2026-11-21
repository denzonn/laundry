<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_pengeluaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50)->unique();
            $table->timestamps();
        });

        Schema::create('pengeluaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_pengeluaran_id')->constrained('kategori_pengeluaran')->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->date('tanggal')->index();
            $table->unsignedBigInteger('jumlah');
            $table->string('keterangan')->nullable();
            $table->string('foto_nota')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengeluaran');
        Schema::dropIfExists('kategori_pengeluaran');
    }
};
