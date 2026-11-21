<?php

use App\Models\Layanan;
use App\Models\Order;
use App\Models\Pelanggan;
use App\Models\Pengaturan;
use App\Models\User;

beforeEach(function () {
    $this->kasir = User::factory()->create();
    $this->pelanggan = Pelanggan::create(['nama' => 'Budi Santoso', 'no_hp' => '0812-3456-7890']);
    $layanan = Layanan::create(['nama' => 'Cuci Setrika', 'kategori' => 'kiloan', 'satuan' => 'kg', 'harga' => 7000, 'durasi_jam' => 48]);
    Pengaturan::toko()->update(['nama_toko' => 'Laundry Wangi', 'alamat' => 'Jl. Melati 1', 'no_hp' => '0899111222']);

    $this->actingAs($this->kasir)->postJson('/order', [
        'pelanggan_id' => $this->pelanggan->id, 'items' => [['layanan_id' => $layanan->id, 'qty' => 2]],
    ])->assertOk()->assertJsonPath('redirect', fn ($url) => str_ends_with($url, '?baru=1'));

    $this->order = Order::latest('id')->firstOrFail();
});

it('renders struk with toko and items, auth required', function () {
    $this->get("/order/{$this->order->id}/struk")
        ->assertOk()
        ->assertSee(['Laundry Wangi', 'Jl. Melati 1', $this->order->kode, 'Cuci Setrika', '2 kg x 7.000', '14.000', 'Budi Santoso']);

    auth()->logout();
    $this->get("/order/{$this->order->id}/struk")->assertRedirect('/login');
});

it('builds WA message with placeholders replaced', function () {
    $pesan = $this->order->pesanWa('masuk');

    expect($pesan)->toContain('Halo Budi Santoso', $this->order->kode, 'Rp 14.000', url('/lacak/'.$this->order->kode))
        ->not->toContain('{')
        ->and($this->order->waUrl('selesai'))->toStartWith('https://wa.me/6281234567890?text=')
        ->toContain(rawurlencode('Sisa tagihan: Rp 14.000'));

    $this->get("/order/{$this->order->id}?baru=1")->assertOk()->assertSee('Cetak Struk')->assertSee('https://wa.me/6281234567890?text=', false);
});

it('shows public lacak page without login, masked and without phone', function () {
    auth()->logout();

    $this->get('/lacak/'.$this->order->kode)
        ->assertOk()
        ->assertSee(['Laundry Wangi', 'Bu***', 'Rp 14.000', 'Antrian'])
        ->assertDontSee('Budi Santoso')
        ->assertDontSee('6281234567890')
        ->assertDontSee('Cuci Setrika');

    $this->get('/lacak?kode='.strtolower($this->order->kode))->assertRedirect('/lacak/'.$this->order->kode);
    $this->get('/lacak')->assertOk();
    $this->get('/lacak/LND-000000-9999')->assertNotFound()->assertSee('Order tidak ditemukan');
});
