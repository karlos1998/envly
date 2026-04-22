<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Locale;
use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Throwable;

class GithubAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('github')
            ->scopes(['read:user', 'user:email', 'repo', 'workflow'])
            ->redirect();
    }

    public function connect(Request $request): RedirectResponse
    {
        $request->session()->put('github_connect_user_id', $request->user()->getKey());

        return $this->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            /** @var SocialiteUser $githubUser */
            $githubUser = Socialite::driver('github')->user();
        } catch (Throwable) {
            return redirect()->route('login')->with('error', trans('flash.github_login_failed'));
        }

        $connectUserId = $request->session()->pull('github_connect_user_id');

        if ($connectUserId) {
            return $this->handleConnectCallback($connectUserId, $githubUser);
        }

        return $this->handleLoginCallback($request, $githubUser);
    }

    public function disconnect(Request $request): RedirectResponse
    {
        $request->user()->githubAccount()->delete();

        return redirect()->route('profile.edit')->with('success', trans('flash.github_disconnected'));
    }

    private function handleConnectCallback(int|string $connectUserId, SocialiteUser $githubUser): RedirectResponse
    {
        $user = User::query()->find($connectUserId);

        if (! $user) {
            return redirect()->route('login')->with('error', trans('flash.github_login_failed'));
        }

        $existingGithubOwner = SocialAccount::query()
            ->where('provider', 'github')
            ->where('provider_user_id', (string) $githubUser->getId())
            ->where('user_id', '!=', $user->getKey())
            ->exists();

        if ($existingGithubOwner) {
            return redirect()->route('profile.edit')->with('error', trans('flash.github_already_connected'));
        }

        $this->syncGithubData($user, $githubUser);
        $user->save();

        return redirect()->route('profile.edit')->with('success', trans('flash.github_connected'));
    }

    private function handleLoginCallback(Request $request, SocialiteUser $githubUser): RedirectResponse
    {
        $githubId = (string) $githubUser->getId();

        $socialAccount = SocialAccount::query()
            ->where('provider', 'github')
            ->where('provider_user_id', $githubId)
            ->first();

        $user = $socialAccount?->user;

        if (! $user && $githubUser->getEmail()) {
            $user = User::query()->where('email', $githubUser->getEmail())->first();
        }

        if (! $user) {
            if (! $githubUser->getEmail()) {
                return redirect()->route('login')->with('error', trans('flash.github_missing_email'));
            }

            $user = User::create([
                'name' => $githubUser->getName() ?: $githubUser->getNickname() ?: 'GitHub User',
                'email' => $githubUser->getEmail(),
                'locale' => Locale::fromBrowserHeader($request->header('Accept-Language'))->value,
                'password' => Hash::make(Str::random(64)),
            ]);
        }

        $this->syncGithubData($user, $githubUser);
        $user->save();

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function syncGithubData(User $user, SocialiteUser $githubUser): void
    {
        $socialAccount = $user->socialAccounts()->firstOrNew(['provider' => 'github']);

        $socialAccount->fill([
            'provider_user_id' => (string) $githubUser->getId(),
            'username' => $githubUser->getNickname(),
            'name' => $githubUser->getName(),
            'email' => $githubUser->getEmail(),
            'avatar_url' => $githubUser->getAvatar(),
            'access_token' => $githubUser->token,
            'refresh_token' => $githubUser->refreshToken,
            'token_expires_at' => $githubUser->expiresIn ? now()->addSeconds((int) $githubUser->expiresIn) : null,
            'connected_at' => now(),
            'scopes' => $githubUser->approvedScopes,
            'provider_data' => $githubUser->getRaw(),
        ]);
        $socialAccount->save();

        if (! $user->email_verified_at && $githubUser->getEmail() && $user->email === $githubUser->getEmail()) {
            $user->email_verified_at = now();
        }
    }
}
