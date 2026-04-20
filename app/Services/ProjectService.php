<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    public function __construct(
        private ProjectIdentifierService $identifiers,
        private EnvironmentService $environments,
    ) {}

    public function create(User $user, string $name): Project
    {
        return DB::transaction(function () use ($user, $name): Project {
            $project = Project::create([
                'user_id' => $user->id,
                'name' => $name,
                'identifier' => $this->identifiers->make($name),
            ]);

            $this->environments->create($project, 'main', $user, "APP_NAME=\nAPP_ENV=production\n");

            activity()
                ->causedBy($user)
                ->performedOn($project)
                ->event('created')
                ->log('project.created');

            return $project->load('environments.versions.creator');
        });
    }

    public function update(Project $project, string $name, User $actor): Project
    {
        $project->update(['name' => $name]);

        activity()
            ->causedBy($actor)
            ->performedOn($project)
            ->event('updated')
            ->log('project.updated');

        return $project->refresh()->load('environments.versions.creator');
    }

    public function delete(Project $project, User $actor): void
    {
        activity()
            ->causedBy($actor)
            ->performedOn($project)
            ->event('deleted')
            ->log('project.deleted');

        $project->delete();
    }
}
