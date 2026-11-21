<?php

namespace Database\Seeders;

use App\Models\Pengaturan;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->owner()->create(['name' => 'Owner Laundry', 'email' => 'owner@laundry.test']);
        User::factory()->create(['name' => 'Kasir Laundry', 'email' => 'kasir@laundry.test']);

        Pengaturan::toko();

        $this->call(MasterSeeder::class);
    }
}
