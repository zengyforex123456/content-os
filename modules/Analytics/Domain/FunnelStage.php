<?php
declare(strict_types=1);
namespace App\Modules\Analytics\Domain;

/** 漏斗阶段值对象 — 内容→浏览→互动→加微→付费 */
class FunnelStage
{
    /** @param FunnelStage|null $previous 上一阶段 (用于计算转化率) */
    public function __construct(
        public readonly string $name,
        public readonly int    $count,
        public readonly ?FunnelStage $previous = null,
    ) {}

    /** 从上一阶段到此的转化率 */
    public function conversionRate(): float
    {
        if ($this->previous === null || $this->previous->count === 0) return 1.0;
        return round($this->count / $this->previous->count, 4);
    }

    /** 流失数 */
    public function dropoff(): int
    {
        if ($this->previous === null) return 0;
        return $this->previous->count - $this->count;
    }

    /** 流失率 */
    public function dropoffRate(): float
    {
        if ($this->previous === null || $this->previous->count === 0) return 0.0;
        return round($this->dropoff() / $this->previous->count, 4);
    }
}
