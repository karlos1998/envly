<?php

namespace App\Models;

use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable(['user_id', 'name', 'identifier'])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory, LogsActivity;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<ProjectEnvironment, $this>
     */
    public function environments(): HasMany
    {
        return $this->hasMany(ProjectEnvironment::class);
    }

    public function mainEnvironment(): ?ProjectEnvironment
    {
        return $this->environments->firstWhere('slug', 'main');
    }

    public function getRouteKeyName(): string
    {
        return 'identifier';
    }

    public function getDisplayNameAttribute(): string
    {
        return sprintf('%s (%s)', $this->name, $this->identifier);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'identifier'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
