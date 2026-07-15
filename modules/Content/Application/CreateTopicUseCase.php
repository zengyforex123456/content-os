<?php
declare(strict_types=1);
namespace App\Modules\Content\Application;

use App\Modules\Content\Domain\Topic;
use App\Modules\Content\Domain\TopicRepositoryInterface;

/** 创建选题用例 */
class CreateTopicUseCase
{
    public function __construct(
        private TopicRepositoryInterface $repo,
    ) {}

    public function execute(string $title, string $platform, array $keywords = [], string $referenceUrl = ''): Topic
    {
        $topic = new Topic(
            title: $title,
            platform: $platform,
            keywords: $keywords,
            referenceUrl: $referenceUrl,
        );
        return $this->repo->save($topic);
    }
}
