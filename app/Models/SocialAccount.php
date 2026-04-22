<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'provider',
    'provider_user_id',
    'username',
    'name',
    'email',
    'avatar_url',
    'access_token',
    'refresh_token',
    'token_expires_at',
    'connected_at',
    'scopes',
    'provider_data',
])]
#[Hidden(['access_token', 'refresh_token'])]
class SocialAccount extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'token_expires_at' => 'datetime',
            'connected_at' => 'datetime',
            'scopes' => 'array',
            'provider_data' => 'array',
        ];
    }
}
