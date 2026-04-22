<?php

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

test('guest can be redirected to github oauth screen', function () {
    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('scopes')->once()->with(['read:user', 'user:email'])->andReturnSelf();
    $provider->shouldReceive('redirect')->once()->andReturn(new RedirectResponse('https://github.com/login/oauth/authorize'));

    Socialite::shouldReceive('driver')->once()->with('github')->andReturn($provider);

    $this->get(route('auth.github.redirect'))
        ->assertRedirect('https://github.com/login/oauth/authorize');
});

test('github callback logs in an existing user by github id', function () {
    $user = User::factory()->create();
    $user->socialAccounts()->create([
        'provider' => 'github',
        'provider_user_id' => '12345',
    ]);

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->once()->andReturn(fakeGithubUser(
        id: '12345',
        email: $user->email,
        name: $user->name,
        nickname: 'envly-dev',
    ));

    Socialite::shouldReceive('driver')->once()->with('github')->andReturn($provider);

    $this->get(route('auth.github.callback'))
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user->fresh());

    $githubAccount = $user->fresh()->githubAccount()->first();

    expect($githubAccount)->not->toBeNull()
        ->and($githubAccount->username)->toBe('envly-dev');
});

test('github callback links github account to an existing user by email', function () {
    $user = User::factory()->create();

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->once()->andReturn(fakeGithubUser(
        id: '99999',
        email: $user->email,
        name: $user->name,
        nickname: 'envly-link',
    ));

    Socialite::shouldReceive('driver')->once()->with('github')->andReturn($provider);

    $this->get(route('auth.github.callback'))
        ->assertRedirect(route('dashboard', absolute: false));

    $user->refresh();

    $githubAccount = $user->githubAccount()->first();

    expect($githubAccount)->not->toBeNull()
        ->and($githubAccount->provider_user_id)->toBe('99999')
        ->and($githubAccount->username)->toBe('envly-link')
        ->and($githubAccount->access_token)->toBe('test-token');
});

test('authenticated user can connect and disconnect github account from profile', function () {
    $user = User::factory()->create();

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('scopes')->once()->with(['read:user', 'user:email'])->andReturnSelf();
    $provider->shouldReceive('redirect')->once()->andReturn(new RedirectResponse('https://github.com/login/oauth/authorize'));
    Socialite::shouldReceive('driver')->once()->with('github')->andReturn($provider);

    $this->actingAs($user)
        ->post(route('auth.github.connect'))
        ->assertRedirect('https://github.com/login/oauth/authorize');

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->once()->andReturn(fakeGithubUser(
        id: '55555',
        email: $user->email,
        name: $user->name,
        nickname: 'envly-connect',
    ));
    Socialite::shouldReceive('driver')->once()->with('github')->andReturn($provider);

    $this->actingAs($user)
        ->withSession(['github_connect_user_id' => $user->id])
        ->get(route('auth.github.callback'))
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->githubAccount()->first()?->provider_user_id)->toBe('55555');

    $this->actingAs($user)
        ->delete(route('auth.github.disconnect'))
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->githubAccount()->exists())->toBeFalse();
});

function fakeGithubUser(string $id, string $email, string $name, string $nickname): SocialiteUser
{
    return (new SocialiteUser)
        ->map([
            'id' => $id,
            'email' => $email,
            'name' => $name,
            'nickname' => $nickname,
            'avatar' => 'https://avatars.githubusercontent.com/u/'.$id,
        ])
        ->setRaw([])
        ->setToken('test-token');
}
