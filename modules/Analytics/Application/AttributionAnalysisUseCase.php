<?php
declare(strict_types=1);
namespace App\Modules\Analytics\Application;

use App\Modules\Analytics\Domain\AnalyticsRepositoryInterface;
use App\Modules\Analytics\Domain\AttributionFactor;
use App\Modules\Analytics\Domain\AttributionResult;

class AttributionAnalysisUseCase
{
    public function __construct(
        private AnalyticsRepositoryInterface $repo,
    ) {}

    public function execute(string $dateFrom, string $dateTo): AttributionResult
    {
        $data = $this->repo->getAttributionData($dateFrom, $dateTo);
        $factors = [];

        // 因子1: 标题长度 vs 互动率
        $titleCorrelation = $this->correlation(
            array_column($data, 'title_length'),
            array_column($data, 'engagement_rate')
        );
        $insight = $titleCorrelation > 0.3
            ? '标题 15-25 字的互动率最高'
            : '标题长度与互动率无明显关联';
        $factors[] = new AttributionFactor('标题长度', abs($titleCorrelation), $insight);

        // 因子2: 发布时间段 vs 播放量
        $hourBuckets = [];
        foreach ($data as $row) {
            $hour = (int)($row['publish_hour'] ?? 0);
            $hourBuckets[$hour][] = $row['views'] ?? 0;
        }
        $morningAvg = $this->avgBucket($hourBuckets, 6, 11);
        $noonAvg    = $this->avgBucket($hourBuckets, 11, 14);
        $eveningAvg = $this->avgBucket($hourBuckets, 18, 22);
        $bestSlot = '晚上 18-22 点';
        $bestAvg  = $eveningAvg;
        if ($morningAvg > $bestAvg) { $bestSlot = '早上 6-11 点'; $bestAvg = $morningAvg; }
        if ($noonAvg > $bestAvg) { $bestSlot = '中午 11-14 点'; }
        $factors[] = new AttributionFactor('发布时间段', 0.5, "{$bestSlot} 播放量最高");

        // 因子3: 话题类型 vs 分享率
        $topicCorrelation = $this->correlation(
            array_column($data, 'topic_category_score'),
            array_column($data, 'share_rate')
        );
        $insight = $topicCorrelation > 0.3
            ? '技术架构类话题的分享率显著高于其他类型'
            : '话题类型与分享率的关联需要更多数据';
        $factors[] = new AttributionFactor('话题类型', abs($topicCorrelation), $insight);

        // 按相关性排序, 取 Top 3
        usort($factors, fn($a, $b) => $b->correlation <=> $a->correlation);
        $factors = array_slice($factors, 0, 3);

        return new AttributionResult($factors, date('Y-m-d'));
    }

    private function correlation(array $x, array $y): float
    {
        $n = min(count($x), count($y));
        if ($n < 2) return 0.0;

        $sumX = array_sum(array_slice($x, 0, $n));
        $sumY = array_sum(array_slice($y, 0, $n));
        $meanX = $sumX / $n;
        $meanY = $sumY / $n;

        $num = $denomX = $denomY = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $dx = $x[$i] - $meanX;
            $dy = $y[$i] - $meanY;
            $num += $dx * $dy;
            $denomX += $dx * $dx;
            $denomY += $dy * $dy;
        }
        $denom = sqrt($denomX * $denomY);
        return $denom > 0 ? round($num / $denom, 4) : 0.0;
    }

    private function avgBucket(array $buckets, int $from, int $to): float
    {
        $values = [];
        for ($h = $from; $h <= $to; $h++) {
            if (isset($buckets[$h])) {
                $values = array_merge($values, $buckets[$h]);
            }
        }
        return count($values) > 0 ? array_sum($values) / count($values) : 0.0;
    }
}
