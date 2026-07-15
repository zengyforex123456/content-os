<?php
declare(strict_types=1);
namespace App\Modules\Ai\Controller;

use App\Modules\Ai\Application\GenerateCopyUseCase;
use App\Modules\Ai\Application\GenerateTitleUseCase;
use App\Modules\Ai\Infrastructure\DeepSeekProvider;
use App\Modules\Ai\Infrastructure\MysqlAiTaskRepository;
use Converge\Contracts\DatabaseInterface;

class AiController
{
    public function __construct(
        private DatabaseInterface $db,
    ) {}

    /** POST /api/ai/generate-title — 生成标题候选 */
    public function generateTitle(): void
    {
        $topicTitle = $_POST['topic_title'] ?? '';
        $platform   = $_POST['platform'] ?? 'wechat';
        $keywords   = json_decode($_POST['keywords'] ?? '[]', true) ?: [];
        $topicId    = isset($_POST['topic_id']) ? (int)$_POST['topic_id'] : null;

        $repo     = new MysqlAiTaskRepository($this->db);
        $provider = new DeepSeekProvider();
        $useCase  = new GenerateTitleUseCase($provider, $repo);

        try {
            $result = $useCase->execute($topicTitle, $platform, $keywords, $topicId);
            header('Content-Type: application/json');
            echo json_encode(['ok' => true, 'data' => [
                'task_id' => $result['task']->id,
                'candidates' => $result['candidates'],
            ]]);
        } catch (\Throwable $e) {
            header('Content-Type: application/json', true, 500);
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /** POST /api/ai/generate-copy — 生成正文 */
    public function generateCopy(): void
    {
        $topicTitle    = $_POST['topic_title'] ?? '';
        $platform      = $_POST['platform'] ?? 'wechat';
        $selectedTitle = $_POST['selected_title'] ?? '';
        $keywords      = json_decode($_POST['keywords'] ?? '[]', true) ?: [];
        $topicId       = isset($_POST['topic_id']) ? (int)$_POST['topic_id'] : null;

        $repo     = new MysqlAiTaskRepository($this->db);
        $provider = new DeepSeekProvider();
        $useCase  = new GenerateCopyUseCase($provider, $repo);

        try {
            $task = $useCase->execute($topicTitle, $platform, $selectedTitle, $keywords, $topicId);
            header('Content-Type: application/json');
            echo json_encode(['ok' => true, 'data' => [
                'task_id' => $task->id,
                'output'  => $task->output,
                'status'  => $task->status,
            ]]);
        } catch (\Throwable $e) {
            header('Content-Type: application/json', true, 500);
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
    }
}
