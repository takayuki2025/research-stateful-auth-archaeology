<?php

namespace App\Modules\Item\Domain\Repository;

interface LearningCandidateRepository
{
    public function append(array $data): void;
}