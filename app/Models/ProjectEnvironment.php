<?php

namespace App\Models;

use Database\Factories\ProjectEnvironmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable(['project_id', 'name', 'slug', 'content', 'access_token', 'access_token_hash', 'last_exported_at'])]
class ProjectEnvironment extends Model
{
    /** @use HasFactory<ProjectEnvironmentFactory> */
    use HasFactory, LogsActivity;

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return HasMany<EnvironmentVersion, $this>
     */
    public function versions(): HasMany
    {
        return $this->hasMany(EnvironmentVersion::class);
    }

    public function getLineCountAttribute(): int
    {
        if ($this->content === null || $this->content === '') {
            return 0;
        }

        return substr_count($this->content, "\n") + 1;
    }

    public function getMaskedTokenAttribute(): string
    {
        return substr($this->access_token, 0, 10).'...'.substr($this->access_token, -6);
    }

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'last_exported_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'access_token_hash'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
