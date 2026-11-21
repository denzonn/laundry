<?php

namespace Database\Seeders;

use App\Models\Layanan;
use App\Models\MetodePembayaran;
use App\Models\Parfum;
use Illuminate\Database\Seeder;

class MasterSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Tunai', 'Transfer', 'QRIS'] as $nama) {
            MetodePembayaran::firstOrCreate(['nama' => $nama]);
        }

        foreach (['Lavender', 'Sakura', 'Ocean Fresh', 'Tanpa Parfum'] as $nama) {
            Parfum::firstOrCreate(['nama' => $nama]);
        }

        $layanan = [
            ['Cuci Kering Setrika', 'kiloan', 'kg', 7000, 48],
            ['Cuci Kering', 'kiloan', 'kg', 5000, 24],
            ['Setrika Saja', 'kiloan', 'kg', 4000, 24],
            ['Express 6 Jam', 'kiloan', 'kg', 12000, 6],
            ['Bed Cover', 'satuan', 'pcs', 25000, 72],
            ['Sepatu', 'satuan', 'pasang', 30000, 72],
            ['Karpet', 'satuan', 'm²', 15000, 96],
        ];

        foreach ($layanan as [$nama, $kategori, $satuan, $harga, $durasi]) {
            Layanan::firstOrCreate(['nama' => $nama], compact('kategori', 'satuan', 'harga') + ['durasi_jam' => $durasi]);
        }
    }
}
