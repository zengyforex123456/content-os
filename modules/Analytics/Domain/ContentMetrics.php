<?php
declare(strict_types=1);
namespace App\Modules\Analytics\Domain;

/** 内容指标值对象 — 不可变, 每平台一条 */
class ContentMetrics
{
    public function __construct(
        public readonly int    $contentId,
        public readonly string $platform,
        public readonly int    $views = 0,
        public readonly int    $likes = 0,
        public readonly int    $shares = 0,
        public readonly int    $comments = 0,
        public readonly int    $follows = 0,
        public readonly string $collectedAt = '',  // ISO 8601
    ) {}

    /** 互动率 = (点赞+分享+评论) / 播放 */
    public function engagementRate(): float
    {
        if ($this->views === 0) return 0.0;
        return round(($this->likes + $this->shares + $this->comments) / $this->views, 4);
    }

    /** 关注转化率 */
    public function followRate(): float
    {
        if ($this->views === 0) return 0.0;
        return round($this->follows / $this->views, 4);
    }

    /** 互动得分 (加权) */
    public function engagementScore(): float
    {
        return $this->likes * 1.0 + $this->shares * 3.0 + $this->comments * 2.0 + $this->follows * 5.0;
    }

    /** 总互动数 */
    public function totalEngagement(): int
    {
        return $this->likes + $this->shares + $this->comments + $this->follows;
    }

    /** 是否达到爆款门槛 (互动率 > 5%) */
    public function isViral(): bool
    {
        return $this->engagementRate() > 0.05;
    }

    /** 数据是否有效 */
    public function isValid(): bool
    {
        return $this->platform !== '' && $this->views >= 0;
    }
}
