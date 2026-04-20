<?php

namespace App\Http\Controllers;

use App\Http\Requests\Environment\RegenerateEnvironmentTokenRequest;
use App\Http\Requests\Environment\StoreEnvironmentRequest;
use App\Http\Requests\Environment\UpdateEnvironmentRequest;
use App\Models\Project;
use App\Models\ProjectEnvironment;
use App\Services\EnvironmentService;
use Illuminate\Http\RedirectResponse;

class EnvironmentController extends Controller
{
    public function __construct(private EnvironmentService $service) {}

    public function store(StoreEnvironmentRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $this->service->create(
            project: $project,
            name: $request->validated('name'),
            actor: $request->user(),
            content: $request->validated('content') ?? '',
        );

        return back()->with('success', __('messages.flash.environment_created'));
    }

    public function update(UpdateEnvironmentRequest $request, Project $project, ProjectEnvironment $environment): RedirectResponse
    {
        abort_unless($environment->project_id === $project->id, 404);

        $this->authorize('update', $environment);

        $this->service->updateContent($environment, $request->validated('content'), $request->user());

        return back()->with('success', __('messages.flash.environment_saved'));
    }

    public function regenerateToken(RegenerateEnvironmentTokenRequest $request, Project $project, ProjectEnvironment $environment): RedirectResponse
    {
        abort_unless($environment->project_id === $project->id, 404);

        $this->authorize('update', $environment);

        $this->service->regenerateToken($environment, $request->user());

        return back()->with('success', __('messages.flash.token_regenerated'));
    }
}
