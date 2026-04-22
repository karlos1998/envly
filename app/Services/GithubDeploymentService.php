<?php

namespace App\Services;

use App\Models\SocialAccount;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GithubDeploymentService
{
    /**
     * @return list<array{id:int,full_name:string,name:string,default_branch:string,private:bool,html_url:string}>
     */
    public function listRepositories(SocialAccount $account): array
    {
        $repositories = [];
        $page = 1;

        do {
            $response = $this->client($account)->get('/user/repos', [
                'sort' => 'updated',
                'direction' => 'desc',
                'per_page' => 100,
                'page' => $page,
            ]);

            if ($response->failed()) {
                throw new RuntimeException('Could not load GitHub repositories.');
            }

            /** @var array<int, array<string, mixed>> $payload */
            $payload = $response->json();

            foreach ($payload as $repository) {
                $fullName = $repository['full_name'] ?? null;
                $name = $repository['name'] ?? null;
                $defaultBranch = $repository['default_branch'] ?? null;
                $htmlUrl = $repository['html_url'] ?? null;
                $id = $repository['id'] ?? null;

                if (! is_string($fullName) || ! is_string($name) || ! is_string($defaultBranch) || ! is_string($htmlUrl) || ! is_numeric($id)) {
                    continue;
                }

                $repositories[] = [
                    'id' => (int) $id,
                    'full_name' => $fullName,
                    'name' => $name,
                    'default_branch' => $defaultBranch,
                    'private' => (bool) ($repository['private'] ?? false),
                    'html_url' => $htmlUrl,
                ];
            }

            $page++;
        } while (count($payload) === 100 && $page <= 5);

        return $repositories;
    }

    /**
     * @return array{id:int,full_name:string,name:string,default_branch:string,private:bool,html_url:string}|null
     */
    public function findRepository(SocialAccount $account, string $repositoryFullName): ?array
    {
        $response = $this->client($account)->get('/repos/'.ltrim($repositoryFullName, '/'));

        if ($response->status() === 404) {
            return null;
        }

        if ($response->failed()) {
            throw new RuntimeException('Could not load selected GitHub repository.');
        }

        /** @var array<string, mixed> $repository */
        $repository = $response->json();

        $id = $repository['id'] ?? null;
        $fullName = $repository['full_name'] ?? null;
        $name = $repository['name'] ?? null;
        $defaultBranch = $repository['default_branch'] ?? null;
        $htmlUrl = $repository['html_url'] ?? null;

        if (! is_numeric($id) || ! is_string($fullName) || ! is_string($name) || ! is_string($defaultBranch) || ! is_string($htmlUrl)) {
            return null;
        }

        return [
            'id' => (int) $id,
            'full_name' => $fullName,
            'name' => $name,
            'default_branch' => $defaultBranch,
            'private' => (bool) ($repository['private'] ?? false),
            'html_url' => $htmlUrl,
        ];
    }

    /**
     * @return list<array{id:string,name:string,path:string,state:string}>
     */
    public function listWorkflows(SocialAccount $account, string $repositoryFullName): array
    {
        $response = $this->client($account)->get('/repos/'.ltrim($repositoryFullName, '/').'/actions/workflows', [
            'per_page' => 100,
        ]);

        if ($response->status() === 404) {
            return [];
        }

        if ($response->failed()) {
            throw new RuntimeException('Could not load GitHub workflows.');
        }

        /** @var array<string, mixed> $payload */
        $payload = $response->json();
        /** @var array<int, array<string, mixed>> $workflows */
        $workflows = $payload['workflows'] ?? [];

        return collect($workflows)
            ->map(function (array $workflow): ?array {
                $id = $workflow['id'] ?? null;
                $name = $workflow['name'] ?? null;
                $path = $workflow['path'] ?? null;
                $state = $workflow['state'] ?? null;

                if (! is_numeric($id) || ! is_string($name) || ! is_string($path) || ! is_string($state)) {
                    return null;
                }

                return [
                    'id' => (string) ((int) $id),
                    'name' => $name,
                    'path' => $path,
                    'state' => $state,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array{id:string,name:string,path:string,state:string}|null
     */
    public function findWorkflow(SocialAccount $account, string $repositoryFullName, string $workflowId): ?array
    {
        $response = $this->client($account)->get('/repos/'.ltrim($repositoryFullName, '/').'/actions/workflows/'.$workflowId);

        if ($response->status() === 404) {
            return null;
        }

        if ($response->failed()) {
            throw new RuntimeException('Could not load selected GitHub workflow.');
        }

        /** @var array<string, mixed> $workflow */
        $workflow = $response->json();

        $id = $workflow['id'] ?? null;
        $name = $workflow['name'] ?? null;
        $path = $workflow['path'] ?? null;
        $state = $workflow['state'] ?? null;

        if (! is_numeric($id) || ! is_string($name) || ! is_string($path) || ! is_string($state)) {
            return null;
        }

        return [
            'id' => (string) ((int) $id),
            'name' => $name,
            'path' => $path,
            'state' => $state,
        ];
    }

    public function dispatchWorkflow(SocialAccount $account, string $repositoryFullName, string $workflowId, string $ref): void
    {
        $response = $this->client($account)->post(
            '/repos/'.ltrim($repositoryFullName, '/').'/actions/workflows/'.$workflowId.'/dispatches',
            [
                'ref' => $ref,
            ],
        );

        if ($response->status() !== 204) {
            throw new RuntimeException('Could not start GitHub deployment workflow.');
        }
    }

    private function client(SocialAccount $account): PendingRequest
    {
        return Http::baseUrl('https://api.github.com')
            ->acceptJson()
            ->asJson()
            ->withToken($account->access_token ?? '')
            ->withHeaders([
                'X-GitHub-Api-Version' => '2022-11-28',
                'User-Agent' => 'Envly',
            ]);
    }
}
