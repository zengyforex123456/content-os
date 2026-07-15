<?php
declare(strict_types=1);
namespace App\Modules\Ai\Infrastructure;

use App\Modules\Ai\Domain\AiTask;
use App\Modules\Ai\Domain\AiTaskRepositoryInterface;
use Converge\Contracts\DatabaseInterface;

class MysqlAiTaskRepository implements AiTaskRepositoryInterface
{
    public function __construct(
        private DatabaseInterface $db,
    ) {}

    public function save(AiTask $task): AiTask
    {
        if ($task->id !== null) {
            $this->db->prepare(
                'UPDATE ai_tasks SET type=?, platform=?, prompt=?, topic_id=?, output=?, status=? WHERE id=?'
            )->execute([$task->type, $task->platform, $task->prompt, $task->topicId, $task->output, $task->status, $task->id]);
            return $task;
        }
        $this->db->prepare(
            'INSERT INTO ai_tasks (type, platform, prompt, topic_id, output, status) VALUES (?, ?, ?, ?, ?, ?)'
        )->execute([$task->type, $task->platform, $task->prompt, $task->topicId, $task->output, $task->status]);
        $id = $this->db->lastInsertId();
        return new AiTask(
            type: $task->type, platform: $task->platform, prompt: $task->prompt,
            topicId: $task->topicId, output: $task->output, status: $task->status, id: (int)$id,
        );
    }

    public function findById(int $id): ?AiTask
    {
        $row = $this->db->prepare('SELECT * FROM ai_tasks WHERE id=?')->execute([$id])->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    /** @return AiTask[] */
    public function findPending(int $limit = 10): array
    {
        $rows = $this->db->prepare(
            'SELECT * FROM ai_tasks WHERE status IN (?, ?) ORDER BY id ASC LIMIT ?'
        )->execute([AiTask::STATUS_PENDING, AiTask::STATUS_PROCESSING, $limit])->fetchAll();
        return array_map([$this, 'hydrate'], $rows);
    }

    /** @return AiTask[] */
    public function findByTopicId(int $topicId): array
    {
        $rows = $this->db->prepare(
            'SELECT * FROM ai_tasks WHERE topic_id=? ORDER BY id DESC'
        )->execute([$topicId])->fetchAll();
        return array_map([$this, 'hydrate'], $rows);
    }

    private function hydrate(array $row): AiTask
    {
        return new AiTask(
            type: $row['type'], platform: $row['platform'], prompt: $row['prompt'],
            topicId: $row['topic_id'] ? (int)$row['topic_id'] : null,
            output: $row['output'] ?? '', status: $row['status'], id: (int)$row['id'],
        );
    }
}
