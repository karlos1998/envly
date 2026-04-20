<?php

use App\Models\EnvironmentVersion;
use App\Models\Project;
use App\Models\ProjectEnvironment;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

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

it('searches owned projects by name or identifier', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Project::factory()->for($user)->create([
        'name' => 'Billing API',
        'identifier' => 'billing-api',
    ]);
    Project::factory()->for($user)->create([
        'name' => 'Marketing Site',
        'identifier' => 'marketing-site',
    ]);
    Project::factory()->for($otherUser)->create([
        'name' => 'Billing API Clone',
        'identifier' => 'billing-api-clone',
    ]);

    $this->actingAs($user)
        ->get(route('projects.index', ['search' => 'bill']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->where('filters.search', 'bill')
            ->where('projects.0.name', 'Billing API')
            ->missing('projects.1')
        );
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
