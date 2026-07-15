<?php
declare(strict_types=1);
namespace App\Modules\Distribution\Application;

use App\Modules\Distribution\Domain\DistributionTask;
use App\Modules\Distribution\Domain\DistributionTaskRepositoryInterface;

class ScheduleDistributionUseCase
{
    public function __construct(
        private DistributionTaskRepositoryInterface $repo,
    ) {}

    public function execute(
        int $contentId, string $title, string $platform, string $scheduledAt, array $options = []
    ): DistributionTask {
        if (strtotime($scheduledAt) < time()) {
            throw new \DomainException('发布时间必须在未来');
        }
        return $this->repo->save(new DistributionTask(
            contentId: $contentId, title: $title, platform: $platform,
            scheduledAt: $scheduledAt, options: $options,
        ));
    }
}
