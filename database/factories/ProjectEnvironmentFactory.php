<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectEnvironment;
use App\Services\AccessTokenService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectEnvironment>
 */
class ProjectEnvironmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tokenService = app(AccessTokenService::class);
        $token = $tokenService->generate();

        return [
            'project_id' => Project::factory(),
            'name' => 'main',
            'slug' => 'main',
            'content' => "APP_NAME=Envly\nAPP_ENV=testing\n",
            'access_token' => $token,
            'access_token_hash' => $tokenService->hash($token),
        ];
    }
}
