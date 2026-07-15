<?php
declare(strict_types=1);
namespace App\Modules\Analytics\Domain;

/** 归因因子值对象 — 每个因子对爆款的贡献度 */
class AttributionFactor
{
    public function __construct(
        public readonly string $name,
        public readonly float  $correlation,  // 0-1 相关性
        public readonly string $insight,      // 洞察一句话
    ) {}
}

/** 归因分析结果 — 输出 Top 3 关键因子 */
class AttributionResult
{
    /** @param AttributionFactor[] $factors */
    public function __construct(
        public readonly array $factors,
        public readonly string $analysisDate,
    ) {
        if (count($factors) > 3) {
            throw new \DomainException('归因结果最多输出 3 个关键因子');
        }
    }

    /** 最强因子 */
    public function topFactor(): ?AttributionFactor
    {
        return $this->factors[0] ?? null;
    }

    /** 是否有高置信度因子 (相关性 > 0.6) */
    public function hasHighConfidence(): bool
    {
        foreach ($this->factors as $f) {
            if ($f->correlation > 0.6) return true;
        }
        return false;
    }

    /** 摘要 */
    public function summary(): string
    {
        $lines = [];
        foreach ($this->factors as $i => $f) {
            $lines[] = ($i + 1) . ". {$f->name} (相关性: " . round($f->correlation * 100) . "%): {$f->insight}";
        }
        return implode("\n", $lines);
    }
}
