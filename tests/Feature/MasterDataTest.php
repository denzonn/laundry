<?php

use App\Models\Layanan;
use App\Models\MetodePembayaran;
use App\Models\Parfum;
use App\Models\Pelanggan;
use App\Models\User;

it('renders all master pages for owner', function () {
    $owner = User::factory()->owner()->create();

    foreach (['/layanan', '/pelanggan', '/parfum', '/metode-pembayaran'] as $url) {
        $this->actingAs($owner)->get($url)->assertOk();
        $this->actingAs($owner)->getJson("$url/data")->assertOk();
    }
});

it('lets kasir manage pelanggan but not other masters', function () {
    $kasir = User::factory()->create();

    $this->actingAs($kasir)->get('/pelanggan')->assertOk();
    $this->actingAs($kasir)->get('/layanan')->assertForbidden();
    $this->actingAs($kasir)->get('/parfum')->assertForbidden();
    $this->actingAs($kasir)->get('/metode-pembayaran')->assertForbidden();
});

it('validates satuan against kategori and saves layanan', function () {
    $owner = User::factory()->owner()->create();
    $data = ['nama' => 'Cuci Setrika', 'kategori' => 'kiloan', 'satuan' => 'pcs', 'harga' => 7000, 'durasi_jam' => 48];

    $this->actingAs($owner)->postJson('/layanan', $data)->assertJsonValidationErrors('satuan');
    $this->actingAs($owner)->postJson('/layanan', ['satuan' => 'kg'] + $data)->assertOk();

    $layanan = Layanan::firstOrFail();
    expect($layanan->harga)->toBe(7000)->and($layanan->aktif)->toBeTrue();

    $this->actingAs($owner)->patchJson("/layanan/{$layanan->id}/toggle")->assertOk();
    expect($layanan->fresh()->aktif)->toBeFalse();
});

it('normalizes pelanggan phone and keeps it unique', function () {
    $kasir = User::factory()->create();

    $res = $this->actingAs($kasir)->postJson('/pelanggan', ['nama' => 'Budi', 'no_hp' => '0812-3456-7890'])->assertOk();
    expect($res->json('data.no_hp'))->toBe('6281234567890');

    $this->actingAs($kasir)->postJson('/pelanggan', ['nama' => 'Budi 2', 'no_hp' => '+62 812 3456 7890'])
        ->assertJsonValidationErrors('no_hp');

    // update diri sendiri dengan nomor yang sama tetap boleh
    $id = $res->json('data.id');
    $this->actingAs($kasir)->putJson("/pelanggan/$id", ['nama' => 'Budi S', 'no_hp' => '081234567890'])->assertOk();
});

it('searches pelanggan by name or local phone format', function () {
    $kasir = User::factory()->create();
    Pelanggan::create(['nama' => 'Budi', 'no_hp' => '081234567890']);
    Pelanggan::create(['nama' => 'Siti', 'no_hp' => '085600001111']);

    expect($this->actingAs($kasir)->getJson('/pelanggan/data?search=budi')->json('total'))->toBe(1);
    expect($this->actingAs($kasir)->getJson('/pelanggan/data?search=0856')->json('data.0.nama'))->toBe('Siti');
});

it('keeps at least one active payment method', function () {
    $owner = User::factory()->owner()->create();
    $tunai = MetodePembayaran::create(['nama' => 'Tunai']);

    $this->actingAs($owner)->patchJson("/metode-pembayaran/{$tunai->id}/toggle")->assertStatus(422);
    $this->actingAs($owner)->deleteJson("/metode-pembayaran/{$tunai->id}")->assertStatus(422);

    MetodePembayaran::create(['nama' => 'QRIS']);
    $this->actingAs($owner)->patchJson("/metode-pembayaran/{$tunai->id}/toggle")->assertOk();
});

it('rejects duplicate parfum name', function () {
    $owner = User::factory()->owner()->create();
    Parfum::create(['nama' => 'Lavender']);

    $this->actingAs($owner)->postJson('/parfum', ['nama' => 'Lavender'])->assertJsonValidationErrors('nama');
});
