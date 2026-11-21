<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_toko');
            $table->string('alamat')->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->string('logo')->nullable();
            $table->string('footer_struk')->nullable();
            $table->enum('ukuran_struk', ['58', '80'])->default('58');
            $table->text('template_wa_masuk')->nullable();
            $table->text('template_wa_selesai')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan');
    }
};
