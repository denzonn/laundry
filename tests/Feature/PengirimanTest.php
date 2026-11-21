<?php

use App\Models\Layanan;
use App\Models\Order;
use App\Models\Pelanggan;
use App\Models\Pengiriman;
use App\Models\User;

beforeEach(function () {
    $this->kasir = User::factory()->create();
    $this->owner = User::factory()->owner()->create();
    $this->kurir = User::factory()->kurir()->create(['name' => 'Joko Kurir']);
    $this->kurirLain = User::factory()->kurir()->create();
    $this->pelanggan = Pelanggan::create(['nama' => 'Rina', 'no_hp' => '081311112222', 'alamat' => 'Jl. Melati 5']);
    $layanan = Layanan::create(['nama' => 'Cuci Kering', 'kategori' => 'kiloan', 'satuan' => 'kg', 'harga' => 6000, 'durasi_jam' => 24]);

    $this->actingAs($this->kasir)->postJson('/order', [
        'pelanggan_id' => $this->pelanggan->id, 'items' => [['layanan_id' => $layanan->id, 'qty' => 3]],
    ])->assertOk();
    $this->order = Order::latest('id')->first();
});

function jadwalkan($test, array $extra = [])
{
    return $test->actingAs($test->kasir)->postJson("/order/{$test->order->id}/pengiriman", $extra + [
        'jenis' => 'antar', 'alamat' => 'Jl. Melati 5', 'jadwal' => now()->addHour()->format('Y-m-d H:i'), 'kurir_id' => $test->kurir->id,
    ]);
}

it('lets kasir schedule and reassign a delivery', function () {
    jadwalkan($this, ['biaya' => 5000])->assertOk();
    $p = Pengiriman::first();

    expect($p->status)->toBe('dijadwalkan')->and($p->kurir_id)->toBe($this->kurir->id)->and($p->biaya)->toBe(5000);

    $this->actingAs($this->kasir)->putJson("/pengiriman/{$p->id}", [
        'alamat' => 'Jl. Mawar 1', 'jadwal' => now()->addDay()->format('Y-m-d H:i'), 'kurir_id' => $this->kurirLain->id,
    ])->assertOk();
    expect($p->fresh()->kurir_id)->toBe($this->kurirLain->id)->and($p->fresh()->alamat)->toBe('Jl. Mawar 1');

    // hanya user kurir aktif yang bisa ditugaskan
    jadwalkan($this, ['kurir_id' => $this->kasir->id])->assertJsonValidationErrors('kurir_id');

    $this->actingAs($this->kasir)->get("/order/{$this->order->id}")->assertOk()->assertSee('Antar / Jemput')->assertSee('Jl. Mawar 1');
    $this->actingAs($this->owner)->get('/pengiriman?tanggal='.now()->addDay()->toDateString())->assertOk()->assertSee('Rina');
});

it('runs the kurir flow on own tasks only', function () {
    jadwalkan($this)->assertOk();
    $p = Pengiriman::first();

    $this->actingAs($this->kurir)->get('/tugas')->assertOk()->assertSee('Rina')->assertSee('Buka Maps')
        ->assertSee('https://www.google.com/maps/search/?api=1&amp;query=Jl.+Melati+5', false)
        ->assertSee('Rp 18.000'); // sisa tagihan ditampilkan
    $this->actingAs($this->kurirLain)->get('/tugas')->assertOk()->assertDontSee('Rina');

    $this->actingAs($this->kurirLain)->patchJson("/pengiriman/{$p->id}/status", ['status' => 'dalam_perjalanan'])->assertForbidden();

    // tidak boleh lompat langsung ke selesai
    $this->actingAs($this->kurir)->patchJson("/pengiriman/{$p->id}/status", ['status' => 'selesai'])->assertJsonValidationErrors('status');
    $this->actingAs($this->kurir)->patchJson("/pengiriman/{$p->id}/status", ['status' => 'dalam_perjalanan'])->assertOk();
    $this->actingAs($this->kurir)->patchJson("/pengiriman/{$p->id}/status", ['status' => 'batal'])->assertJsonValidationErrors('status');
    $this->actingAs($this->kurir)->patchJson("/pengiriman/{$p->id}/status", ['status' => 'selesai'])->assertOk();

    expect($p->fresh()->status)->toBe('selesai')->and($p->fresh()->selesai_at)->not->toBeNull();
    $this->actingAs($this->kasir)->patchJson("/pengiriman/{$p->id}/status", ['status' => 'batal'])->assertJsonValidationErrors('status');
});

it('lets owner or kasir cancel an active delivery', function () {
    jadwalkan($this)->assertOk();
    $p = Pengiriman::first();

    $this->actingAs($this->kasir)->patchJson("/pengiriman/{$p->id}/status", ['status' => 'batal'])->assertOk();
    expect($p->fresh()->status)->toBe('batal');
    $this->actingAs($this->kasir)->putJson("/pengiriman/{$p->id}", ['alamat' => 'x', 'jadwal' => now()->format('Y-m-d H:i')])->assertStatus(422);
});

it('restricts kurir to tugas & profil', function () {
    jadwalkan($this)->assertOk();
    $p = Pengiriman::first();

    $this->actingAs($this->kurir)->get('/dashboard')->assertRedirect('/tugas');
    $this->actingAs($this->kurir)->get('/profil')->assertOk();

    foreach (['/order', '/order/baru', "/order/{$this->order->id}", '/order/data', '/pelanggan', '/pelanggan/data',
        "/pelanggan/{$this->pelanggan->id}/orders", '/pengiriman', '/laporan/pendapatan', '/user'] as $url) {
        $this->actingAs($this->kurir)->get($url)->assertForbidden();
    }
    $this->actingAs($this->kurir)->postJson("/order/{$this->order->id}/bayar", ['jumlah' => 1000])->assertForbidden();
    $this->actingAs($this->kurir)->putJson("/pengiriman/{$p->id}", ['alamat' => 'x', 'jadwal' => now()])->assertForbidden();
    $this->actingAs($this->kasir)->get('/tugas')->assertForbidden();

    // menu kurir saja
    $this->actingAs($this->kurir)->get('/tugas')->assertSee('Tugas Saya')->assertDontSee('Daftar Order')->assertDontSee('Pelanggan');
});

it('lets owner create a kurir user', function () {
    $this->actingAs($this->owner)->postJson('/user', [
        'name' => 'Kurir Baru', 'email' => 'kb@laundry.test', 'role' => 'kurir', 'password' => 'password123',
    ])->assertOk();

    expect(User::where('email', 'kb@laundry.test')->first()->isKurir())->toBeTrue();
});
