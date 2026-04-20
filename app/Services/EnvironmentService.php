<?php

namespace App\Services;

use App\Models\EnvironmentVersion;
use App\Models\Project;
use App\Models\ProjectEnvironment;
use App\Models\User;
use App\Repositories\ProjectEnvironmentRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EnvironmentService
{
    public function __construct(
        private AccessTokenService $tokens,
        private EnvironmentDiffService $diffs,
        private ProjectEnvironmentRepository $environments,
    ) {}

    public function create(Project $project, string $name, ?User $actor = null, string $content = ''): ProjectEnvironment
    {
        $slug = $this->makeUniqueSlug($project, $name);
        $token = $this->tokens->generate();

        return DB::transaction(function () use ($project, $name, $slug, $token, $actor, $content): ProjectEnvironment {
            $environment = ProjectEnvironment::create([
                'project_id' => $project->id,
                'name' => $name,
                'slug' => $slug,
                'content' => $content,
                'access_token' => $token,
                'access_token_hash' => $this->tokens->hash($token),
            ]);

            $this->createVersion($environment, null, $content, $actor, __('messages.history.environment_created'));

            activity()
                ->causedBy($actor)
                ->performedOn($environment)
                ->event('created')
                ->log('environment.created');

            return $environment;
        });
    }

    public function updateContent(ProjectEnvironment $environment, string $content, User $actor): EnvironmentVersion
    {
        return DB::transaction(function () use ($environment, $content, $actor): EnvironmentVersion {
            $previousContent = $environment->content;

            $environment->update(['content' => $content]);

            $version = $this->createVersion(
                environment: $environment,
                previousContent: $previousContent,
                content: $content,
                actor: $actor,
                summary: __('messages.history.environment_updated'),
            );

            activity()
                ->causedBy($actor)
                ->performedOn($environment)
                ->event('updated')
                ->withProperties([
                    'version_id' => $version->id,
                    'line_count' => $environment->line_count,
                ])
                ->log('environment.content_updated');

            return $version;
        });
    }

    public function regenerateToken(ProjectEnvironment $environment, User $actor): ProjectEnvironment
    {
        return DB::transaction(function () use ($environment, $actor): ProjectEnvironment {
            $token = $this->tokens->generate();

            $environment->update([
                'access_token' => $token,
                'access_token_hash' => $this->tokens->hash($token),
            ]);

            $version = $this->createVersion(
                environment: $environment,
                previousContent: $environment->content,
                content: $environment->content,
                actor: $actor,
                summary: __('messages.history.token_regenerated'),
            );

            activity()
                ->causedBy($actor)
                ->performedOn($environment)
                ->event('updated')
                ->withProperties([
                    'version_id' => $version->id,
                    'line_count' => $environment->line_count,
                ])
                ->log('environment.token_regenerated');

            return $environment->refresh();
        });
    }

    public function markExported(ProjectEnvironment $environment): void
    {
        $environment->forceFill(['last_exported_at' => now()])->save();
    }

    private function makeUniqueSlug(Project $project, string $name): string
    {
        $baseSlug = Str::slug($name) ?: 'environment';
        $slug = $baseSlug;
        $counter = 2;

        while ($this->environments->slugExists($project, $slug)) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function createVersion(ProjectEnvironment $environment, ?string $previousContent, string $content, ?User $actor, string $summary): EnvironmentVersion
    {
        $changes = $this->diffs->countChangedLines($previousContent, $content);

        return $environment->versions()->create([
            'created_by_id' => $actor?->id,
            'previous_content' => $previousContent,
            'content' => $content,
            'added_lines' => $changes['added'],
            'removed_lines' => $changes['removed'],
            'summary' => $summary,
        ]);
    }
}
