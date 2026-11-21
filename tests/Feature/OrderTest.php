<?php

use App\Models\Layanan;
use App\Models\MetodePembayaran;
use App\Models\Order;
use App\Models\Pelanggan;
use App\Models\User;

beforeEach(function () {
    $this->kasir = User::factory()->create();
    $this->owner = User::factory()->owner()->create();
    $this->pelanggan = Pelanggan::create(['nama' => 'Budi', 'no_hp' => '081234567890']);
    $this->kiloan = Layanan::create(['nama' => 'Cuci Setrika', 'kategori' => 'kiloan', 'satuan' => 'kg', 'harga' => 7000, 'durasi_jam' => 48]);
    $this->satuan = Layanan::create(['nama' => 'Bed Cover', 'kategori' => 'satuan', 'satuan' => 'pcs', 'harga' => 25000, 'durasi_jam' => 72]);
    $this->tunai = MetodePembayaran::create(['nama' => 'Tunai']);
});

function buatOrder($test, array $extra = []): Order
{
    $test->actingAs($test->kasir)->postJson('/order', $extra + [
        'pelanggan_id' => $test->pelanggan->id,
        'items' => [
            ['layanan_id' => $test->kiloan->id, 'qty' => 3.5],
            ['layanan_id' => $test->satuan->id, 'qty' => 1],
        ],
    ])->assertOk();

    return Order::latest('id')->firstOrFail();
}

it('computes totals on the server, ignoring client prices', function () {
    $order = buatOrder($this, ['diskon' => 1500, 'biaya_antar' => 5000, 'items' => [
        ['layanan_id' => $this->kiloan->id, 'qty' => 3.5, 'harga' => 1], // harga dari client diabaikan
        ['layanan_id' => $this->satuan->id, 'qty' => 1],
    ]]);

    expect($order->subtotal)->toBe(24500 + 25000)
        ->and($order->total)->toBe(49500 - 1500 + 5000)
        ->and($order->status)->toBe('antrian')
        ->and($order->status_bayar)->toBe('belum')
        ->and($order->items)->toHaveCount(2)
        ->and($order->items[0]->nama_layanan)->toBe('Cuci Setrika')
        ->and($order->logs)->toHaveCount(1);

    // estimasi = layanan terlama (72 jam)
    expect((int) round($order->tanggal_masuk->diffInHours($order->estimasi_selesai)))->toBe(72);
});

it('generates sequential daily codes', function () {
    $a = buatOrder($this);
    $b = buatOrder($this);

    expect($a->kode)->toBe('LND-'.now()->format('ymd').'-0001')
        ->and($b->kode)->toBe('LND-'.now()->format('ymd').'-0002');
});

it('rejects discount above subtotal and payment above total', function () {
    $base = ['pelanggan_id' => $this->pelanggan->id, 'items' => [['layanan_id' => $this->satuan->id, 'qty' => 1]]];

    $this->actingAs($this->kasir)->postJson('/order', $base + ['diskon' => 30000])->assertJsonValidationErrors('diskon');
    $this->actingAs($this->kasir)->postJson('/order', $base + ['bayar' => 30000, 'metode_pembayaran_id' => $this->tunai->id])->assertJsonValidationErrors('bayar');
    $this->actingAs($this->kasir)->postJson('/order', $base + ['bayar' => 10000])->assertJsonValidationErrors('metode_pembayaran_id');
    $this->actingAs($this->kasir)->postJson('/order', ['pelanggan_id' => $this->pelanggan->id, 'items' => []])->assertJsonValidationErrors('items');

    expect(Order::count())->toBe(0);
});

it('rejects inactive layanan', function () {
    $this->satuan->update(['aktif' => false]);

    $this->actingAs($this->kasir)->postJson('/order', [
        'pelanggan_id' => $this->pelanggan->id, 'items' => [['layanan_id' => $this->satuan->id, 'qty' => 1]],
    ])->assertJsonValidationErrors('items.0.layanan_id');
});

it('tracks DP then pelunasan', function () {
    $order = buatOrder($this, ['bayar' => 20000, 'metode_pembayaran_id' => $this->tunai->id]); // total 49500

    expect($order->status_bayar)->toBe('dp')->and($order->dibayar)->toBe(20000)->and($order->sisa)->toBe(29500);

    $this->actingAs($this->kasir)->postJson("/order/{$order->id}/bayar", ['jumlah' => 30000, 'metode_pembayaran_id' => $this->tunai->id])
        ->assertJsonValidationErrors('jumlah'); // melebihi sisa

    $this->actingAs($this->kasir)->postJson("/order/{$order->id}/bayar", ['jumlah' => 29500, 'metode_pembayaran_id' => $this->tunai->id])
        ->assertOk();

    expect($order->fresh())->status_bayar->toBe('lunas')->sisa->toBe(0)
        ->and($order->pembayaran()->count())->toBe(2);
});

it('enforces status flow', function () {
    $order = buatOrder($this);
    $ubah = fn ($status, $user = null, $catatan = null) => $this->actingAs($user ?? $this->kasir)
        ->patchJson("/order/{$order->id}/status", ['status' => $status, 'catatan' => $catatan]);

    $ubah('disetrika')->assertJsonValidationErrors('status');   // lompat 2 langkah
    $ubah('dicuci')->assertOk();
    $ubah('siap_diambil')->assertOk();                          // lompat setrika boleh
    $ubah('diambil')->assertJsonValidationErrors('status');     // belum lunas
    $ubah('antrian')->assertJsonValidationErrors('status');     // mundur

    $order->fresh()->bayar(49500, $this->tunai->id, $this->kasir);
    $ubah('diambil')->assertOk();

    expect($order->fresh())->status->toBe('diambil')->tanggal_diambil->not->toBeNull()
        ->and($order->logs()->count())->toBe(4);

    $ubah('batal', $this->owner, 'salah input')->assertJsonValidationErrors('status'); // sudah diambil
});

it('only owner can cancel, with a reason', function () {
    $order = buatOrder($this);

    $this->actingAs($this->kasir)->patchJson("/order/{$order->id}/status", ['status' => 'batal', 'catatan' => 'x'])->assertJsonValidationErrors('status');
    $this->actingAs($this->owner)->patchJson("/order/{$order->id}/status", ['status' => 'batal'])->assertJsonValidationErrors('catatan');
    $this->actingAs($this->owner)->patchJson("/order/{$order->id}/status", ['status' => 'batal', 'catatan' => 'Pelanggan batal'])->assertOk();

    expect($order->fresh())->status->toBe('batal')->alasan_batal->toBe('Pelanggan batal');

    $this->actingAs($this->kasir)->postJson("/order/{$order->id}/bayar", ['jumlah' => 1000, 'metode_pembayaran_id' => $this->tunai->id])
        ->assertJsonValidationErrors('jumlah');
});

it('lists orders with status counts and search', function () {
    buatOrder($this);
    $o = buatOrder($this);
    $o->ubahStatus('dicuci', $this->kasir);

    $res = $this->actingAs($this->kasir)->getJson('/order/data?status=dicuci')->assertOk();
    expect($res->json('total'))->toBe(1)
        ->and($res->json('counts.antrian'))->toBe(1)
        ->and($res->json('counts.dicuci'))->toBe(1);

    expect($this->actingAs($this->kasir)->getJson('/order/data?search=budi')->json('total'))->toBe(2);
    expect($this->actingAs($this->kasir)->getJson('/order/data?search=zzz')->json('total'))->toBe(0);
});

it('blocks deleting masters used by orders', function () {
    buatOrder($this);

    $this->actingAs($this->owner)->deleteJson("/layanan/{$this->kiloan->id}")->assertStatus(422);
    $this->actingAs($this->owner)->deleteJson("/pelanggan/{$this->pelanggan->id}")->assertStatus(422);
    expect(Layanan::find($this->kiloan->id))->not->toBeNull();
});

it('returns pelanggan order history', function () {
    buatOrder($this);

    $this->actingAs($this->kasir)->getJson("/pelanggan/{$this->pelanggan->id}/orders")
        ->assertOk()->assertJsonPath('jumlah', 1)->assertJsonPath('total', 49500);
});

it('renders order pages', function () {
    $order = buatOrder($this, ['bayar' => 10000, 'metode_pembayaran_id' => $this->tunai->id, 'catatan' => 'noda kopi']);
    $order->ubahStatus('dicuci', $this->kasir);

    $this->actingAs($this->kasir)->get('/order/baru')->assertOk()->assertSee('Cuci Setrika');
    $this->actingAs($this->kasir)->get('/order')->assertOk();
    $this->actingAs($this->kasir)->get("/order/{$order->id}")->assertOk()
        ->assertSee($order->kode)->assertSee('noda kopi')->assertSee('Terima Pembayaran')
        ->assertDontSee('Batalkan'); // kasir tidak bisa batal

    $this->actingAs($this->owner)->get("/order/{$order->id}")->assertOk()->assertSee('Batalkan');
});
