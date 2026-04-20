<?php

use App\Models\EnvironmentVersion;
use App\Models\Project;
use App\Models\ProjectEnvironment;
use App\Models\User;

it('creates a project with a globally unique identifier and main environment', function () {
    Project::factory()->create(['identifier' => 'moj-projekt']);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('projects.store'), ['name' => 'Mój projekt'])
        ->assertRedirect();

    $project = Project::query()->whereBelongsTo($user)->firstOrFail();

    expect($project->identifier)->toBe('moj-projekt-2')
        ->and($project->environments()->where('slug', 'main')->exists())->toBeTrue();
});

it('stores a new environment version when content changes', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $environment = ProjectEnvironment::factory()->for($project)->create([
        'content' => "APP_NAME=Old\n",
    ]);

    $this->actingAs($user)
        ->put(route('projects.environments.update', [$project, $environment]), [
            'content' => "APP_NAME=New\nAPP_DEBUG=false\n",
        ])
        ->assertRedirect();

    expect($environment->refresh()->content)->toBe("APP_NAME=New\nAPP_DEBUG=false\n")
        ->and(EnvironmentVersion::query()->where('project_environment_id', $environment->id)->count())->toBe(1);
});
