<?php

namespace App\Repositories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProjectRepository
{
    /**
     * @return Collection<int, Project>
     */
    public function forUser(User $user, ?string $search = null): Collection
    {
        return Project::query()
            ->whereBelongsTo($user)
            ->when($search, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('identifier', 'like', "%{$search}%");
                });
            })
            ->with(['environments' => fn ($query) => $query->latest('updated_at')])
            ->latest('updated_at')
            ->get();
    }

    public function findOwnedByIdentifier(User $user, string $identifier): ?Project
    {
        return Project::query()
            ->whereBelongsTo($user)
            ->where('identifier', $identifier)
            ->with(['environments.versions.creator'])
            ->first();
    }

    public function identifierExists(string $identifier): bool
    {
        return Project::query()->where('identifier', $identifier)->exists();
    }
}
