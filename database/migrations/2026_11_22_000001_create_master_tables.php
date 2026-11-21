<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('layanan', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->enum('kategori', ['kiloan', 'satuan']);
            $table->string('satuan', 20);
            $table->unsignedBigInteger('harga');
            $table->unsignedSmallInteger('durasi_jam');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('pelanggan', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('no_hp', 20)->unique();
            $table->string('alamat')->nullable();
            $table->string('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('parfum', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50)->unique();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('metode_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50)->unique();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metode_pembayaran');
        Schema::dropIfExists('parfum');
        Schema::dropIfExists('pelanggan');
        Schema::dropIfExists('layanan');
    }
};
