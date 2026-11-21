<?php

use App\Jobs\KirimWhatsapp;
use App\Models\Layanan;
use App\Models\Order;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->kasir = User::factory()->create();
    $this->pelanggan = Pelanggan::create(['nama' => 'Budi', 'no_hp' => '081234567890']);
    $this->layanan = Layanan::create(['nama' => 'Cuci Setrika', 'kategori' => 'kiloan', 'satuan' => 'kg', 'harga' => 7000, 'durasi_jam' => 48]);
    config(['services.whatsapp.enabled' => false, 'services.whatsapp.token' => null]);
});

function orderWa($test): Order
{
    $test->actingAs($test->kasir)->postJson('/order', [
        'pelanggan_id' => $test->pelanggan->id,
        'items' => [['layanan_id' => $test->layanan->id, 'qty' => 2]],
    ])->assertOk();

    return Order::latest('id')->firstOrFail();
}

function sampaiSiap($test, Order $order): void
{
    foreach (['dicuci', 'siap_diambil'] as $status) {
        $test->actingAs($test->kasir)->patchJson("/order/{$order->id}/status", ['status' => $status])->assertOk();
    }
}

it('does not dispatch when auto send is disabled or token empty', function () {
    Queue::fake();

    sampaiSiap($this, orderWa($this));

    config(['services.whatsapp.enabled' => true]); // aktif tapi token kosong
    orderWa($this);

    Queue::assertNothingPushed();
});

it('dispatches on order created and when ready for pickup', function () {
    Queue::fake();
    config(['services.whatsapp.enabled' => true, 'services.whatsapp.token' => 'token-uji']);

    $order = orderWa($this);
    Queue::assertPushed(KirimWhatsapp::class, 1);
    Queue::assertPushed(KirimWhatsapp::class, fn ($job) => $job->orderId === $order->id && $job->jenis === 'masuk');

    $this->actingAs($this->kasir)->patchJson("/order/{$order->id}/status", ['status' => 'dicuci'])->assertOk();
    Queue::assertPushed(KirimWhatsapp::class, 1); // status lain tidak mengirim

    $this->actingAs($this->kasir)->patchJson("/order/{$order->id}/status", ['status' => 'siap_diambil'])->assertOk();
    Queue::assertPushed(KirimWhatsapp::class, fn ($job) => $job->orderId === $order->id && $job->jenis === 'selesai');
    Queue::assertPushed(KirimWhatsapp::class, 2);
});

it('sends target and message from Order::pesanWa to fonnte', function () {
    Http::fake(['api.fonnte.com/*' => Http::response(['status' => true])]);
    $order = orderWa($this); // sync queue, tapi masih nonaktif saat dibuat
    Http::assertNothingSent();

    config(['services.whatsapp.enabled' => true, 'services.whatsapp.token' => 'token-uji']);
    (new KirimWhatsapp($order->id, 'masuk'))->handle();

    Http::assertSent(fn (Request $r) => $r->url() === 'https://api.fonnte.com/send'
        && $r->hasHeader('Authorization', 'token-uji')
        && $r['target'] === '6281234567890'
        && $r['message'] === $order->fresh()->pesanWa('masuk'));
});

it('throws when fonnte rejects so the job is retried', function () {
    Http::fake(['api.fonnte.com/*' => Http::response(['status' => false, 'reason' => 'invalid token'])]);
    $order = orderWa($this);
    config(['services.whatsapp.enabled' => true, 'services.whatsapp.token' => 'salah']);

    (new KirimWhatsapp($order->id, 'selesai'))->handle();
})->throws(RuntimeException::class, 'invalid token');

it('job is a no-op when token is empty', function () {
    Http::fake();
    $order = orderWa($this);
    config(['services.whatsapp.enabled' => true, 'services.whatsapp.token' => '']);

    (new KirimWhatsapp($order->id, 'masuk'))->handle();

    Http::assertNothingSent();
});

it('shows auto send status in pengaturan', function () {
    $owner = User::factory()->owner()->create();
    $this->actingAs($owner)->get('/pengaturan')->assertSee('Kirim otomatis:')->assertSee('Nonaktif');

    config(['services.whatsapp.enabled' => true, 'services.whatsapp.token' => 'x']);
    $this->actingAs($owner)->get('/pengaturan')->assertSee('Aktif');
});
