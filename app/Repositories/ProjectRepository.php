<?php

namespace App\Repositories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ProjectRepository
{
    /**
     * @return Collection<int, Project>
     */
    public function forUser(User $user): Collection
    {
        return Project::query()
            ->whereBelongsTo($user)
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
