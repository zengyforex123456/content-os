<?php
declare(strict_types=1);
namespace App\Modules\Pipeline\Controller;

use App\Modules\Pipeline\Application\CreateDraftUseCase;
use App\Modules\Pipeline\Application\PublishContentUseCase;
use App\Modules\Pipeline\Infrastructure\MysqlContentRepository;
use App\Modules\Pipeline\Domain\Content;
use Converge\Contracts\DatabaseInterface;

class ContentController
{
    public function __construct(
        private DatabaseInterface $db,
    ) {}

    /** POST /api/contents — 创建草稿 */
    public function create(): void
    {
        $repo    = new MysqlContentRepository($this->db);
        $useCase = new CreateDraftUseCase($repo);
        $content = $useCase->execute(
            title: $_POST['title'] ?? '',
            body: $_POST['body'] ?? '',
            platform: $_POST['platform'] ?? '',
            topicId: isset($_POST['topic_id']) ? (int)$_POST['topic_id'] : null,
        );
        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'data' => ['id' => $content->id, 'status' => $content->status]]);
    }

    /** POST /api/contents/publish — 发布内容 */
    public function publish(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $repo    = new MysqlContentRepository($this->db);
        $useCase = new PublishContentUseCase($repo);
        $content = $useCase->execute($id);
        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'data' => ['id' => $content->id, 'status' => $content->status]]);
    }

    /** GET /api/contents — 列表 */
    public function list(): void
    {
        $repo = new MysqlContentRepository($this->db);
        $status   = $_GET['status'] ?? '';
        $platform = $_GET['platform'] ?? '';
        $topicId  = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : 0;

        if ($topicId)     $contents = $repo->findByTopicId($topicId);
        elseif ($status)  $contents = $repo->findByStatus($status);
        elseif ($platform) $contents = $repo->findByPlatform($platform);
        else              $contents = $repo->findByStatus(Content::STATUS_DRAFT);

        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'data' => array_map(fn($c) => [
            'id' => $c->id, 'title' => $c->title, 'platform' => $c->platform,
            'status' => $c->status, 'wordCount' => $c->wordCount(),
        ], $contents)]);
    }
}
