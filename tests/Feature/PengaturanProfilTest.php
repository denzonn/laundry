<?php

use App\Models\Pengaturan;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('lets owner update store settings with logo', function () {
    Storage::fake('public');
    $owner = User::factory()->owner()->create();

    $this->actingAs($owner)->get('/pengaturan')->assertOk();

    $this->actingAs($owner)->put('/pengaturan', [
        'nama_toko' => 'Laundry Wangi',
        'ukuran_struk' => '80',
        'logo' => UploadedFile::fake()->image('logo.png'),
    ])->assertRedirect()->assertSessionHasNoErrors();

    $toko = Pengaturan::first();
    expect($toko->nama_toko)->toBe('Laundry Wangi')->and($toko->ukuran_struk)->toBe('80');
    Storage::disk('public')->assertExists($toko->logo);
    expect(Pengaturan::count())->toBe(1);
});

it('lets any user update own profile and password', function () {
    $kasir = User::factory()->create();

    $this->actingAs($kasir)->get('/profil')->assertOk();

    $this->actingAs($kasir)->put('/profil', [
        'name' => 'Nama Baru', 'email' => $kasir->email, 'no_hp' => '0812',
    ])->assertSessionHasNoErrors();
    expect($kasir->fresh()->name)->toBe('Nama Baru');

    $this->actingAs($kasir)->put('/profil/password', [
        'current_password' => 'salah', 'password' => 'barubaru1', 'password_confirmation' => 'barubaru1',
    ])->assertSessionHasErrors('current_password');

    $this->actingAs($kasir)->put('/profil/password', [
        'current_password' => 'password', 'password' => 'barubaru1', 'password_confirmation' => 'barubaru1',
    ])->assertSessionHasNoErrors();
    expect(Hash::check('barubaru1', $kasir->fresh()->password))->toBeTrue();
});

it('profile update cannot escalate role', function () {
    $kasir = User::factory()->create();

    $this->actingAs($kasir)->put('/profil', ['name' => 'X', 'email' => $kasir->email, 'role' => 'owner']);

    expect($kasir->fresh()->role)->toBe('kasir');
});
