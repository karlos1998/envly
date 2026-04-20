<?php

namespace App\Services;

class EnvironmentDiffService
{
    /**
     * @return array{added:int, removed:int}
     */
    public function countChangedLines(?string $previousContent, string $content): array
    {
        $previousLines = $this->normalizedLines($previousContent ?? '');
        $nextLines = $this->normalizedLines($content);

        return [
            'added' => max(count($nextLines) - count(array_intersect_assoc($nextLines, $previousLines)), 0),
            'removed' => max(count($previousLines) - count(array_intersect_assoc($previousLines, $nextLines)), 0),
        ];
    }

    /**
     * @return list<string>
     */
    private function normalizedLines(string $content): array
    {
        if ($content === '') {
            return [];
        }

        return preg_split('/\R/', $content) ?: [];
    }
}
