<?php
declare(strict_types=1);
namespace App\Modules\Ai\Domain;

/** AI 任务实体 — 纯业务规则, 零 IO. 一次 AI 生成 = 一个任务 */
class AiTask
{
    public const TYPE_TITLE   = 'title';
    public const TYPE_COPY    = 'copy';
    public const TYPE_SUMMARY = 'summary';
    public const TYPE_REPLY   = 'reply';

    public const STATUS_PENDING    = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED  = 'completed';
    public const STATUS_FAILED     = 'failed';

    public function __construct(
        public readonly string $type,
        public readonly string $platform,
        public readonly string $prompt,
        public readonly ?int   $topicId = null,
        public readonly string $output = '',
        public readonly string $status = self::STATUS_PENDING,
        public readonly ?int   $id = null,
    ) {}

    /** 开始处理 */
    public function startProcessing(): self
    {
        if ($this->status !== self::STATUS_PENDING) {
            throw new \DomainException('只有待处理任务可开始');
        }
        return $this->with(self::STATUS_PROCESSING, '');
    }

    /** 完成 */
    public function complete(string $output): self
    {
        if ($this->status !== self::STATUS_PROCESSING) {
            throw new \DomainException('只有处理中任务可完成');
        }
        if ($output === '') throw new \DomainException('AI 输出不能为空');
        return $this->with(self::STATUS_COMPLETED, $output);
    }

    /** 失败 */
    public function fail(): self
    {
        if ($this->status !== self::STATUS_PROCESSING) {
            throw new \DomainException('只有处理中任务可标记失败');
        }
        return $this->with(self::STATUS_FAILED, '');
    }

    /** 重试 */
    public function retry(): self
    {
        if ($this->status !== self::STATUS_FAILED) {
            throw new \DomainException('只有失败任务可重试');
        }
        return $this->with(self::STATUS_PENDING, '');
    }

    /** 平台差异化 prompt 后缀 */
    public function platformHint(): string
    {
        return match ($this->platform) {
            'zhihu'   => '知乎风格: 深度长文, 理性克制, 带数据支撑',
            'juejin'  => '掘金风格: 技术干货, 代码示例, 实用导向',
            'wechat'  => '公众号风格: 吸引点击, 情绪共鸣, 简洁有力',
            'xiaohongshu' => '小红书风格: 口语化, emoji, 生活化表达',
            default   => '专业风格: 清晰准确, 避免情绪化',
        };
    }

    /** 类型对应的 system prompt */
    public function systemPrompt(): string
    {
        return match ($this->type) {
            self::TYPE_TITLE   => '你是SaaS垂直领域的标题创作专家。根据话题生成3个高点击率标题。',
            self::TYPE_COPY    => '你是SaaS垂直领域的内容创作专家。用架构师视角撰写专业内容。',
            self::TYPE_SUMMARY => '你是技术内容摘要专家。用一句话提炼核心观点。',
            self::TYPE_REPLY   => '你是社区运营专家。针对用户评论生成专业、友好的回复。',
            default            => '你是SaaS垂直领域的写作助手。',
        };
    }

    /** 类型对应的用户 prompt 前缀 */
    public function userPromptPrefix(): string
    {
        return match ($this->type) {
            self::TYPE_TITLE => '为以下SaaS话题生成3个标题候选，每个标题适配不同场景：',
            default          => '请撰写以下SaaS话题的内容：',
        };
    }

    private function with(string $status, string $output): self
    {
        return new self(
            type: $this->type, platform: $this->platform, prompt: $this->prompt,
            topicId: $this->topicId, output: $output, status: $status, id: $this->id,
        );
    }
}
