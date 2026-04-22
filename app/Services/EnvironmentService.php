<?php

namespace App\Services;

use App\Models\EnvironmentVersion;
use App\Models\Project;
use App\Models\ProjectEnvironment;
use App\Models\User;
use App\Repositories\ProjectEnvironmentRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EnvironmentService
{
    public function __construct(
        private AccessTokenService $tokens,
        private EnvironmentDiffService $diffs,
        private ProjectEnvironmentRepository $environments,
    ) {}

    public function create(Project $project, string $name, ?User $actor = null, string $content = '', ?int $sourceEnvironmentId = null): ProjectEnvironment
    {
        $slug = $this->makeUniqueSlug($project, $name);
        $token = $this->tokens->generate();
        $sourceEnvironment = $sourceEnvironmentId === null ? null : $this->environments->findForProject($project, $sourceEnvironmentId);

        if ($sourceEnvironmentId !== null && $sourceEnvironment === null) {
            throw ValidationException::withMessages([
                'source_environment_id' => __('messages.validation.environment_copy_source_invalid'),
            ]);
        }

        $content = $sourceEnvironment?->content ?? $content;

        return DB::transaction(function () use ($project, $name, $slug, $token, $actor, $content): ProjectEnvironment {
            $environment = ProjectEnvironment::create([
                'project_id' => $project->id,
                'name' => $name,
                'slug' => $slug,
                'content' => $content,
                'access_token' => $token,
                'access_token_hash' => $this->tokens->hash($token),
            ]);

            $changes = $this->diffs->countChangedLines(null, $content);

            if ($changes['added'] > 0 || $changes['removed'] > 0) {
                $this->createVersion($environment, null, $content, $actor, __('messages.history.environment_created'), $changes);
            }

            activity()
                ->causedBy($actor)
                ->performedOn($environment)
                ->event('created')
                ->log('environment.created');

            return $environment;
        });
    }

    public function updateContent(ProjectEnvironment $environment, string $content, User $actor): ?EnvironmentVersion
    {
        return DB::transaction(function () use ($environment, $content, $actor): ?EnvironmentVersion {
            $previousContent = $environment->content;
            $changes = $this->diffs->countChangedLines($previousContent, $content);

            if ($changes['added'] === 0 && $changes['removed'] === 0) {
                return null;
            }

            $environment->update(['content' => $content]);

            $version = $this->createVersion(
                environment: $environment,
                previousContent: $previousContent,
                content: $content,
                actor: $actor,
                summary: __('messages.history.environment_updated'),
                changes: $changes,
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

    public function delete(Project $project, ProjectEnvironment $environment, User $actor): void
    {
        if ($environment->project_id !== $project->id) {
            throw ValidationException::withMessages([
                'name' => __('messages.validation.environment_invalid_project'),
            ]);
        }

        if ($this->environments->countForProject($project) <= 1) {
            throw ValidationException::withMessages([
                'name' => __('messages.validation.environment_last_one'),
            ]);
        }

        DB::transaction(function () use ($environment, $actor): void {
            activity()
                ->causedBy($actor)
                ->performedOn($environment)
                ->event('deleted')
                ->log('environment.deleted');

            $environment->delete();
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

    /**
     * @param  array{added:int, removed:int}|null  $changes
     */
    private function createVersion(ProjectEnvironment $environment, ?string $previousContent, string $content, ?User $actor, string $summary, ?array $changes = null): EnvironmentVersion
    {
        $changes ??= $this->diffs->countChangedLines($previousContent, $content);

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
