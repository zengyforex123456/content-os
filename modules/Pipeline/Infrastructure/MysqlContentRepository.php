<?php
declare(strict_types=1);
namespace App\Modules\Pipeline\Infrastructure;

use App\Modules\Pipeline\Domain\Content;
use App\Modules\Pipeline\Domain\ContentRepositoryInterface;
use Converge\Contracts\DatabaseInterface;

class MysqlContentRepository implements ContentRepositoryInterface
{
    public function __construct(
        private DatabaseInterface $db,
    ) {}

    public function save(Content $c): Content
    {
        if ($c->id !== null) {
            $this->db->prepare('UPDATE contents SET title=?, body=?, platform=?, topic_id=?, status=? WHERE id=?')
                ->execute([$c->title, $c->body, $c->platform, $c->topicId, $c->status, $c->id]);
            return $c;
        }
        $this->db->prepare('INSERT INTO contents (title, body, platform, topic_id, status) VALUES (?, ?, ?, ?, ?)')
            ->execute([$c->title, $c->body, $c->platform, $c->topicId, $c->status]);
        $id = $this->db->lastInsertId();
        return new Content(
            title: $c->title, body: $c->body, platform: $c->platform,
            topicId: $c->topicId, status: $c->status, id: (int)$id,
        );
    }

    public function findById(int $id): ?Content
    {
        $row = $this->db->prepare('SELECT * FROM contents WHERE id=?')->execute([$id])->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    /** @return Content[] */
    public function findByStatus(string $status): array
    {
        $rows = $this->db->prepare('SELECT * FROM contents WHERE status=? ORDER BY id DESC')->execute([$status])->fetchAll();
        return array_map([$this, 'hydrate'], $rows);
    }

    /** @return Content[] */
    public function findByPlatform(string $platform): array
    {
        $rows = $this->db->prepare('SELECT * FROM contents WHERE platform=? ORDER BY id DESC')->execute([$platform])->fetchAll();
        return array_map([$this, 'hydrate'], $rows);
    }

    /** @return Content[] */
    public function findByTopicId(int $topicId): array
    {
        $rows = $this->db->prepare('SELECT * FROM contents WHERE topic_id=? ORDER BY id DESC')->execute([$topicId])->fetchAll();
        return array_map([$this, 'hydrate'], $rows);
    }

    private function hydrate(array $row): Content
    {
        return new Content(
            title: $row['title'], body: $row['body'], platform: $row['platform'],
            topicId: $row['topic_id'] ? (int)$row['topic_id'] : null,
            status: $row['status'], id: (int)$row['id'],
        );
    }
}
