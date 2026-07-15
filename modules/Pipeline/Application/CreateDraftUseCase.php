<?php
declare(strict_types=1);
namespace App\Modules\Pipeline\Application;

use App\Modules\Pipeline\Domain\Content;
use App\Modules\Pipeline\Domain\ContentRepositoryInterface;

class CreateDraftUseCase
{
    public function __construct(
        private ContentRepositoryInterface $repo,
    ) {}

    public function execute(string $title, string $body, string $platform, ?int $topicId = null): Content
    {
        return $this->repo->save(new Content(title: $title, body: $body, platform: $platform, topicId: $topicId));
    }
}
