<?php

use App\Models\KategoriPengeluaran;
use App\Models\MetodePembayaran;
use App\Models\Order;
use App\Models\Pelanggan;
use App\Models\Pengeluaran;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->kasir = User::factory()->create();
    $this->owner = User::factory()->owner()->create();
    $this->bahan = KategoriPengeluaran::create(['nama' => 'Deterjen & Bahan']);
    $this->listrik = KategoriPengeluaran::create(['nama' => 'Listrik & Air']);
});

function keluar($test, array $data = []): Pengeluaran
{
    return Pengeluaran::create($data + [
        'kategori_pengeluaran_id' => $test->bahan->id, 'user_id' => $test->owner->id, 'tanggal' => today(), 'jumlah' => 10000,
    ]);
}

it('forbids kasir from pengeluaran and laba rugi', function (string $method, string $url) {
    $this->actingAs($this->kasir)->json($method, $url)->assertForbidden();
})->with([
    ['GET', '/pengeluaran'], ['GET', '/pengeluaran/data'], ['POST', '/pengeluaran'],
    ['POST', '/pengeluaran/kategori'], ['GET', '/laporan/laba-rugi'],
]);

it('renders pages for owner', function () {
    $this->actingAs($this->owner)->get('/pengeluaran')->assertOk()->assertSee('Buku Pengeluaran');
    $this->actingAs($this->owner)->get('/laporan/laba-rugi')->assertOk()->assertSee('berdasarkan uang masuk');
});

it('creates pengeluaran with foto nota, replaces and deletes the file', function () {
    Storage::fake('public');

    $this->actingAs($this->owner)->post('/pengeluaran', [
        'kategori_pengeluaran_id' => $this->bahan->id, 'tanggal' => today()->toDateString(), 'jumlah' => 75000,
        'keterangan' => 'Deterjen 5 L', 'foto_nota' => UploadedFile::fake()->image('nota.jpg'),
    ], ['Accept' => 'application/json'])->assertOk();

    $p = Pengeluaran::sole();
    expect($p->jumlah)->toBe(75000)->and($p->user_id)->toBe($this->owner->id);
    Storage::disk('public')->assertExists($lama = $p->foto_nota);

    $this->actingAs($this->owner)->post("/pengeluaran/{$p->id}", [
        '_method' => 'PUT', 'kategori_pengeluaran_id' => $this->listrik->id, 'tanggal' => today()->toDateString(),
        'jumlah' => 80000, 'foto_nota' => UploadedFile::fake()->image('baru.png'),
    ], ['Accept' => 'application/json'])->assertOk();

    $p->refresh();
    expect($p->jumlah)->toBe(80000)->and($p->kategori_pengeluaran_id)->toBe($this->listrik->id)->and($p->foto_nota)->not->toBe($lama);
    Storage::disk('public')->assertMissing($lama);
    Storage::disk('public')->assertExists($p->foto_nota);

    // update tanpa file → foto tetap
    $this->actingAs($this->owner)->putJson("/pengeluaran/{$p->id}", [
        'kategori_pengeluaran_id' => $this->listrik->id, 'tanggal' => today()->toDateString(), 'jumlah' => 81000,
    ])->assertOk();
    expect($p->fresh()->foto_nota)->toBe($p->foto_nota);

    $this->actingAs($this->owner)->deleteJson("/pengeluaran/{$p->id}")->assertOk();
    expect(Pengeluaran::count())->toBe(0);
    Storage::disk('public')->assertMissing($p->foto_nota);
});

it('validates pengeluaran input', function (array $data, string $field) {
    $this->actingAs($this->owner)->postJson('/pengeluaran', $data + [
        'kategori_pengeluaran_id' => $this->bahan->id, 'tanggal' => today()->toDateString(), 'jumlah' => 5000,
    ])->assertStatus(422)->assertJsonValidationErrors($field);
})->with([
    'jumlah nol' => [['jumlah' => 0], 'jumlah'],
    'jumlah terlalu besar' => [['jumlah' => 1000000001], 'jumlah'],
    'jumlah bukan angka' => [['jumlah' => '5.000'], 'jumlah'],
    'tanggal besok' => [['tanggal' => '2999-01-01'], 'tanggal'],
    'kategori tidak ada' => [['kategori_pengeluaran_id' => 999], 'kategori_pengeluaran_id'],
    'bukan gambar' => [['foto_nota' => UploadedFile::fake()->create('nota.pdf', 10, 'application/pdf')], 'foto_nota'],
    'gambar > 2MB' => [['foto_nota' => UploadedFile::fake()->image('besar.jpg')->size(3000)], 'foto_nota'],
]);

it('lists one month with totals per kategori and filter', function () {
    keluar($this, ['tanggal' => '2026-03-02', 'jumlah' => 10000]);
    keluar($this, ['tanggal' => '2026-03-20', 'jumlah' => 5000, 'kategori_pengeluaran_id' => $this->listrik->id]);
    keluar($this, ['tanggal' => '2026-03-31', 'jumlah' => 2000]);
    keluar($this, ['tanggal' => '2026-04-01', 'jumlah' => 99000]); // bulan lain

    $res = $this->actingAs($this->owner)->getJson('/pengeluaran/data?bulan=2026-03')->assertOk();
    expect($res->json('total'))->toBe(17000)
        ->and($res->json('items'))->toHaveCount(3)
        ->and($res->json('items.0.tanggal'))->toBe('2026-03-31')
        ->and(collect($res->json('perKategori'))->pluck('total', 'id')->all())->toBe([$this->bahan->id => 12000, $this->listrik->id => 5000]);

    $res = $this->actingAs($this->owner)->getJson("/pengeluaran/data?bulan=2026-03&kategori={$this->listrik->id}")->assertOk();
    expect($res->json('items'))->toHaveCount(1)->and($res->json('total'))->toBe(17000);
});

it('manages kategori and refuses deleting a used one', function () {
    $this->actingAs($this->owner)->postJson('/pengeluaran/kategori', ['nama' => 'Bensin'])->assertOk();
    $this->actingAs($this->owner)->postJson('/pengeluaran/kategori', ['nama' => 'Bensin'])->assertStatus(422)->assertJsonValidationErrors('nama');
    $bensin = KategoriPengeluaran::where('nama', 'Bensin')->sole();
    $this->actingAs($this->owner)->putJson("/pengeluaran/kategori/{$bensin->id}", ['nama' => 'BBM'])->assertOk();
    expect($bensin->fresh()->nama)->toBe('BBM');
    $this->actingAs($this->owner)->deleteJson("/pengeluaran/kategori/{$bensin->id}")->assertOk();

    keluar($this);
    $this->actingAs($this->owner)->deleteJson("/pengeluaran/kategori/{$this->bahan->id}")
        ->assertStatus(422)->assertJsonPath('message', fn ($m) => str_contains($m, 'tidak bisa dihapus'));
    expect(KategoriPengeluaran::find($this->bahan->id))->not->toBeNull();
});

it('computes laba rugi from pembayaran minus pengeluaran per kategori', function () {
    $order = Order::create([
        'kode' => 'LD-TEST-1', 'pelanggan_id' => Pelanggan::create(['nama' => 'Siti', 'no_hp' => '0812'])->id,
        'user_id' => $this->kasir->id, 'tanggal_masuk' => '2026-03-01 08:00:00', 'estimasi_selesai' => '2026-03-03 08:00:00', 'subtotal' => 100000, 'total' => 100000,
    ]);
    $tunai = MetodePembayaran::create(['nama' => 'Tunai']);
    $bayar = fn ($jumlah, $tgl) => $order->pembayaran()->create(['metode_pembayaran_id' => $tunai->id, 'user_id' => $this->kasir->id, 'jumlah' => $jumlah, 'tanggal' => $tgl]);
    $bayar(30000, '2026-03-05 10:00:00');
    $bayar(20000, '2026-03-31 21:00:00');
    $bayar(50000, '2026-04-01 08:00:00'); // di luar periode

    keluar($this, ['tanggal' => '2026-03-05', 'jumlah' => 40000]);
    keluar($this, ['tanggal' => '2026-03-10', 'jumlah' => 25000, 'kategori_pengeluaran_id' => $this->listrik->id]);
    keluar($this, ['tanggal' => '2026-03-31', 'jumlah' => 5000]);
    keluar($this, ['tanggal' => '2026-02-28', 'jumlah' => 99000]); // di luar periode

    $res = $this->actingAs($this->owner)->get('/laporan/laba-rugi?start=2026-03-01&end=2026-03-31')->assertOk()->assertSee('Rugi');

    expect($res->viewData('pendapatan'))->toBe(50000)
        ->and($res->viewData('pengeluaran'))->toBe(70000)
        ->and($res->viewData('laba'))->toBe(-20000)
        ->and($res->viewData('perKategori')->mapWithKeys(fn ($k) => [$k->nama => (int) $k->total])->all())
        ->toBe(['Deterjen & Bahan' => 45000, 'Listrik & Air' => 25000])
        ->and($res->viewData('tren')->all())->toBe([
            '2026-03-05' => ['masuk' => 30000, 'keluar' => 40000],
            '2026-03-10' => ['masuk' => 0, 'keluar' => 25000],
            '2026-03-31' => ['masuk' => 20000, 'keluar' => 5000],
        ]);

    // periode panjang → tren per bulan
    $res = $this->actingAs($this->owner)->get('/laporan/laba-rugi?start=2026-01-01&end=2026-04-30')->assertOk();
    expect($res->viewData('laba'))->toBe(100000 - 169000)
        ->and(array_keys($res->viewData('tren')->all()))->toBe(['2026-02', '2026-03', '2026-04']);
});
