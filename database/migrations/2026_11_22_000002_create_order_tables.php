<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // FK restrictOnDelete: master yang sudah dipakai order tidak bisa dihapus (lihat handler di bootstrap/app.php)
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->foreignId('pelanggan_id')->constrained('pelanggan')->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('parfum_id')->nullable()->constrained('parfum')->restrictOnDelete();
            $table->dateTime('tanggal_masuk');
            $table->dateTime('estimasi_selesai');
            $table->dateTime('tanggal_diambil')->nullable();
            $table->enum('status', ['antrian', 'dicuci', 'disetrika', 'siap_diambil', 'diambil', 'batal'])->default('antrian')->index();
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('diskon')->default(0);
            $table->unsignedBigInteger('biaya_antar')->default(0);
            $table->unsignedBigInteger('total');
            $table->unsignedBigInteger('dibayar')->default(0);
            $table->enum('status_bayar', ['belum', 'dp', 'lunas'])->default('belum')->index();
            $table->text('catatan')->nullable();
            $table->string('alasan_batal')->nullable();
            $table->timestamps();

            $table->index('tanggal_masuk');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('layanan_id')->constrained('layanan')->restrictOnDelete();
            $table->string('nama_layanan', 100);
            $table->string('satuan', 20);
            $table->unsignedBigInteger('harga');
            $table->decimal('qty', 8, 2);
            $table->unsignedBigInteger('subtotal');
            $table->timestamps();
        });

        Schema::create('order_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('status', 20);
            $table->string('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('metode_pembayaran_id')->constrained('metode_pembayaran')->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('jumlah');
            $table->dateTime('tanggal');
            $table->string('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
        Schema::dropIfExists('order_status_logs');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
