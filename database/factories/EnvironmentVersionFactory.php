<?php

namespace Database\Factories;

use App\Models\EnvironmentVersion;
use App\Models\ProjectEnvironment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EnvironmentVersion>
 */
class EnvironmentVersionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_environment_id' => ProjectEnvironment::factory(),
            'created_by_id' => User::factory(),
            'previous_content' => null,
            'content' => "APP_NAME=Envly\n",
            'added_lines' => 1,
            'removed_lines' => 0,
            'summary' => 'Environment updated',
        ];
    }
}
