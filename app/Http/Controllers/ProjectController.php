<?php

namespace App\Http\Controllers;

use App\Http\Requests\Project\SearchProjectsRequest;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Models\Project;
use App\Repositories\ProjectRepository;
use App\Services\EnvTemplateCatalog;
use App\Services\ProjectPresenter;
use App\Services\ProjectService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function __construct(
        private ProjectRepository $projects,
        private ProjectService $service,
        private ProjectPresenter $presenter,
        private EnvTemplateCatalog $templates,
    ) {}

    public function index(SearchProjectsRequest $request): Response
    {
        $search = $request->searchTerm();

        return Inertia::render('Projects/Index', [
            'filters' => [
                'search' => $search ?? '',
            ],
            'envTemplateOptions' => $this->templates->options(),
            'projects' => $this->presenter->projects($this->projects->forUser($request->user(), $search)),
        ]);
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $project = $this->service->create($request->user(), $request->validated('name'), $request->template());

        return to_route('projects.show', $project)
            ->with('success', __('messages.flash.project_created'));
    }

    public function show(Project $project): Response
    {
        $this->authorize('view', $project);

        return Inertia::render('Projects/Show', [
            'project' => $this->presenter->project($project),
            'github' => [
                'connected' => $project->user->githubAccount()->exists(),
            ],
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $this->service->update($project, $request->validated('name'), $request->user());

        return back()->with('success', __('messages.flash.project_updated'));
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $this->service->delete($project, request()->user());

        return to_route('projects.index')->with('success', __('messages.flash.project_deleted'));
    }
}
