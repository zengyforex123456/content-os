<?php
declare(strict_types=1);
namespace App\Modules\Content\Infrastructure;

use App\Modules\Content\Domain\Topic;
use App\Modules\Content\Domain\TopicRepositoryInterface;
use Converge\Contracts\DatabaseInterface;

class MysqlTopicRepository implements TopicRepositoryInterface
{
    public function __construct(
        private DatabaseInterface $db,
    ) {}

    public function save(Topic $topic): Topic
    {
        if ($topic->id !== null) {
            $this->db->prepare('UPDATE topics SET title=?, platform=?, keywords=?, reference_url=?, status=? WHERE id=?')
                ->execute([$topic->title, $topic->platform, json_encode($topic->keywords), $topic->referenceUrl, $topic->status, $topic->id]);
            return $topic;
        }
        $this->db->prepare('INSERT INTO topics (title, platform, keywords, reference_url, status) VALUES (?, ?, ?, ?, ?)')
            ->execute([$topic->title, $topic->platform, json_encode($topic->keywords), $topic->referenceUrl, $topic->status]);
        $id = $this->db->lastInsertId();
        return new Topic(
            title: $topic->title, platform: $topic->platform,
            keywords: $topic->keywords, referenceUrl: $topic->referenceUrl,
            status: $topic->status, id: (int)$id,
        );
    }

    public function findById(int $id): ?Topic
    {
        $row = $this->db->prepare('SELECT * FROM topics WHERE id=?')->execute([$id])->fetch();
        if (!$row) return null;
        return $this->hydrate($row);
    }

    /** @return Topic[] */
    public function findByStatus(string $status): array
    {
        $rows = $this->db->prepare('SELECT * FROM topics WHERE status=? ORDER BY id DESC')->execute([$status])->fetchAll();
        return array_map([$this, 'hydrate'], $rows);
    }

    /** @return Topic[] */
    public function findByPlatform(string $platform): array
    {
        $rows = $this->db->prepare('SELECT * FROM topics WHERE platform=? ORDER BY id DESC')->execute([$platform])->fetchAll();
        return array_map([$this, 'hydrate'], $rows);
    }

    /** @return Topic[] */
    public function searchByKeyword(string $keyword): array
    {
        $rows = $this->db->prepare('SELECT * FROM topics WHERE JSON_CONTAINS(keywords, ?) ORDER BY id DESC')
            ->execute([json_encode($keyword)])->fetchAll();
        return array_map([$this, 'hydrate'], $rows);
    }

    private function hydrate(array $row): Topic
    {
        return new Topic(
            title: $row['title'],
            platform: $row['platform'],
            keywords: json_decode($row['keywords'] ?? '[]', true),
            referenceUrl: $row['reference_url'] ?? '',
            status: $row['status'],
            id: (int)$row['id'],
        );
    }
}
