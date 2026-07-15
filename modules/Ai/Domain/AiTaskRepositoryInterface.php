<?php
declare(strict_types=1);
namespace App\Modules\Ai\Domain;

interface AiTaskRepositoryInterface
{
    public function save(AiTask $task): AiTask;
    /** @return AiTask[] */
    public function findPending(int $limit = 10): array;
    /** @return AiTask[] */
    public function findByTopicId(int $topicId): array;
    public function findById(int $id): ?AiTask;
}
