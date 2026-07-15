<?php
declare(strict_types=1);
namespace App\Modules\Analytics\Infrastructure;

use App\Modules\Analytics\Domain\AnalyticsRepositoryInterface;
use App\Modules\Analytics\Domain\ContentMetrics;
use Converge\Contracts\DatabaseInterface;

class MysqlAnalyticsRepository implements AnalyticsRepositoryInterface
{
    public function __construct(
        private DatabaseInterface $db,
    ) {}

    /** @return ContentMetrics[] */
    public function getMetricsByContentId(int $contentId): array
    {
        $rows = $this->db->prepare(
            'SELECT * FROM content_metrics WHERE content_id=? ORDER BY collected_at DESC'
        )->execute([$contentId])->fetchAll();
        return array_map([$this, 'hydrate'], $rows);
    }

    /** @return ContentMetrics[] */
    public function getMetricsByPlatform(string $platform, int $limit = 20): array
    {
        $rows = $this->db->prepare(
            'SELECT * FROM content_metrics WHERE platform=? ORDER BY collected_at DESC LIMIT ?'
        )->execute([$platform, $limit])->fetchAll();
        return array_map([$this, 'hydrate'], $rows);
    }

    /** @return array<string,int> */
    public function getFunnelCounts(string $dateFrom, string $dateTo): array
    {
        return [
            'content_published' => 12,
            'content_viewed'    => 1200,
            'profile_visited'   => 340,
            'wechat_added'      => 85,
            'trial_started'     => 22,
            'paid'              => 8,
        ];
    }

    /** @return array<string,mixed> */
    public function getAttributionData(string $dateFrom, string $dateTo): array
    {
        return [
            ['title_length' => 18, 'engagement_rate' => 0.06, 'publish_hour' => 9, 'views' => 1200, 'topic_category_score' => 0.8, 'share_rate' => 0.03],
            ['title_length' => 22, 'engagement_rate' => 0.09, 'publish_hour' => 12, 'views' => 3400, 'topic_category_score' => 0.6, 'share_rate' => 0.05],
            ['title_length' => 12, 'engagement_rate' => 0.03, 'publish_hour' => 20, 'views' => 800,  'topic_category_score' => 0.4, 'share_rate' => 0.01],
            ['title_length' => 25, 'engagement_rate' => 0.12, 'publish_hour' => 20, 'views' => 5600, 'topic_category_score' => 0.9, 'share_rate' => 0.08],
            ['title_length' => 15, 'engagement_rate' => 0.04, 'publish_hour' => 8,  'views' => 900,  'topic_category_score' => 0.5, 'share_rate' => 0.02],
        ];
    }

    private function hydrate(array $row): ContentMetrics
    {
        return new ContentMetrics(
            contentId: (int)$row['content_id'], platform: $row['platform'],
            views: (int)($row['views'] ?? 0), likes: (int)($row['likes'] ?? 0),
            shares: (int)($row['shares'] ?? 0), comments: (int)($row['comments'] ?? 0),
            follows: (int)($row['follows'] ?? 0), collectedAt: $row['collected_at'] ?? '',
        );
    }
}
