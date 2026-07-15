<?php
declare(strict_types=1);
namespace App\Modules\Content\Controller;

use App\Modules\Content\Application\CreateTopicUseCase;
use App\Modules\Content\Infrastructure\MysqlTopicRepository;
use Converge\Contracts\DatabaseInterface;

class TopicController
{
    public function __construct(
        private DatabaseInterface $db,
    ) {}

    /** 创建选题 — POST /api/topics */
    public function create(): void
    {
        $title       = $_POST['title'] ?? '';
        $platform    = $_POST['platform'] ?? '';
        $keywords    = json_decode($_POST['keywords'] ?? '[]', true);
        $referenceUrl = $_POST['reference_url'] ?? '';

        $repo    = new MysqlTopicRepository($this->db);
        $useCase = new CreateTopicUseCase($repo);
        $topic   = $useCase->execute($title, $platform, $keywords, $referenceUrl);

        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'data' => ['id' => $topic->id, 'status' => $topic->status]]);
    }

    /** 列出选题 — GET /api/topics?status=draft&platform=zhihu */
    public function list(): void
    {
        $repo = new MysqlTopicRepository($this->db);
        $status   = $_GET['status'] ?? '';
        $platform = $_GET['platform'] ?? '';

        if ($status)    $topics = $repo->findByStatus($status);
        elseif ($platform) $topics = $repo->findByPlatform($platform);
        else             $topics = $repo->findByStatus('draft');

        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'data' => array_map(fn($t) => [
            'id' => $t->id, 'title' => $t->title, 'platform' => $t->platform,
            'keywords' => $t->keywords, 'status' => $t->status,
        ], $topics)]);
    }
}
