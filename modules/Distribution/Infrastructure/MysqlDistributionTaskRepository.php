<?php
declare(strict_types=1);
namespace App\Modules\Distribution\Infrastructure;

use App\Modules\Distribution\Domain\DistributionTask;
use App\Modules\Distribution\Domain\DistributionTaskRepositoryInterface;
use Converge\Contracts\DatabaseInterface;

class MysqlDistributionTaskRepository implements DistributionTaskRepositoryInterface
{
    public function __construct(
        private DatabaseInterface $db,
    ) {}

    public function save(DistributionTask $task): DistributionTask
    {
        if ($task->id !== null) {
            $this->db->prepare(
                'UPDATE distribution_tasks SET content_id=?, title=?, platform=?, scheduled_at=?, options=?, status=?, result=?, published_at=?, retry_count=? WHERE id=?'
            )->execute([$task->contentId, $task->title, $task->platform, $task->scheduledAt,
                json_encode($task->options), $task->status, $task->result, $task->publishedAt,
                $task->retryCount, $task->id]);
            return $task;
        }
        $this->db->prepare(
            'INSERT INTO distribution_tasks (content_id, title, platform, scheduled_at, options, status, result, published_at, retry_count) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([$task->contentId, $task->title, $task->platform, $task->scheduledAt,
            json_encode($task->options), $task->status, $task->result, $task->publishedAt, $task->retryCount]);
        $id = $this->db->lastInsertId();
        return $this->hydrateWith($task, (int)$id);
    }

    public function findById(int $id): ?DistributionTask
    {
        $row = $this->db->prepare('SELECT * FROM distribution_tasks WHERE id=?')->execute([$id])->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    /** @return DistributionTask[] */
    public function findPending(): array
    {
        $rows = $this->db->prepare(
            "SELECT * FROM distribution_tasks WHERE status IN ('scheduled','publishing') ORDER BY scheduled_at ASC"
        )->execute([])->fetchAll();
        return array_map([$this, 'hydrate'], $rows);
    }

    /** @return DistributionTask[] */
    public function findByContentId(int $contentId): array
    {
        $rows = $this->db->prepare(
            'SELECT * FROM distribution_tasks WHERE content_id=? ORDER BY id DESC'
        )->execute([$contentId])->fetchAll();
        return array_map([$this, 'hydrate'], $rows);
    }

    /** @return DistributionTask[] */
    public function findByPlatform(string $platform): array
    {
        $rows = $this->db->prepare(
            'SELECT * FROM distribution_tasks WHERE platform=? ORDER BY id DESC'
        )->execute([$platform])->fetchAll();
        return array_map([$this, 'hydrate'], $rows);
    }

    private function hydrate(array $row): DistributionTask
    {
        return new DistributionTask(
            contentId: (int)$row['content_id'], title: $row['title'], platform: $row['platform'],
            scheduledAt: $row['scheduled_at'],
            options: json_decode($row['options'] ?? '{}', true) ?: [],
            status: $row['status'], result: $row['result'] ?? '',
            publishedAt: $row['published_at'], retryCount: (int)$row['retry_count'],
            id: (int)$row['id'],
        );
    }

    private function hydrateWith(DistributionTask $task, int $id): DistributionTask
    {
        return new DistributionTask(
            contentId: $task->contentId, title: $task->title, platform: $task->platform,
            scheduledAt: $task->scheduledAt, options: $task->options,
            status: $task->status, result: $task->result,
            publishedAt: $task->publishedAt, retryCount: $task->retryCount,
            id: $id,
        );
    }
}
