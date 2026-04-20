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

it('requires the current password to regenerate an environment token', function () {
    $user = User::factory()->create([
        'password' => 'secret-password',
    ]);
    $project = Project::factory()->for($user)->create();
    $environment = ProjectEnvironment::factory()->for($project)->create();
    $originalTokenHash = $environment->access_token_hash;

    $this->actingAs($user)
        ->from(route('projects.show', $project))
        ->post(route('projects.environments.token', [$project, $environment]), [
            'current_password' => 'wrong-password',
        ])
        ->assertRedirect(route('projects.show', $project))
        ->assertSessionHasErrors('current_password');

    expect($environment->refresh()->access_token_hash)->toBe($originalTokenHash)
        ->and($environment->versions()->count())->toBe(0);
});

it('stores token regeneration in environment history', function () {
    $user = User::factory()->create([
        'password' => 'secret-password',
    ]);
    $project = Project::factory()->for($user)->create();
    $environment = ProjectEnvironment::factory()->for($project)->create([
        'content' => "APP_NAME=Envly\nAPP_DEBUG=false\n",
    ]);
    $originalTokenHash = $environment->access_token_hash;

    $this->actingAs($user)
        ->post(route('projects.environments.token', [$project, $environment]), [
            'current_password' => 'secret-password',
        ])
        ->assertRedirect();

    $version = $environment->refresh()->versions()->latest()->first();

    expect($environment->access_token_hash)->not->toBe($originalTokenHash)
        ->and($version)->not->toBeNull()
        ->and($version?->summary)->toBe(__('messages.history.token_regenerated'))
        ->and($version?->previous_content)->toBe("APP_NAME=Envly\nAPP_DEBUG=false\n")
        ->and($version?->content)->toBe("APP_NAME=Envly\nAPP_DEBUG=false\n")
        ->and($version?->added_lines)->toBe(0)
        ->and($version?->removed_lines)->toBe(0);
});

it('includes detailed history entries on the project page', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $environment = ProjectEnvironment::factory()->for($project)->create([
        'content' => "APP_NAME=Current\nAPP_DEBUG=false\n",
    ]);

    EnvironmentVersion::factory()->for($environment)->for($user, 'creator')->create([
        'summary' => __('messages.history.environment_updated'),
        'previous_content' => "APP_NAME=Previous\n",
        'content' => "APP_NAME=Current\nAPP_DEBUG=false\n",
        'added_lines' => 1,
        'removed_lines' => 1,
    ]);

    EnvironmentVersion::factory()->for($environment)->for($user, 'creator')->create([
        'summary' => __('messages.history.token_regenerated'),
        'previous_content' => "APP_NAME=Current\nAPP_DEBUG=false\n",
        'content' => "APP_NAME=Current\nAPP_DEBUG=false\n",
        'added_lines' => 0,
        'removed_lines' => 0,
    ]);

    $this->actingAs($user)
        ->get(route('projects.show', $project))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Show')
            ->where('project.environments.0.versions.0.summary', __('messages.history.token_regenerated'))
            ->where('project.environments.0.versions.0.has_content_changes', false)
            ->where('project.environments.0.versions.1.summary', __('messages.history.environment_updated'))
            ->where('project.environments.0.versions.1.has_content_changes', true)
            ->where('project.environments.0.versions.1.previous_content', "APP_NAME=Previous\n")
            ->where('project.environments.0.versions.1.content', "APP_NAME=Current\nAPP_DEBUG=false\n")
        );
});
