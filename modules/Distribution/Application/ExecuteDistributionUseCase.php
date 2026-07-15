<?php
declare(strict_types=1);
namespace App\Modules\Distribution\Application;

use App\Modules\Distribution\Domain\DistributionTask;
use App\Modules\Distribution\Domain\DistributionTaskRepositoryInterface;
use App\Modules\Distribution\Domain\PlatformAdapterInterface;

class ExecuteDistributionUseCase
{
    /** @param PlatformAdapterInterface[] $adapters */
    public function __construct(
        private DistributionTaskRepositoryInterface $repo,
        private array $adapters,
    ) {}

    /** @return DistributionTask[] */
    public function executePending(): array
    {
        $results = [];
        foreach ($this->repo->findPending() as $task) {
            if (!$task->isOverdue()) continue;
            $results[] = $this->executeOne($task);
        }
        return $results;
    }

    public function executeOne(DistributionTask $task): DistributionTask
    {
        $adapter = $this->getAdapter($task->platform);
        $task    = $this->repo->save($task->startPublishing());

        try {
            $result = $adapter->publish($task->title, '', $task->options);
            if ($result->success) {
                return $this->repo->save($task->markPublished($result->remoteUrl));
            }
            return $this->repo->save($task->markFailed($result->message));
        } catch (\Throwable $e) {
            return $this->repo->save($task->markFailed($e->getMessage()));
        }
    }

    public function retry(int $taskId): DistributionTask
    {
        $task = $this->repo->findById($taskId)
            ?? throw new \DomainException("分发任务 ID={$taskId} 不存在");
        return $this->repo->save($task->retry());
    }

    private function getAdapter(string $platform): PlatformAdapterInterface
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->platform() === $platform) return $adapter;
        }
        throw new \RuntimeException("平台 '{$platform}' 没有注册适配器");
    }
}
