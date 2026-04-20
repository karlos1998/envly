<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\ProjectEnvironmentRepository;
use App\Services\EnvironmentService;
use Illuminate\Http\Response;

class EnvironmentFileController extends Controller
{
    public function __construct(
        private ProjectEnvironmentRepository $environments,
        private EnvironmentService $service,
    ) {}

    public function __invoke(string $projectIdentifier, string $token): Response
    {
        $environment = $this->environments->findForApi($projectIdentifier, $token);

        abort_if($environment === null, 404);

        $this->service->markExported($environment);

        return response($environment->content ?? '', 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'no-store, private',
        ]);
    }
}
