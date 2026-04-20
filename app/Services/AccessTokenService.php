<?php

namespace App\Services;

use Illuminate\Support\Str;

class AccessTokenService
{
    public function generate(): string
    {
        return 'envly_'.Str::random(48);
    }

    public function hash(string $token): string
    {
        return hash('sha256', $token);
    }
}
