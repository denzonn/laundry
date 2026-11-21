<?php

use App\Models\User;

it('blocks kasir from owner pages', function () {
    $kasir = User::factory()->create();

    $this->actingAs($kasir)->get('/user')->assertForbidden();
    $this->actingAs($kasir)->get('/user/data')->assertForbidden();
    $this->actingAs($kasir)->get('/pengaturan')->assertForbidden();
    $this->actingAs($kasir)->post('/user', [])->assertForbidden();
});

it('lets owner create, update, toggle and delete users', function () {
    $owner = User::factory()->owner()->create();

    $this->actingAs($owner)->get('/user')->assertOk()->assertSee('Tim Laundry');
    $this->actingAs($owner)->getJson('/user/data')->assertOk()->assertJsonPath('stats.owner', 1);

    $this->actingAs($owner)->postJson('/user', [
        'name' => 'Kasir Baru', 'email' => 'baru@laundry.test', 'role' => 'kasir', 'password' => 'rahasia123',
    ])->assertOk();

    $user = User::where('email', 'baru@laundry.test')->firstOrFail();
    expect($user->role)->toBe('kasir')->and($user->aktif)->toBeTrue();

    $this->actingAs($owner)->putJson("/user/{$user->id}", [
        'name' => 'Kasir Edit', 'email' => 'baru@laundry.test', 'role' => 'owner', 'password' => '',
    ])->assertOk();
    expect($user->fresh())->name->toBe('Kasir Edit')->role->toBe('owner');
    expect(Hash::check('rahasia123', $user->fresh()->password))->toBeTrue(); // password kosong = tidak diganti

    $this->actingAs($owner)->patchJson("/user/{$user->id}/toggle")->assertOk();
    expect($user->fresh()->aktif)->toBeFalse();

    $this->actingAs($owner)->deleteJson("/user/{$user->id}")->assertOk();
    expect(User::find($user->id))->toBeNull();
});

it('prevents owner from locking themselves out', function () {
    $owner = User::factory()->owner()->create();

    $this->actingAs($owner)->patchJson("/user/{$owner->id}/toggle")->assertStatus(422);
    $this->actingAs($owner)->deleteJson("/user/{$owner->id}")->assertStatus(422);

    $this->actingAs($owner)->putJson("/user/{$owner->id}", [
        'name' => $owner->name, 'email' => $owner->email, 'role' => 'kasir',
    ])->assertOk();
    expect($owner->fresh()->role)->toBe('owner');
});

it('validates unique email', function () {
    $owner = User::factory()->owner()->create();

    $this->actingAs($owner)->postJson('/user', [
        'name' => 'X', 'email' => $owner->email, 'role' => 'kasir', 'password' => 'rahasia123',
    ])->assertJsonValidationErrors('email');
});

it('rejects login for inactive users', function () {
    $user = User::factory()->create(['aktif' => false]);

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertSessionHasErrors('email');
    $this->assertGuest();
});
