<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectEnvironment;
use Illuminate\Support\Collection;

class ProjectPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function project(Project $project): array
    {
        $project->loadMissing(['environments.versions.creator']);

        return [
            'id' => $project->id,
            'name' => $project->name,
            'identifier' => $project->identifier,
            'display_name' => $project->display_name,
            'created_at' => $project->created_at?->toISOString(),
            'updated_at' => $project->updated_at?->toISOString(),
            'environments' => $project->environments
                ->sortBy(fn (ProjectEnvironment $environment): bool => $environment->slug !== 'main')
                ->values()
                ->map(fn (ProjectEnvironment $environment): array => $this->environment($environment))
                ->all(),
        ];
    }

    /**
     * @param  Collection<int, Project>  $projects
     * @return list<array<string, mixed>>
     */
    public function projects(Collection $projects): array
    {
        return $projects->map(fn (Project $project): array => $this->project($project))->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function environment(ProjectEnvironment $environment): array
    {
        $environment->loadMissing(['versions.creator']);

        return [
            'id' => $environment->id,
            'name' => $environment->name,
            'slug' => $environment->slug,
            'content' => $environment->content ?? '',
            'access_token' => $environment->access_token,
            'masked_token' => $environment->masked_token,
            'line_count' => $environment->line_count,
            'last_exported_at' => $environment->last_exported_at?->toISOString(),
            'updated_at' => $environment->updated_at?->toISOString(),
            'versions' => $environment->versions
                ->sortByDesc('created_at')
                ->values()
                ->map(fn ($version): array => [
                    'id' => $version->id,
                    'summary' => $version->summary,
                    'added_lines' => $version->added_lines,
                    'removed_lines' => $version->removed_lines,
                    'has_content_changes' => $version->previous_content !== $version->content,
                    'content' => $version->content,
                    'previous_content' => $version->previous_content,
                    'created_at' => $version->created_at?->toISOString(),
                    'creator' => $version->creator ? [
                        'id' => $version->creator->id,
                        'name' => $version->creator->name,
                    ] : null,
                ])
                ->all(),
        ];
    }
}
