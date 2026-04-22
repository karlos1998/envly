<?php

namespace App\Models;

use Database\Factories\EnvironmentVersionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['project_environment_id', 'created_by_id', 'previous_content', 'content', 'added_lines', 'removed_lines', 'summary'])]
class EnvironmentVersion extends Model
{
    /** @use HasFactory<EnvironmentVersionFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<ProjectEnvironment, $this>
     */
    public function environment(): BelongsTo
    {
        return $this->belongsTo(ProjectEnvironment::class, 'project_environment_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
