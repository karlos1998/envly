<?php

use App\Models\Project;
use App\Models\ProjectEnvironment;
use App\Models\User;
use App\Services\AccessTokenService;

it('returns plain text env content for a valid project identifier and environment token', function () {
    $tokenService = app(AccessTokenService::class);
    $token = $tokenService->generate();
    $project = Project::factory()->for(User::factory())->create(['identifier' => 'client-app']);
    $environment = ProjectEnvironment::factory()->for($project)->create([
        'content' => "APP_NAME=Client\nAPP_ENV=production\n",
        'access_token' => $token,
        'access_token_hash' => $tokenService->hash($token),
    ]);

    $this->withHeaders([
        'Authorization' => 'Bearer '.$token,
    ])->get('/api/env/client-app')
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSeeText("APP_NAME=Client\nAPP_ENV=production\n");

    expect($environment->refresh()->last_exported_at)->not->toBeNull();
});

it('does not leak env content for an invalid token', function () {
    $project = Project::factory()->for(User::factory())->create(['identifier' => 'client-app']);
    ProjectEnvironment::factory()->for($project)->create(['content' => 'SECRET=value']);

    $this->withHeaders([
        'Authorization' => 'Bearer wrong-token',
    ])->get('/api/env/client-app')->assertNotFound();
});

it('does not return env content when bearer token is missing', function () {
    $project = Project::factory()->for(User::factory())->create(['identifier' => 'client-app']);
    ProjectEnvironment::factory()->for($project)->create(['content' => 'SECRET=value']);

    $this->get('/api/env/client-app')->assertNotFound();
});
