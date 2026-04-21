<?php

namespace App\Http\Requests\Environment;

use App\Models\ProjectEnvironment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeleteEnvironmentRequest extends FormRequest
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
        /** @var ProjectEnvironment $environment */
        $environment = $this->route('environment');

        return [
            'name' => ['required', 'string', Rule::in([$environment->name])],
        ];
    }

    public function confirmationName(): string
    {
        return (string) $this->validated('name');
    }
}
