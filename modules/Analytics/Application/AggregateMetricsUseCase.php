<?php
declare(strict_types=1);
namespace App\Modules\Analytics\Application;

use App\Modules\Analytics\Domain\AnalyticsRepositoryInterface;
use App\Modules\Analytics\Domain\ContentMetrics;

class AggregateMetricsUseCase
{
    public function __construct(
        private AnalyticsRepositoryInterface $repo,
    ) {}

    /** @param ContentMetrics[] $metrics */
    public function aggregate(array $metrics): array
    {
        $byPlatform = [];
        $totalViral = 0;
        $totalViews = 0;
        $totalEngagement = 0;

        foreach ($metrics as $m) {
            $byPlatform[$m->platform][] = $m;
            $totalViews += $m->views;
            $totalEngagement += $m->totalEngagement();
            if ($m->isViral()) $totalViral++;
        }

        $platformStats = [];
        foreach ($byPlatform as $platform => $items) {
            $views = array_sum(array_map(fn($m) => $m->views, $items));
            $platformStats[$platform] = [
                'contentCount' => count($items),
                'totalViews'   => $views,
                'avgEngagement' => $views > 0
                    ? round(array_sum(array_map(fn($m) => $m->engagementRate(), $items)) / count($items), 4)
                    : 0,
            ];
        }

        return [
            'totalViews'      => $totalViews,
            'totalEngagement' => $totalEngagement,
            'viralCount'      => $totalViral,
            'contentCount'    => count($metrics),
            'avgEngagementRate' => count($metrics) > 0
                ? round(array_sum(array_map(fn($m) => $m->engagementRate(), $metrics)) / count($metrics), 4)
                : 0,
            'byPlatform'      => $platformStats,
        ];
    }

    /** 获取单篇内容的跨平台汇总 */
    public function forContent(int $contentId): array
    {
        $metrics = $this->repo->getMetricsByContentId($contentId);
        return $this->aggregate($metrics);
    }

    /** 获取某平台的内容排名 (按互动得分) */
    public function ranking(string $platform, int $limit = 10): array
    {
        $metrics = $this->repo->getMetricsByPlatform($platform, $limit * 2);
        usort($metrics, fn($a, $b) => $b->engagementScore() <=> $a->engagementScore());
        return array_slice(array_map(fn($m) => [
            'contentId'       => $m->contentId,
            'views'           => $m->views,
            'engagementScore' => $m->engagementScore(),
            'isViral'         => $m->isViral(),
        ], $metrics), 0, $limit);
    }
}
