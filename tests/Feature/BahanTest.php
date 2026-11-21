<?php

use App\Models\Bahan;
use App\Models\User;
use Database\Seeders\BahanSeeder;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->kasir = User::factory()->create();
    $this->owner = User::factory()->owner()->create();
    $this->deterjen = Bahan::create(['nama' => 'Deterjen cair', 'satuan' => 'liter', 'stok_minimum' => 5]);
});

it('forbids kasir from stok bahan', function (string $method, string $url) {
    $this->actingAs($this->kasir)->json($method, $url)->assertForbidden();
})->with([
    ['GET', '/bahan'], ['GET', '/bahan/data'], ['POST', '/bahan'], ['POST', '/bahan/1/mutasi'], ['GET', '/bahan/1/riwayat'],
]);

it('renders page for owner', function () {
    $this->actingAs($this->owner)->get('/bahan')->assertOk()->assertSee('Rak Bahan Laundry');
});

it('creates bahan with stok awal recorded as mutasi', function () {
    $this->actingAs($this->owner)->postJson('/bahan', ['nama' => 'Pewangi', 'satuan' => 'liter', 'stok_minimum' => 2, 'stok_awal' => 10])->assertOk();

    $b = Bahan::where('nama', 'Pewangi')->first();
    expect($b->stok)->toBe(10.0)->and($b->riwayat()->first()->keterangan)->toBe('Stok awal');

    $this->actingAs($this->owner)->postJson('/bahan', ['nama' => 'Pewangi', 'satuan' => 'liter', 'stok_minimum' => 1])->assertJsonValidationErrors('nama');
});

it('computes mutasi masuk, keluar, penyesuaian and rejects negative stock', function () {
    $b = $this->deterjen;

    $b->mutasi('masuk', 10.5, $this->owner);
    $b->mutasi('keluar', 3.25, $this->owner, 'cuci pagi');
    expect($b->fresh()->stok)->toBe(7.25);

    expect(fn () => $b->mutasi('keluar', 8, $this->owner))->toThrow(ValidationException::class);
    expect(fn () => $b->mutasi('masuk', 0, $this->owner))->toThrow(ValidationException::class);
    expect($b->fresh()->stok)->toBe(7.25);

    $m = $b->mutasi('penyesuaian', 6, $this->owner, 'stok opname');
    expect($b->fresh()->stok)->toBe(6.0)->and($m->jumlah)->toBe(6.0)->and($m->stok_setelah)->toBe(6.0);

    $b->mutasi('penyesuaian', 0, $this->owner);
    expect($b->fresh()->stok)->toBe(0.0)->and($b->riwayat()->count())->toBe(4);
});

it('records mutasi over http and returns 422 when stock is not enough', function () {
    $url = "/bahan/{$this->deterjen->id}/mutasi";

    $this->actingAs($this->owner)->postJson($url, ['jenis' => 'masuk', 'jumlah' => 4])->assertOk()->assertJsonFragment(['message' => 'Stok Deterjen cair sekarang 4 liter.']);
    $this->actingAs($this->owner)->postJson($url, ['jenis' => 'keluar', 'jumlah' => 5])->assertJsonValidationErrors('jumlah');
    $this->actingAs($this->owner)->postJson($url, ['jenis' => 'curi', 'jumlah' => 1])->assertJsonValidationErrors('jenis');

    expect($this->deterjen->fresh()->stok)->toBe(4.0);
});

it('returns riwayat newest first with user', function () {
    foreach ([['masuk', 5], ['keluar', 1], ['keluar', 2]] as [$jenis, $n]) {
        $this->deterjen->mutasi($jenis, $n, $this->owner);
    }

    $res = $this->actingAs($this->owner)->getJson("/bahan/{$this->deterjen->id}/riwayat")->assertOk()->json();

    expect(array_column($res, 'stok_setelah'))->toBe([2, 4, 5])
        ->and($res[0]['user']['name'])->toBe($this->owner->name);
});

it('detects low stock and shows it on owner dashboard only', function () {
    $hanger = Bahan::create(['nama' => 'Hanger', 'satuan' => 'pcs', 'stok_minimum' => 10]);
    $hanger->mutasi('masuk', 50, $this->owner);

    expect(Bahan::menipis()->pluck('nama')->all())->toBe(['Deterjen cair'])
        ->and($this->deterjen->menipis)->toBeTrue()
        ->and($hanger->fresh()->menipis)->toBeFalse();

    $this->actingAs($this->owner)->get('/dashboard')->assertSee('Stok menipis:')->assertSee('Deterjen cair');
    $this->actingAs($this->kasir)->get('/dashboard')->assertDontSee('Stok menipis:');

    $this->deterjen->mutasi('masuk', 20, $this->owner);
    $this->actingAs($this->owner)->get('/dashboard')->assertDontSee('Stok menipis:');
});

it('seeds default bahan idempotently', function () {
    $this->seed(BahanSeeder::class);
    $this->seed(BahanSeeder::class);

    expect(Bahan::count())->toBe(4); // Deterjen cair sudah ada dari beforeEach
});
