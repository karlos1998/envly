<?php

namespace App\Http\Controllers;

use App\Http\Requests\Project\ListGithubWorkflowsRequest;
use App\Http\Requests\Project\UpdateGithubDeploymentRequest;
use App\Models\Project;
use App\Models\SocialAccount;
use App\Services\GithubDeploymentService;
use App\Services\ProjectPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class ProjectGithubDeploymentController extends Controller
{
    public function __construct(
        private GithubDeploymentService $github,
        private ProjectPresenter $presenter,
    ) {}

    public function edit(Project $project): Response
    {
        $this->authorize('update', $project);

        return Inertia::render('Projects/GithubDeploy', [
            'project' => $this->presenter->project($project),
            'github' => [
                'connected' => $project->user->githubAccount()->exists(),
            ],
        ]);
    }

    public function repositories(Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $githubAccount = $this->resolveGithubAccount(request()->user()->getKey());

        if ($githubAccount === null) {
            return response()->json(['message' => __('messages.flash.github_not_connected')], 422);
        }

        try {
            return response()->json([
                'repositories' => $this->github->listRepositories($githubAccount),
            ]);
        } catch (RuntimeException) {
            return response()->json(['message' => __('messages.flash.github_api_failed')], 502);
        }
    }

    public function workflows(ListGithubWorkflowsRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $githubAccount = $this->resolveGithubAccount($request->user()->getKey());

        if ($githubAccount === null) {
            return response()->json(['message' => __('messages.flash.github_not_connected')], 422);
        }

        $repository = $request->validated('repository');

        $selectedRepository = $this->github->findRepository($githubAccount, $repository);

        if ($selectedRepository === null) {
            return response()->json(['message' => __('messages.flash.github_repository_not_accessible')], 404);
        }

        try {
            return response()->json([
                'workflows' => $this->github->listWorkflows($githubAccount, $selectedRepository['full_name']),
            ]);
        } catch (RuntimeException) {
            return response()->json(['message' => __('messages.flash.github_api_failed')], 502);
        }
    }

    public function update(UpdateGithubDeploymentRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $githubAccount = $this->resolveGithubAccount($request->user()->getKey());

        if ($githubAccount === null) {
            return back()->with('error', __('messages.flash.github_not_connected'));
        }

        $validated = $request->validated();
        $repositoryFullName = $validated['repository_full_name'];
        $workflowId = $validated['workflow_id'];

        try {
            $repository = $this->github->findRepository($githubAccount, $repositoryFullName);

            if ($repository === null) {
                return back()->with('error', __('messages.flash.github_repository_not_accessible'));
            }

            $workflow = $this->github->findWorkflow($githubAccount, $repository['full_name'], $workflowId);

            if ($workflow === null) {
                return back()->with('error', __('messages.flash.github_workflow_not_found'));
            }
        } catch (RuntimeException) {
            return back()->with('error', __('messages.flash.github_api_failed'));
        }

        $project->update([
            'github_repository_id' => $repository['id'],
            'github_repository_full_name' => $repository['full_name'],
            'github_workflow_id' => $workflow['id'],
            'github_workflow_name' => $workflow['name'],
            'github_deploy_ref' => $validated['deploy_ref'] ?: $repository['default_branch'],
        ]);

        return back()->with('success', __('messages.flash.github_project_linked'));
    }

    public function deploy(Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $user = request()->user();
        $githubAccount = $this->resolveGithubAccount($user->getKey());

        if ($githubAccount === null) {
            return back()->with('error', __('messages.flash.github_not_connected'));
        }

        if (! $project->github_repository_full_name || ! $project->github_workflow_id) {
            return back()->with('error', __('messages.flash.github_project_not_configured'));
        }

        $ref = $project->github_deploy_ref ?: 'main';

        try {
            $this->github->dispatchWorkflow($githubAccount, $project->github_repository_full_name, $project->github_workflow_id, $ref);
        } catch (RuntimeException) {
            return back()->with('error', __('messages.flash.github_deploy_failed'));
        }

        return back()->with('success', __('messages.flash.github_deploy_started'));
    }

    private function resolveGithubAccount(int $userId): ?SocialAccount
    {
        return SocialAccount::query()
            ->where('user_id', $userId)
            ->where('provider', 'github')
            ->first();
    }
}
