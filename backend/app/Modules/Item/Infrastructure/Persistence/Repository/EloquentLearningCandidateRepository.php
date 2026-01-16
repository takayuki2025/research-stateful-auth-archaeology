<?php

namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use App\Models\LearningCandidate;
use App\Modules\Item\Domain\Repository\LearningCandidateRepository;

final class EloquentLearningCandidateRepository
    implements LearningCandidateRepository
{
    public function append(array $data): void
    {
        LearningCandidate::create($data);
    }
}