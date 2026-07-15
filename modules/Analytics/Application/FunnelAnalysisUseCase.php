<?php
declare(strict_types=1);
namespace App\Modules\Analytics\Application;

use App\Modules\Analytics\Domain\AnalyticsRepositoryInterface;
use App\Modules\Analytics\Domain\FunnelStage;

class FunnelAnalysisUseCase
{
    public function __construct(
        private AnalyticsRepositoryInterface $repo,
    ) {}

    /** @return FunnelStage[] */
    public function execute(string $dateFrom, string $dateTo): array
    {
        $counts = $this->repo->getFunnelCounts($dateFrom, $dateTo);

        $stages = [];
        $prev = null;
        $order = ['content_published' => '发布内容', 'content_viewed' => '内容浏览',
                   'profile_visited' => '访问主页', 'wechat_added' => '添加微信',
                   'trial_started' => '开始试用', 'paid' => '付费'];

        foreach ($order as $key => $label) {
            $stage = new FunnelStage(name: $label, count: $counts[$key] ?? 0, previous: $prev);
            $stages[] = $stage;
            $prev = $stage;
        }

        return $stages;
    }

    /** 漏斗健康度评分 */
    public function healthScore(array $stages): int
    {
        $score = 100;
        foreach ($stages as $stage) {
            $dropoff = $stage->dropoffRate();
            if ($dropoff > 0.5) $score -= 25;
            elseif ($dropoff > 0.3) $score -= 10;
            elseif ($dropoff > 0.1) $score -= 5;
        }
        return max(0, $score);
    }
}
