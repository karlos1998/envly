<?php

namespace App\Http\Requests\Project;

use App\Enums\EnvTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120'],
            'template' => ['nullable', 'string', Rule::in(EnvTemplate::values())],
        ];
    }

    public function template(): ?EnvTemplate
    {
        $template = $this->validated('template');

        return is_string($template) ? EnvTemplate::tryFrom($template) : null;
    }
}
