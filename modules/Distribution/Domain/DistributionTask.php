<?php
declare(strict_types=1);
namespace App\Modules\Distribution\Domain;

/** 分发任务实体 — 定时发布·状态机·重试·纯业务规则 */
class DistributionTask
{
    public const STATUS_SCHEDULED  = 'scheduled';
    public const STATUS_PUBLISHING = 'publishing';
    public const STATUS_PUBLISHED  = 'published';
    public const STATUS_FAILED     = 'failed';

    private const MAX_RETRIES = 3;

    private const VALID_TRANSITIONS = [
        self::STATUS_SCHEDULED  => [self::STATUS_PUBLISHING],
        self::STATUS_PUBLISHING => [self::STATUS_PUBLISHED, self::STATUS_FAILED],
        self::STATUS_FAILED     => [self::STATUS_SCHEDULED],
        self::STATUS_PUBLISHED  => [],
    ];

    /** @param array<string,string> $options 平台特定选项 (封面/标签/尺寸) */
    public function __construct(
        public readonly int    $contentId,
        public readonly string $title,
        public readonly string $platform,
        public readonly string $scheduledAt,  // ISO 8601
        public readonly array  $options = [],
        public readonly string $status = self::STATUS_SCHEDULED,
        public readonly string $result = '',
        public readonly ?string $publishedAt = null,
        public readonly int    $retryCount = 0,
        public readonly ?int   $id = null,
    ) {}

    /** 开始发布 */
    public function startPublishing(): self
    {
        if ($this->scheduledAt > date('c')) {
            throw new \DomainException('未到预定发布时间');
        }
        $this->assertTransition(self::STATUS_PUBLISHING);
        return $this->with(self::STATUS_PUBLISHING);
    }

    /** 发布成功 */
    public function markPublished(string $result = ''): self
    {
        $this->assertTransition(self::STATUS_PUBLISHED);
        return $this->with(self::STATUS_PUBLISHED, result: $result, publishedAt: date('c'));
    }

    /** 发布失败 */
    public function markFailed(string $error): self
    {
        $this->assertTransition(self::STATUS_FAILED);
        return $this->with(self::STATUS_FAILED, result: $error);
    }

    /** 重试 */
    public function retry(): self
    {
        if ($this->retryCount >= self::MAX_RETRIES) {
            throw new \DomainException("已达最大重试次数 ({self::MAX_RETRIES})");
        }
        $this->assertTransition(self::STATUS_SCHEDULED);
        return $this->with(self::STATUS_SCHEDULED, retryCount: $this->retryCount + 1);
    }

    /** 是否逾期未发布 */
    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_SCHEDULED && $this->scheduledAt < date('c');
    }

    /** 平台标签适配 */
    public function platformTags(): array
    {
        $base = $this->options['tags'] ?? [];
        return match ($this->platform) {
            'juejin' => array_merge($base, ['SaaS', '架构']),
            'zhihu'  => array_merge($base, ['SaaS', '软件开发']),
            'wechat' => $base,
            default  => $base,
        };
    }

    /** 封面尺寸适配 */
    public function coverSize(): array
    {
        return match ($this->platform) {
            'wechat'       => ['width' => 900, 'height' => 383],
            'zhihu'        => ['width' => 1200, 'height' => 630],
            'juejin'       => ['width' => 1080, 'height' => 540],
            'xiaohongshu'  => ['width' => 1080, 'height' => 1440],
            default        => ['width' => 1200, 'height' => 630],
        };
    }

    private function with(string $status, string $result = '', ?string $publishedAt = null, int $retryCount = 0): self
    {
        return new self(
            contentId: $this->contentId, title: $this->title, platform: $this->platform,
            scheduledAt: $this->scheduledAt, options: $this->options,
            status: $status, result: $result ?: $this->result,
            publishedAt: $publishedAt ?? $this->publishedAt,
            retryCount: $retryCount ?: $this->retryCount,
            id: $this->id,
        );
    }

    private function assertTransition(string $to): void
    {
        $allowed = self::VALID_TRANSITIONS[$this->status] ?? [];
        if (!in_array($to, $allowed, true)) {
            throw new \DomainException("分发状态不可从 {$this->status} 转换为 {$to}");
        }
    }
}
