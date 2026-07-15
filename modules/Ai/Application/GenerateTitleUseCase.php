<?php
declare(strict_types=1);
namespace App\Modules\Ai\Application;

use App\Modules\Ai\Domain\AiProviderInterface;
use App\Modules\Ai\Domain\AiTask;
use App\Modules\Ai\Domain\AiTaskRepositoryInterface;

/** 标题生成用例 — 为给定话题生成 3 个标题候选 */
class GenerateTitleUseCase
{
    public function __construct(
        private AiProviderInterface      $ai,
        private AiTaskRepositoryInterface $repo,
    ) {}

    /**
     * @return array{task: AiTask, candidates: string[]}
     */
    public function execute(string $topicTitle, string $platform, array $keywords = [], ?int $topicId = null): array
    {
        $task = new AiTask(
            type: AiTask::TYPE_TITLE,
            platform: $platform,
            prompt: $topicTitle . "\n关键词: " . implode(', ', $keywords),
            topicId: $topicId,
        );

        $task = $this->repo->save($task->startProcessing());

        try {
            $userPrompt = $task->userPromptPrefix() . "\n\n话题: {$topicTitle}"
                . "\n平台: {$platform}" . "\n" . $task->platformHint()
                . "\n\n请输出3个标题候选，每行一个，用换行分隔。";

            $output = $this->ai->generate(
                systemPrompt: $task->systemPrompt(),
                userPrompt: $userPrompt,
            );

            $candidates = array_values(array_filter(
                array_map('trim', explode("\n", $output)),
                fn($line) => $line !== '' && !str_starts_with($line, '#'),
            ));

            $task = $this->repo->save($task->complete($output));

            return ['task' => $task, 'candidates' => $candidates];
        } catch (\Throwable $e) {
            $task = $this->repo->save($task->fail());
            throw $e;
        }
    }
}
