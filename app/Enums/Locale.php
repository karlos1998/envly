<?php

namespace App\Enums;

enum Locale: string
{
    case English = 'en';
    case Polish = 'pl';

    public function label(): string
    {
        return match ($this) {
            self::English => 'English',
            self::Polish => 'Polski',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function fromBrowserHeader(?string $header): self
    {
        if ($header === null) {
            return self::English;
        }

        return str_contains(strtolower($header), 'pl') ? self::Polish : self::English;
    }
}
