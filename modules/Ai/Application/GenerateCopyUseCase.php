<?php
declare(strict_types=1);
namespace App\Modules\Ai\Application;

use App\Modules\Ai\Domain\AiProviderInterface;
use App\Modules\Ai\Domain\AiTask;
use App\Modules\Ai\Domain\AiTaskRepositoryInterface;

/** 正文生成用例 */
class GenerateCopyUseCase
{
    public function __construct(
        private AiProviderInterface      $ai,
        private AiTaskRepositoryInterface $repo,
    ) {}

    public function execute(string $topicTitle, string $platform, string $selectedTitle = '', array $keywords = [], ?int $topicId = null): AiTask
    {
        $task = new AiTask(
            type: AiTask::TYPE_COPY,
            platform: $platform,
            prompt: $topicTitle . "\n标题: " . $selectedTitle . "\n关键词: " . implode(', ', $keywords),
            topicId: $topicId,
        );

        $task = $this->repo->save($task->startProcessing());

        try {
            $userPrompt = $task->userPromptPrefix()
                . "\n\n话题: {$topicTitle}"
                . ($selectedTitle !== '' ? "\n选定标题: {$selectedTitle}" : '')
                . "\n平台: {$platform}" . "\n" . $task->platformHint()
                . "\n\n请撰写一篇800-1500字的专业内容。";

            $output = $this->ai->generate(
                systemPrompt: $task->systemPrompt(),
                userPrompt: $userPrompt,
            );

            return $this->repo->save($task->complete($output));
        } catch (\Throwable $e) {
            $this->repo->save($task->fail());
            throw $e;
        }
    }
}
