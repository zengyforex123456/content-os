<?php
declare(strict_types=1);
namespace App\Modules\Ai\Infrastructure;

use App\Modules\Ai\Domain\AiProviderInterface;
use Converge\Foundation\System\LlmKeyResolver;

/** DeepSeek LLM Provider — 通过 curl 调用 DeepSeek Chat API */
class DeepSeekProvider implements AiProviderInterface
{
    private const API_URL = 'https://api.deepseek.com/v1/chat/completions';
    private const DEFAULT_MODEL = 'deepseek-chat';

    public function __construct(
        private string $model = self::DEFAULT_MODEL,
        private int    $timeout = 30,
    ) {}

    public function name(): string
    {
        return 'deepseek';
    }

    public function generate(string $systemPrompt, string $userPrompt, array $options = []): string
    {
        $apiKey = LlmKeyResolver::resolve('deepseek');
        if ($apiKey === '') throw new \RuntimeException('DeepSeek API Key 未配置');

        $payload = json_encode([
            'model'    => $options['model'] ?? $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $userPrompt],
            ],
            'temperature' => $options['temperature'] ?? 0.8,
            'max_tokens'  => $options['max_tokens'] ?? 2048,
        ], JSON_UNESCAPED_UNICODE);

        $ch = curl_init(self::API_URL);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
        ]);

        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error !== '') throw new \RuntimeException("DeepSeek API 网络错误: {$error}");
        if ($httpCode !== 200) throw new \RuntimeException("DeepSeek API HTTP {$httpCode}: {$body}");

        $data = json_decode($body, true);
        $content = $data['choices'][0]['message']['content'] ?? '';

        if ($content === '') throw new \RuntimeException('DeepSeek API 返回空内容');

        return $content;
    }
}
