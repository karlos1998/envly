<?php

namespace App\Http\Middleware;

use App\Enums\Locale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'success' => fn (): ?string => $request->session()->get('success'),
                'error' => fn (): ?string => $request->session()->get('error'),
            ],
            'locale' => app()->getLocale(),
            'locales' => collect(Locale::cases())->map(fn (Locale $locale): array => [
                'value' => $locale->value,
                'label' => $locale->label(),
            ])->values()->all(),
            'translations' => Lang::get('messages'),
        ];
    }
}
