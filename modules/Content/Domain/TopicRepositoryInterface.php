<?php
declare(strict_types=1);
namespace App\Modules\Content\Domain;

/** 选题数据端口 — Domain 定义, Infrastructure 实现 */
interface TopicRepositoryInterface
{
    public function save(Topic $topic): Topic;
    /** @return Topic[] */
    public function findByStatus(string $status): array;
    /** @return Topic[] */
    public function findByPlatform(string $platform): array;
    /** @return Topic[] */
    public function searchByKeyword(string $keyword): array;
    public function findById(int $id): ?Topic;
}
