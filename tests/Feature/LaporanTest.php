<?php

use App\Models\Layanan;
use App\Models\MetodePembayaran;
use App\Models\Order;
use App\Models\Pelanggan;
use App\Models\User;

beforeEach(function () {
    $this->kasir = User::factory()->create();
    $this->owner = User::factory()->owner()->create();
    $this->pelanggan = Pelanggan::create(['nama' => 'Siti', 'no_hp' => '081298765432']);
    $this->kiloan = Layanan::create(['nama' => 'Cuci Setrika', 'kategori' => 'kiloan', 'satuan' => 'kg', 'harga' => 7000, 'durasi_jam' => 48]);
    $this->satuan = Layanan::create(['nama' => 'Bed Cover', 'kategori' => 'satuan', 'satuan' => 'pcs', 'harga' => 25000, 'durasi_jam' => 72]);
    $this->tunai = MetodePembayaran::create(['nama' => 'Tunai']);
    $this->qris = MetodePembayaran::create(['nama' => 'QRIS']);
});

/** Order 2 kg kiloan (14.000) + 1 bed cover (25.000) = 39.000, opsional langsung bayar. */
function orderLaporan($test, int $bayar = 0, ?int $metode = null, array $extra = []): Order
{
    $test->actingAs($test->kasir)->postJson('/order', $extra + [
        'pelanggan_id' => $test->pelanggan->id,
        'items' => [['layanan_id' => $test->kiloan->id, 'qty' => 2], ['layanan_id' => $test->satuan->id, 'qty' => 1]],
        'bayar' => $bayar ?: null,
        'metode_pembayaran_id' => $metode ?? $test->tunai->id,
    ])->assertOk();

    return Order::latest('id')->firstOrFail();
}

it('forbids kasir from laporan', function (string $url) {
    $this->actingAs($this->kasir)->get($url)->assertForbidden();
    $this->actingAs($this->owner)->get($url)->assertOk();
})->with(['/laporan/pendapatan', '/laporan/order', '/laporan/piutang']);

it('sums pendapatan per day and metode inside the range', function () {
    $a = orderLaporan($this, 10000);
    $a->pembayaran()->update(['tanggal' => '2026-03-05 09:00:00']);
    $b = orderLaporan($this, 39000, $this->qris->id);
    $b->pembayaran()->update(['tanggal' => '2026-03-05 18:00:00']);
    $a->refresh()->bayar(5000, $this->qris->id, $this->kasir);
    $a->pembayaran()->latest('id')->first()->update(['tanggal' => '2026-03-07 10:00:00']);
    orderLaporan($this, 20000)->pembayaran()->update(['tanggal' => '2026-04-01 08:00:00']); // di luar periode

    $res = $this->actingAs($this->owner)->get('/laporan/pendapatan?start=2026-03-01&end=2026-03-31')->assertOk();

    expect($res->viewData('total'))->toBe(54000)
        ->and($res->viewData('transaksi'))->toBe(3)
        ->and($res->viewData('perHari')->mapWithKeys(fn ($h) => [$h->tgl => (int) $h->total])->all())
        ->toBe(['2026-03-05' => 49000, '2026-03-07' => 5000])
        ->and($res->viewData('perMetode')->mapWithKeys(fn ($m) => [$m->nama => (int) $m->total])->all())
        ->toBe(['QRIS' => 44000, 'Tunai' => 10000]);
});

it('excludes batal from omzet and layanan totals but counts it', function () {
    orderLaporan($this);
    orderLaporan($this);
    $batal = orderLaporan($this);
    $batal->ubahStatus('batal', $this->owner, 'Salah input');

    $res = $this->actingAs($this->owner)->get('/laporan/order')->assertOk()->assertSee('Cuci Setrika');

    expect($res->viewData('omzet'))->toBe(78000)
        ->and((int) $res->viewData('perStatus')['batal'])->toBe(1)
        ->and((int) $res->viewData('perStatus')['antrian'])->toBe(2);

    $kiloan = $res->viewData('perLayanan')->firstWhere('nama', 'Cuci Setrika');
    expect((float) $kiloan->qty)->toBe(4.0)
        ->and((int) $kiloan->subtotal)->toBe(28000)
        ->and((int) $kiloan->jumlah_order)->toBe(2);
});

it('lists only unpaid non-batal orders as piutang, oldest first', function () {
    $lunas = orderLaporan($this, 39000);
    $dp = orderLaporan($this, 9000);
    $belum = orderLaporan($this);
    $belum->update(['tanggal_masuk' => now()->subDays(10)]);
    orderLaporan($this)->ubahStatus('batal', $this->owner, 'Batal');

    $res = $this->actingAs($this->owner)->get('/laporan/piutang')->assertOk()->assertSee('10');

    expect($res->viewData('orders')->pluck('id')->all())->toBe([$belum->id, $dp->id])
        ->and($res->viewData('total'))->toBe(39000 + 30000)
        ->and($res->viewData('orders')->pluck('id'))->not->toContain($lunas->id);
});

it('shows revenue on dashboard only for owner', function () {
    orderLaporan($this, 12345);

    $this->actingAs($this->kasir)->get('/dashboard')->assertOk()
        ->assertSee('Order masuk hari ini')->assertDontSee('Pendapatan hari ini')->assertDontSee('12.345');

    $this->actingAs($this->owner)->get('/dashboard')->assertOk()
        ->assertSee('Pendapatan hari ini')->assertSee('Rp 12.345')->assertViewHas('orderHariIni', 1);
});

it('detects terlambat orders on dashboard', function () {
    $telat = orderLaporan($this);
    $telat->update(['estimasi_selesai' => now()->subHour()]);
    $siap = orderLaporan($this);
    $siap->update(['estimasi_selesai' => now()->subHour(), 'status' => 'siap_diambil']); // sudah selesai, tidak terlambat
    orderLaporan($this); // estimasi masih di depan

    $res = $this->actingAs($this->kasir)->get('/dashboard')->assertOk()->assertSee($telat->kode);

    expect($res->viewData('terlambat')->pluck('id')->all())->toBe([$telat->id])
        ->and($res->viewData('jumlahTerlambat'))->toBe(1)
        ->and($res->viewData('siap')->pluck('id')->all())->toBe([$siap->id]);
});
