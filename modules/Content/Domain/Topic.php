<?php
declare(strict_types=1);
namespace App\Modules\Content\Domain;

/** 选题实体 — 爆款公式+关键词+对标库, 纯业务规则, 零 IO */
class Topic
{
    public const STATUS_DRAFT     = 'draft';
    public const STATUS_READY     = 'ready';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED  = 'archived';

    /** 合法状态转换 */
    private const VALID_TRANSITIONS = [
        self::STATUS_DRAFT     => [self::STATUS_READY, self::STATUS_ARCHIVED],
        self::STATUS_READY     => [self::STATUS_PUBLISHED, self::STATUS_ARCHIVED],
        self::STATUS_PUBLISHED => [self::STATUS_ARCHIVED],
        self::STATUS_ARCHIVED  => [],
    ];

    /** @param string[] $keywords */
    public function __construct(
        public readonly string $title,
        public readonly string $platform,
        public readonly array  $keywords = [],
        public readonly string $referenceUrl = '',
        public readonly string $status = self::STATUS_DRAFT,
        public readonly ?int   $id = null,
    ) {}

    /** 选题就绪 */
    public function markReady(): self
    {
        $this->assertTransition(self::STATUS_READY);
        return new self(
            title: $this->title, platform: $this->platform,
            keywords: $this->keywords, referenceUrl: $this->referenceUrl,
            status: self::STATUS_READY, id: $this->id,
        );
    }

    /** 已发布为内容 */
    public function markPublished(): self
    {
        $this->assertTransition(self::STATUS_PUBLISHED);
        return new self(
            title: $this->title, platform: $this->platform,
            keywords: $this->keywords, referenceUrl: $this->referenceUrl,
            status: self::STATUS_PUBLISHED, id: $this->id,
        );
    }

    /** 归档 */
    public function archive(): self
    {
        $this->assertTransition(self::STATUS_ARCHIVED);
        return new self(
            title: $this->title, platform: $this->platform,
            keywords: $this->keywords, referenceUrl: $this->referenceUrl,
            status: self::STATUS_ARCHIVED, id: $this->id,
        );
    }

    /** 是否为爆款公式 (含关键词+对标链接) */
    public function isFormulaComplete(): bool
    {
        return $this->title !== '' && count($this->keywords) > 0 && $this->referenceUrl !== '';
    }

    /** 获取主关键词 */
    public function primaryKeyword(): string
    {
        return $this->keywords[0] ?? '';
    }

    private function assertTransition(string $to): void
    {
        $allowed = self::VALID_TRANSITIONS[$this->status] ?? [];
        if (!in_array($to, $allowed, true)) {
            throw new \DomainException(
                "选题状态不可从 {$this->status} 转换为 {$to}"
            );
        }
    }
}
