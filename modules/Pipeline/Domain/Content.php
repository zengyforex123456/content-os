<?php
declare(strict_types=1);
namespace App\Modules\Pipeline\Domain;

/** 内容实体 — 草稿→审核→发布→复盘, 纯业务规则, 零 IO */
class Content
{
    public const STATUS_DRAFT     = 'draft';
    public const STATUS_REVIEW    = 'review';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED  = 'archived';

    /** 合法状态转换 */
    private const VALID_TRANSITIONS = [
        self::STATUS_DRAFT     => [self::STATUS_REVIEW, self::STATUS_ARCHIVED],
        self::STATUS_REVIEW    => [self::STATUS_DRAFT, self::STATUS_PUBLISHED, self::STATUS_ARCHIVED],
        self::STATUS_PUBLISHED => [self::STATUS_ARCHIVED],
        self::STATUS_ARCHIVED  => [],
    ];

    public function __construct(
        public readonly string $title,
        public readonly string $body,
        public readonly string $platform,
        public readonly ?int   $topicId = null,
        public readonly string $status = self::STATUS_DRAFT,
        public readonly ?int   $id = null,
    ) {}

    /** 提交审核 */
    public function submitForReview(): self
    {
        if ($this->body === '') throw new \DomainException('内容不能为空');
        $this->assertTransition(self::STATUS_REVIEW);
        return $this->withStatus(self::STATUS_REVIEW);
    }

    /** 退回修改 */
    public function reject(): self
    {
        $this->assertTransition(self::STATUS_DRAFT);
        return $this->withStatus(self::STATUS_DRAFT);
    }

    /** 审核通过, 发布 */
    public function publish(): self
    {
        $this->assertTransition(self::STATUS_PUBLISHED);
        return $this->withStatus(self::STATUS_PUBLISHED);
    }

    /** 归档 */
    public function archive(): self
    {
        $this->assertTransition(self::STATUS_ARCHIVED);
        return $this->withStatus(self::STATUS_ARCHIVED);
    }

    /** 字数 */
    public function wordCount(): int
    {
        return mb_strlen(strip_tags($this->body));
    }

    /** 是否可就绪 */
    public function isReadyForReview(): bool
    {
        return $this->title !== '' && $this->body !== '' && $this->platform !== '';
    }

    private function withStatus(string $status): self
    {
        return new self(
            title: $this->title, body: $this->body, platform: $this->platform,
            topicId: $this->topicId, status: $status, id: $this->id,
        );
    }

    private function assertTransition(string $to): void
    {
        $allowed = self::VALID_TRANSITIONS[$this->status] ?? [];
        if (!in_array($to, $allowed, true)) {
            throw new \DomainException("内容状态不可从 {$this->status} 转换为 {$to}");
        }
    }
}
