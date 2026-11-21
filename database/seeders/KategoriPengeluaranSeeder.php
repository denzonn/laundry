<?php

namespace Database\Seeders;

use App\Models\KategoriPengeluaran;
use Illuminate\Database\Seeder;

class KategoriPengeluaranSeeder extends Seeder
{
    public function run(): void
    {
        foreach (KategoriPengeluaran::DEFAULT as $nama) {
            KategoriPengeluaran::firstOrCreate(['nama' => $nama]);
        }
    }
}
