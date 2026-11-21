<?php

use App\Models\Layanan;
use App\Models\MetodePembayaran;
use App\Models\Order;
use App\Models\Pelanggan;
use App\Models\Pengaturan;
use App\Models\User;

beforeEach(function () {
    $this->kasir = User::factory()->create();
    $this->owner = User::factory()->owner()->create();
    $this->pelanggan = Pelanggan::create(['nama' => 'Sari', 'no_hp' => '081299990000']);
    $this->layanan = Layanan::create(['nama' => 'Bed Cover', 'kategori' => 'satuan', 'satuan' => 'pcs', 'harga' => 25000, 'durasi_jam' => 24]);
    $this->tunai = MetodePembayaran::create(['nama' => 'Tunai']);
    Pengaturan::toko()->update(['poin_aktif' => true, 'rupiah_per_poin' => 10000, 'nilai_poin' => 100]);
});

function orderPoin($test, array $extra = [], int $qty = 2)
{
    return $test->actingAs($test->kasir)->postJson('/order', $extra + [
        'pelanggan_id' => $test->pelanggan->id,
        'items' => [['layanan_id' => $test->layanan->id, 'qty' => $qty]],
    ]);
}

function ambil($test, Order $order): void
{
    foreach (['siap_diambil', 'diambil'] as $s) {
        $test->actingAs($test->kasir)->patchJson("/order/{$order->id}/status", ['status' => $s])->assertOk();
    }
}

it('awards poin once when an order is picked up', function () {
    orderPoin($this, ['bayar' => 50000, 'metode_pembayaran_id' => $this->tunai->id])->assertOk(); // total 50.000
    $order = Order::latest('id')->first();

    $this->actingAs($this->kasir)->patchJson("/order/{$order->id}/status", ['status' => 'siap_diambil'])->assertOk();
    expect($this->pelanggan->fresh()->poin)->toBe(0);

    $this->actingAs($this->kasir)->patchJson("/order/{$order->id}/status", ['status' => 'diambil'])->assertOk();
    expect($this->pelanggan->fresh()->poin)->toBe(5);

    $order->fresh()->beriPoin(); // dipanggil ulang tetap sekali
    expect($this->pelanggan->fresh()->poin)->toBe(5)
        ->and($this->pelanggan->riwayatPoin()->count())->toBe(1)
        ->and($this->pelanggan->riwayatPoin()->first()->saldo_setelah)->toBe(5);
});

it('awards nothing when the program is off', function () {
    Pengaturan::toko()->update(['poin_aktif' => false]);
    orderPoin($this, ['bayar' => 50000, 'metode_pembayaran_id' => $this->tunai->id])->assertOk();
    ambil($this, Order::latest('id')->first());

    expect($this->pelanggan->fresh()->poin)->toBe(0)->and($this->pelanggan->riwayatPoin()->count())->toBe(0);
});

it('lets kasir redeem poin and computes the total on the server', function () {
    $this->pelanggan->mutasiPoin('dapat', 300);

    orderPoin($this, ['diskon' => 5000, 'biaya_antar' => 7000, 'poin_ditukar' => 100])->assertOk();
    $order = Order::latest('id')->first();

    expect($order->poin_ditukar)->toBe(100)
        ->and($order->potongan_poin)->toBe(10000)
        ->and($order->total)->toBe(50000 - 5000 - 10000 + 7000)
        ->and($this->pelanggan->fresh()->poin)->toBe(200)
        ->and($this->pelanggan->riwayatPoin()->first()->only('jenis', 'poin', 'saldo_setelah'))
        ->toBe(['jenis' => 'tukar', 'poin' => -100, 'saldo_setelah' => 200]);

    $this->actingAs($this->kasir)->get("/order/{$order->id}/struk")->assertSee('Tukar 100 poin');
});

it('caps redeemed poin by saldo and by subtotal after discount', function () {
    $this->pelanggan->mutasiPoin('dapat', 1000);

    // potongan 600 × 100 = 60.000 > subtotal 50.000 − diskon 0
    orderPoin($this, ['poin_ditukar' => 600])->assertJsonValidationErrors('poin_ditukar');
    // saldo cukup tapi diskon menghabiskan subtotal
    orderPoin($this, ['diskon' => 50000, 'poin_ditukar' => 1])->assertJsonValidationErrors('poin_ditukar');

    $this->pelanggan->forceFill(['poin' => 0])->save();
    orderPoin($this, ['poin_ditukar' => 10])->assertJsonValidationErrors('poin_ditukar'); // saldo kurang

    Pengaturan::toko()->update(['poin_aktif' => false]);
    $this->pelanggan->forceFill(['poin' => 50])->save();
    orderPoin($this, ['poin_ditukar' => 10])->assertJsonValidationErrors('poin_ditukar'); // program mati

    expect(Order::count())->toBe(0)->and($this->pelanggan->fresh()->poin)->toBe(50);
});

it('allows a full poin redemption leaving only biaya antar', function () {
    $this->pelanggan->mutasiPoin('dapat', 500);
    orderPoin($this, ['biaya_antar' => 5000, 'poin_ditukar' => 500])->assertOk();

    expect(Order::latest('id')->first()->total)->toBe(5000);
});

it('refunds redeemed poin when the order is cancelled', function () {
    $this->pelanggan->mutasiPoin('dapat', 200);
    orderPoin($this, ['poin_ditukar' => 150])->assertOk();
    $order = Order::latest('id')->first();
    expect($this->pelanggan->fresh()->poin)->toBe(50);

    $this->actingAs($this->owner)->patchJson("/order/{$order->id}/status", ['status' => 'batal', 'catatan' => 'Salah input'])->assertOk();

    expect($this->pelanggan->fresh()->poin)->toBe(200)
        ->and($this->pelanggan->riwayatPoin()->first()->jenis)->toBe('batal');
});

it('shows poin saldo in the pelanggan detail json', function () {
    $this->pelanggan->mutasiPoin('dapat', 12, null, 'Bonus');

    $this->actingAs($this->kasir)->getJson("/pelanggan/{$this->pelanggan->id}/orders")
        ->assertOk()->assertJsonPath('poin', 12)->assertJsonPath('riwayat_poin.0.keterangan', 'Bonus');
});

it('saves poin settings', function () {
    $this->actingAs($this->owner)->put('/pengaturan', [
        'nama_toko' => 'Laundry', 'ukuran_struk' => '58', 'poin_aktif' => '0', 'rupiah_per_poin' => 5000, 'nilai_poin' => 50,
    ])->assertRedirect();

    $toko = Pengaturan::first();
    expect($toko->poin_aktif)->toBeFalse()->and($toko->rupiah_per_poin)->toBe(5000)->and($toko->nilai_poin)->toBe(50);
});
