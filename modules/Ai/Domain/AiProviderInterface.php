<?php
declare(strict_types=1);
namespace App\Modules\Ai\Domain;

/** LLM Provider 端口 — Domain 定义, Infrastructure 实现 */
interface AiProviderInterface
{
    /** @throws \RuntimeException 当 API 调用失败时 */
    public function generate(string $systemPrompt, string $userPrompt, array $options = []): string;

    /** Provider 名称 (如 'deepseek', 'openai') */
    public function name(): string;
}
