<?php
declare(strict_types=1);
namespace App\Modules\Pipeline\Application;

use App\Modules\Pipeline\Domain\Content;
use App\Modules\Pipeline\Domain\ContentRepositoryInterface;

class PublishContentUseCase
{
    public function __construct(
        private ContentRepositoryInterface $repo,
    ) {}

    public function execute(int $contentId): Content
    {
        $content = $this->repo->findById($contentId);
        if ($content === null) throw new \DomainException("内容 ID={$contentId} 不存在");
        return $this->repo->save($content->publish());
    }
}
