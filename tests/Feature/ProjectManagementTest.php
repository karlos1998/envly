<?php

use App\Models\EnvironmentVersion;
use App\Models\Project;
use App\Models\ProjectEnvironment;
use App\Models\User;
use Illuminate\Support\Facades\Http;
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

it('creates a project with a laravel env template', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('projects.store'), [
            'name' => 'Laravel App',
            'template' => 'laravel',
        ])
        ->assertRedirect();

    $project = Project::query()->whereBelongsTo($user)->firstOrFail();
    $environment = $project->environments()->where('slug', 'main')->firstOrFail();

    expect($environment->content)->toStartWith("APP_NAME=Laravel\nAPP_ENV=local\nAPP_KEY=\n")
        ->and($environment->content)->toContain('DB_CONNECTION=sqlite')
        ->and($environment->content)->toContain('MAIL_FROM_NAME="${APP_NAME}"')
        ->and($environment->content)->toContain('VITE_APP_NAME="${APP_NAME}"');
});

it('does not store content history for an empty created environment', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $this->actingAs($user)
        ->post(route('projects.environments.store', $project), ['name' => 'Staging'])
        ->assertRedirect();

    $environment = $project->environments()->where('slug', 'staging')->firstOrFail();

    expect($environment->versions()->count())->toBe(0);
});

it('creates an environment by copying another environment content', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $sourceEnvironment = ProjectEnvironment::factory()->for($project)->create([
        'name' => 'Production',
        'slug' => 'production',
        'content' => "APP_NAME=Copied\nAPP_ENV=production\n",
    ]);

    $this->actingAs($user)
        ->post(route('projects.environments.store', $project), [
            'name' => 'Staging',
            'creation_mode' => 'copy',
            'source_environment_id' => $sourceEnvironment->id,
        ])
        ->assertRedirect();

    $environment = $project->environments()->where('slug', 'staging')->firstOrFail();

    expect($environment->content)->toBe("APP_NAME=Copied\nAPP_ENV=production\n")
        ->and($environment->versions()->count())->toBe(1);
});

it('does not copy an environment from another project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $otherProject = Project::factory()->for($user)->create();
    $sourceEnvironment = ProjectEnvironment::factory()->for($otherProject)->create([
        'name' => 'Production',
        'slug' => 'production',
        'content' => 'SECRET=external',
    ]);

    $this->actingAs($user)
        ->from(route('projects.show', $project))
        ->post(route('projects.environments.store', $project), [
            'name' => 'Staging',
            'creation_mode' => 'copy',
            'source_environment_id' => $sourceEnvironment->id,
        ])
        ->assertRedirect(route('projects.show', $project))
        ->assertSessionHasErrors('source_environment_id');

    expect($project->environments()->where('slug', 'staging')->exists())->toBeFalse();
});

it('deletes an environment after confirming its name', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    ProjectEnvironment::factory()->for($project)->create([
        'name' => 'main',
        'slug' => 'main',
    ]);
    $environment = ProjectEnvironment::factory()->for($project)->create([
        'name' => 'Staging',
        'slug' => 'staging',
    ]);

    $this->actingAs($user)
        ->delete(route('projects.environments.destroy', [$project, $environment]), [
            'name' => 'Staging',
        ])
        ->assertRedirect();

    $this->assertModelMissing($environment);
});

it('does not delete an environment when it is the only one in a project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $environment = ProjectEnvironment::factory()->for($project)->create([
        'name' => 'main',
        'slug' => 'main',
    ]);

    $this->actingAs($user)
        ->from(route('projects.show', $project))
        ->delete(route('projects.environments.destroy', [$project, $environment]), [
            'name' => 'main',
        ])
        ->assertRedirect(route('projects.show', $project))
        ->assertSessionHasErrors('name');

    expect($environment->refresh()->exists)->toBeTrue();
});

it('requires the environment name before deleting it', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    ProjectEnvironment::factory()->for($project)->create([
        'name' => 'main',
        'slug' => 'main',
    ]);
    $environment = ProjectEnvironment::factory()->for($project)->create([
        'name' => 'Staging',
        'slug' => 'staging',
    ]);

    $this->actingAs($user)
        ->from(route('projects.show', $project))
        ->delete(route('projects.environments.destroy', [$project, $environment]), [
            'name' => 'Production',
        ])
        ->assertRedirect(route('projects.show', $project))
        ->assertSessionHasErrors('name');

    expect($environment->refresh()->exists)->toBeTrue();
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

it('does not store environment history when content does not change', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $environment = ProjectEnvironment::factory()->for($project)->create([
        'content' => "APP_NAME=Same\nAPP_DEBUG=false\n",
    ]);

    $this->actingAs($user)
        ->put(route('projects.environments.update', [$project, $environment]), [
            'content' => "APP_NAME=Same\nAPP_DEBUG=false\n",
        ])
        ->assertRedirect();

    expect($environment->refresh()->content)->toBe("APP_NAME=Same\nAPP_DEBUG=false\n")
        ->and(EnvironmentVersion::query()->where('project_environment_id', $environment->id)->count())->toBe(0);
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

    EnvironmentVersion::factory()->for($environment, 'environment')->for($user, 'creator')->create([
        'summary' => __('messages.history.environment_updated'),
        'previous_content' => "APP_NAME=Previous\n",
        'content' => "APP_NAME=Current\nAPP_DEBUG=false\n",
        'added_lines' => 1,
        'removed_lines' => 1,
        'created_at' => now()->subMinute(),
    ]);

    EnvironmentVersion::factory()->for($environment, 'environment')->for($user, 'creator')->create([
        'summary' => __('messages.history.token_regenerated'),
        'previous_content' => "APP_NAME=Current\nAPP_DEBUG=false\n",
        'content' => "APP_NAME=Current\nAPP_DEBUG=false\n",
        'added_lines' => 0,
        'removed_lines' => 0,
        'created_at' => now(),
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

it('includes github connection state on project page when account is not connected', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('projects.show', $project))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Show')
            ->where('github.connected', false)
        );
});

it('includes github connection state on project page for connected account', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $user->socialAccounts()->create([
        'provider' => 'github',
        'provider_user_id' => '12345',
        'access_token' => 'github-token',
    ]);

    $this->actingAs($user)
        ->get(route('projects.show', $project))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Show')
            ->where('github.connected', true)
        );
});

it('shows github deploy configuration page', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('projects.github.edit', $project))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Projects/GithubDeploy')
            ->where('github.connected', false)
        );
});

it('returns github repositories from api endpoint', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $user->socialAccounts()->create([
        'provider' => 'github',
        'provider_user_id' => '12345',
        'access_token' => 'github-token',
    ]);

    Http::fake([
        'https://api.github.com/user/repos*' => Http::response([
            [
                'id' => 1001,
                'name' => 'envly-app',
                'full_name' => 'acme/envly-app',
                'default_branch' => 'main',
                'private' => true,
                'html_url' => 'https://github.com/acme/envly-app',
            ],
        ], 200),
    ]);

    $this->actingAs($user)
        ->get(route('projects.github.repositories', $project))
        ->assertSuccessful()
        ->assertJsonPath('repositories.0.full_name', 'acme/envly-app');
});

it('stores github deployment configuration for a project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $user->socialAccounts()->create([
        'provider' => 'github',
        'provider_user_id' => '12345',
        'access_token' => 'github-token',
    ]);

    Http::fake([
        'https://api.github.com/repos/acme/envly-app' => Http::response([
            'id' => 1001,
            'name' => 'envly-app',
            'full_name' => 'acme/envly-app',
            'default_branch' => 'main',
            'private' => true,
            'html_url' => 'https://github.com/acme/envly-app',
        ], 200),
        'https://api.github.com/repos/acme/envly-app/actions/workflows/8899' => Http::response([
            'id' => 8899,
            'name' => 'Deploy production',
            'path' => '.github/workflows/deploy.yml',
            'state' => 'active',
        ], 200),
    ]);

    $this->actingAs($user)
        ->put(route('projects.github.update', $project), [
            'repository_full_name' => 'acme/envly-app',
            'workflow_id' => '8899',
            'deploy_ref' => 'main',
        ])
        ->assertRedirect();

    expect($project->refresh()->github_repository_id)->toBe(1001)
        ->and($project->github_repository_full_name)->toBe('acme/envly-app')
        ->and($project->github_workflow_id)->toBe('8899')
        ->and($project->github_workflow_name)->toBe('Deploy production')
        ->and($project->github_deploy_ref)->toBe('main');
});

it('dispatches selected github workflow for configured project deployment', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create([
        'github_repository_full_name' => 'acme/envly-app',
        'github_workflow_id' => '8899',
        'github_deploy_ref' => 'main',
    ]);
    $user->socialAccounts()->create([
        'provider' => 'github',
        'provider_user_id' => '12345',
        'access_token' => 'github-token',
    ]);

    Http::fake([
        'https://api.github.com/repos/acme/envly-app/actions/workflows/8899/dispatches' => Http::response('', 204),
    ]);

    $this->actingAs($user)
        ->post(route('projects.github.deploy', $project))
        ->assertRedirect();

    Http::assertSent(function ($request): bool {
        return $request->url() === 'https://api.github.com/repos/acme/envly-app/actions/workflows/8899/dispatches'
            && $request['ref'] === 'main'
            && $request->method() === 'POST';
    });
});

it('updates github repository secret for current environment token', function () {
    if (! function_exists('sodium_crypto_box_publickey')) {
        $this->markTestSkipped('Libsodium extension is required.');
    }

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create([
        'github_repository_full_name' => 'acme/envly-app',
    ]);
    $environment = ProjectEnvironment::factory()->for($project)->create([
        'access_token' => 'new-env-token',
    ]);
    $user->socialAccounts()->create([
        'provider' => 'github',
        'provider_user_id' => '12345',
        'access_token' => 'github-token',
    ]);

    $keyPair = sodium_crypto_box_keypair();
    $publicKey = sodium_crypto_box_publickey($keyPair);

    Http::fake([
        'https://api.github.com/repos/acme/envly-app/actions/secrets/public-key' => Http::response([
            'key_id' => 'test-key-id',
            'key' => base64_encode($publicKey),
        ], 200),
        'https://api.github.com/repos/acme/envly-app/actions/secrets/ENVLY_TOKEN' => Http::response('', 201),
    ]);

    $this->actingAs($user)
        ->post(route('projects.environments.github_secret', [$project, $environment]))
        ->assertRedirect();

    Http::assertSent(function ($request): bool {
        return $request->method() === 'PUT'
            && $request->url() === 'https://api.github.com/repos/acme/envly-app/actions/secrets/ENVLY_TOKEN'
            && $request['key_id'] === 'test-key-id'
            && is_string($request['encrypted_value'])
            && $request['encrypted_value'] !== '';
    });
});
