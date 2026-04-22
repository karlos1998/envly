<?php

namespace App\Repositories;

use App\Models\Project;
use App\Models\ProjectEnvironment;

class ProjectEnvironmentRepository
{
    public function findForApi(string $projectIdentifier, string $plainToken): ?ProjectEnvironment
    {
        return ProjectEnvironment::query()
            ->where('access_token_hash', hash('sha256', $plainToken))
            ->whereHas('project', fn ($query) => $query->where('identifier', $projectIdentifier))
            ->with('project')
            ->first();
    }

    public function slugExists(Project $project, string $slug): bool
    {
        return ProjectEnvironment::query()
            ->whereBelongsTo($project)
            ->where('slug', $slug)
            ->exists();
    }

    public function findForProject(Project $project, int $environmentId): ?ProjectEnvironment
    {
        return ProjectEnvironment::query()
            ->whereBelongsTo($project)
            ->whereKey($environmentId)
            ->first();
    }

    public function countForProject(Project $project): int
    {
        return ProjectEnvironment::query()
            ->whereBelongsTo($project)
            ->count();
    }
}
