<?php

namespace App\Http\Controllers;

use App\Http\Requests\Environment\DeleteEnvironmentRequest;
use App\Http\Requests\Environment\RegenerateEnvironmentTokenRequest;
use App\Http\Requests\Environment\StoreEnvironmentRequest;
use App\Http\Requests\Environment\UpdateEnvironmentRequest;
use App\Models\Project;
use App\Models\ProjectEnvironment;
use App\Services\EnvironmentService;
use App\Services\GithubDeploymentService;
use Illuminate\Http\RedirectResponse;
use RuntimeException;

class EnvironmentController extends Controller
{
    public function __construct(
        private EnvironmentService $service,
        private GithubDeploymentService $github,
    ) {}

    public function store(StoreEnvironmentRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $this->service->create(
            project: $project,
            name: $request->validated('name'),
            actor: $request->user(),
            content: $request->validated('content') ?? '',
            sourceEnvironmentId: $request->sourceEnvironmentId(),
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

    public function destroy(DeleteEnvironmentRequest $request, Project $project, ProjectEnvironment $environment): RedirectResponse
    {
        abort_unless($environment->project_id === $project->id, 404);

        $this->authorize('delete', $environment);

        $this->service->delete($project, $environment, $request->user());

        return back()->with('success', __('messages.flash.environment_deleted'));
    }

    public function syncGithubSecret(Project $project, ProjectEnvironment $environment): RedirectResponse
    {
        abort_unless($environment->project_id === $project->id, 404);

        $this->authorize('update', $environment);

        if (! $project->github_repository_full_name) {
            return back()->with('error', __('messages.flash.github_project_not_configured'));
        }

        $githubAccount = request()->user()->githubAccount()->first();

        if ($githubAccount === null) {
            return back()->with('error', __('messages.flash.github_not_connected'));
        }

        try {
            $this->github->upsertRepositorySecret(
                account: $githubAccount,
                repositoryFullName: $project->github_repository_full_name,
                secretName: 'ENVLY_TOKEN',
                secretValue: $environment->access_token,
            );
        } catch (RuntimeException $exception) {
            return back()->with('error', __('messages.flash.github_secret_update_failed_with_details', [
                'details' => $exception->getMessage(),
            ]));
        }

        return back()->with('success', __('messages.flash.github_secret_updated'));
    }
}
