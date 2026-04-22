<?php

namespace App\Enums;

enum EnvTemplate: string
{
    case Laravel = 'laravel';

    public function label(): string
    {
        return __('messages.env_templates.'.$this->value.'.label');
    }

    public function description(): string
    {
        return __('messages.env_templates.'.$this->value.'.description');
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
