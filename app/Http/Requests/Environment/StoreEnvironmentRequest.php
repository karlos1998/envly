<?php

namespace App\Http\Requests\Environment;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEnvironmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        /** @var Project $project */
        $project = $this->route('project');

        return [
            'name' => ['required', 'string', 'max:80'],
            'content' => ['nullable', 'string'],
            'creation_mode' => ['nullable', 'string', Rule::in(['empty', 'copy'])],
            'source_environment_id' => [
                Rule::requiredIf($this->input('creation_mode') === 'copy'),
                'nullable',
                'integer',
                Rule::exists('project_environments', 'id')->where('project_id', $project->id),
            ],
        ];
    }

    public function sourceEnvironmentId(): ?int
    {
        if ($this->validated('creation_mode') !== 'copy') {
            return null;
        }

        return (int) $this->validated('source_environment_id');
    }
}
