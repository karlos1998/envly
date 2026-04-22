<?php

namespace App\Services;

use App\Repositories\ProjectRepository;
use Illuminate\Support\Str;

class ProjectIdentifierService
{
    public function __construct(private ProjectRepository $projects) {}

    public function make(string $name): string
    {
        $baseIdentifier = Str::slug($name) ?: 'project';
        $identifier = $baseIdentifier;
        $counter = 2;

        while ($this->projects->identifierExists($identifier)) {
            $identifier = $baseIdentifier.'-'.$counter;
            $counter++;
        }

        return $identifier;
    }
}
