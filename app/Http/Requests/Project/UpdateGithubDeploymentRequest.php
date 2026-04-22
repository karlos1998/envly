<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGithubDeploymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'repository_full_name' => ['required', 'string', 'max:255'],
            'workflow_id' => ['required', 'string', 'max:120'],
            'deploy_ref' => ['nullable', 'string', 'max:255'],
        ];
    }
}
