<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['owner', 'kasir', 'kurir'])->default('kasir')->change();
        });

        Schema::table('pengaturan', function (Blueprint $table) {
            $table->boolean('poin_aktif')->default(false);
            $table->unsignedInteger('rupiah_per_poin')->default(10000);
            $table->unsignedInteger('nilai_poin')->default(100);
        });

        Schema::table('pelanggan', function (Blueprint $table) {
            $table->unsignedInteger('poin')->default(0);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('poin_ditukar')->default(0)->after('biaya_antar');
            $table->unsignedBigInteger('potongan_poin')->default(0)->after('poin_ditukar');
        });

        Schema::create('riwayat_poin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggan_id')->constrained('pelanggan')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('jenis', ['dapat', 'tukar', 'batal']);
            $table->integer('poin');
            $table->unsignedInteger('saldo_setelah');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('pengiriman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->enum('jenis', ['jemput', 'antar']);
            $table->foreignId('kurir_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('alamat');
            $table->dateTime('jadwal')->index();
            $table->enum('status', ['dijadwalkan', 'dalam_perjalanan', 'selesai', 'batal'])->default('dijadwalkan');
            $table->unsignedBigInteger('biaya')->default(0);
            $table->string('catatan')->nullable();
            $table->dateTime('selesai_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengiriman');
        Schema::dropIfExists('riwayat_poin');
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn(['poin_ditukar', 'potongan_poin']));
        Schema::table('pelanggan', fn (Blueprint $table) => $table->dropColumn('poin'));
        Schema::table('pengaturan', fn (Blueprint $table) => $table->dropColumn(['poin_aktif', 'rupiah_per_poin', 'nilai_poin']));
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['owner', 'kasir'])->default('kasir')->change();
        });
    }
};
