<?php
declare(strict_types=1);
namespace App\Modules\Analytics\Infrastructure;

use App\Modules\Analytics\Domain\ContentMetrics;
use App\Modules\Analytics\Domain\ReportGeneratorInterface;

class SimpleReportGenerator implements ReportGeneratorInterface
{
    /** @param ContentMetrics[] $metrics */
    public function generateReport(array $metrics, string $type, string $dateFrom, string $dateTo): string
    {
        $totalViews = array_sum(array_map(fn($m) => $m->views, $metrics));
        $totalEngagement = array_sum(array_map(fn($m) => $m->totalEngagement(), $metrics));
        $viralCount = count(array_filter($metrics, fn($m) => $m->isViral()));
        $avgRate = count($metrics) > 0
            ? round(array_sum(array_map(fn($m) => $m->engagementRate(), $metrics)) / count($metrics) * 100, 1)
            : 0;

        $platformBreakdown = [];
        foreach ($metrics as $m) {
            $platformBreakdown[$m->platform] = ($platformBreakdown[$m->platform] ?? 0) + $m->views;
        }

        $lines = [
            str_repeat('=', 50),
            "  Content-OS {$type} 报告",
            "  周期: {$dateFrom} ~ {$dateTo}",
            str_repeat('=', 50),
            '',
            "📊 总览",
            "  内容数: " . count($metrics),
            "  总播放: {$totalViews}",
            "  总互动: {$totalEngagement}",
            "  爆款数: {$viralCount}",
            "  平均互动率: {$avgRate}%",
            '',
            "📈 平台分布",
        ];

        foreach ($platformBreakdown as $platform => $views) {
            $lines[] = "  {$platform}: {$views} 次播放";
        }

        $lines[] = '';
        $lines[] = "--- 由 Content-OS 自动生成 ---";

        return implode("\n", $lines);
    }
}
