<?php

use App\Models\User;

it('stores the selected locale during registration', function () {
    $this->post(route('register'), [
        'name' => 'Karol',
        'email' => 'karol@example.com',
        'locale' => 'pl',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    expect(User::query()->where('email', 'karol@example.com')->first()?->locale)->toBe('pl');
});
