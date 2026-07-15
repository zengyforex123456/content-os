<?php
declare(strict_types=1);
namespace App\Modules\Distribution\Controller;

use App\Modules\Distribution\Application\ExecuteDistributionUseCase;
use App\Modules\Distribution\Application\ScheduleDistributionUseCase;
use App\Modules\Distribution\Infrastructure\MysqlDistributionTaskRepository;
use App\Modules\Distribution\Infrastructure\SimulatedPlatformAdapter;
use Converge\Contracts\DatabaseInterface;

class DistributionController
{
    public function __construct(
        private DatabaseInterface $db,
    ) {}

    /** POST /api/distribution/schedule — 排程发布 */
    public function schedule(): void
    {
        $repo    = new MysqlDistributionTaskRepository($this->db);
        $useCase = new ScheduleDistributionUseCase($repo);
        $task    = $useCase->execute(
            contentId: (int)($_POST['content_id'] ?? 0),
            title: $_POST['title'] ?? '',
            platform: $_POST['platform'] ?? 'wechat',
            scheduledAt: $_POST['scheduled_at'] ?? date('c', time() + 3600),
            options: json_decode($_POST['options'] ?? '{}', true) ?: [],
        );
        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'data' => ['id' => $task->id, 'status' => $task->status]]);
    }

    /** POST /api/distribution/execute — 执行待发布任务 */
    public function execute(): void
    {
        $repo     = new MysqlDistributionTaskRepository($this->db);
        $adapters = [
            new SimulatedPlatformAdapter('wechat'),
            new SimulatedPlatformAdapter('zhihu'),
            new SimulatedPlatformAdapter('juejin'),
        ];
        $useCase = new ExecuteDistributionUseCase($repo, $adapters);
        $results = $useCase->executePending();

        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'data' => array_map(fn($t) => [
            'id' => $t->id, 'title' => $t->title, 'platform' => $t->platform,
            'status' => $t->status, 'result' => $t->result,
        ], $results)]);
    }

    /** GET /api/distribution/tasks — 任务列表 */
    public function list(): void
    {
        $repo     = new MysqlDistributionTaskRepository($this->db);
        $platform = $_GET['platform'] ?? '';
        $tasks    = $platform ? $repo->findByPlatform($platform) : $repo->findPending();

        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'data' => array_map(fn($t) => [
            'id' => $t->id, 'contentId' => $t->contentId, 'title' => $t->title,
            'platform' => $t->platform, 'scheduledAt' => $t->scheduledAt,
            'status' => $t->status, 'retryCount' => $t->retryCount,
            'coverSize' => $t->coverSize(), 'tags' => $t->platformTags(),
        ], $tasks)]);
    }
}
