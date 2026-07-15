<?php
declare(strict_types=1);
namespace App\Modules\Distribution\Domain;

interface DistributionTaskRepositoryInterface
{
    public function save(DistributionTask $task): DistributionTask;
    /** @return DistributionTask[] */
    public function findPending(): array;
    /** @return DistributionTask[] */
    public function findByContentId(int $contentId): array;
    /** @return DistributionTask[] */
    public function findByPlatform(string $platform): array;
    public function findById(int $id): ?DistributionTask;
}
