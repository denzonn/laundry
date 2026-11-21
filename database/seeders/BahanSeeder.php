<?php

namespace Database\Seeders;

use App\Models\Bahan;
use Illuminate\Database\Seeder;

class BahanSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Bahan::DEFAULT as [$nama, $satuan, $minimum]) {
            Bahan::firstOrCreate(['nama' => $nama], ['satuan' => $satuan, 'stok' => 0, 'stok_minimum' => $minimum]);
        }
    }
}
