<?php

namespace App\Policies;

use App\Models\ProjectEnvironment;
use App\Models\User;

class ProjectEnvironmentPolicy
{
    public function update(User $user, ProjectEnvironment $projectEnvironment): bool
    {
        return $projectEnvironment->project()->where('user_id', $user->id)->exists();
    }

    public function delete(User $user, ProjectEnvironment $projectEnvironment): bool
    {
        return $projectEnvironment->project()->where('user_id', $user->id)->exists();
    }
}
