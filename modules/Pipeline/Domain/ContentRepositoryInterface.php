<?php
declare(strict_types=1);
namespace App\Modules\Pipeline\Domain;

interface ContentRepositoryInterface
{
    public function save(Content $c): Content;
    /** @return Content[] */
    public function findByStatus(string $status): array;
    /** @return Content[] */
    public function findByPlatform(string $platform): array;
    /** @return Content[] */
    public function findByTopicId(int $topicId): array;
    public function findById(int $id): ?Content;
}
