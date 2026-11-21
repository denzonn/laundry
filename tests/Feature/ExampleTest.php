<?php

use App\Models\User;

it('redirects guests to login', function () {
    $this->get('/')->assertRedirect('/dashboard');
    $this->get('/dashboard')->assertRedirect('/login');
});

it('shows dashboard to authenticated users', function () {
    $this->actingAs(User::factory()->create())
        ->get('/dashboard')
        ->assertOk();
});
