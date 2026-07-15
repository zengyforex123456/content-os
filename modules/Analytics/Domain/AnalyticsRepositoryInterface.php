<?php
declare(strict_types=1);
namespace App\Modules\Analytics\Domain;

interface AnalyticsRepositoryInterface
{
    /** @return ContentMetrics[] */
    public function getMetricsByContentId(int $contentId): array;
    /** @return ContentMetrics[] */
    public function getMetricsByPlatform(string $platform, int $limit = 20): array;
    /** @return array<string,int> 漏斗各阶段计数 */
    public function getFunnelCounts(string $dateFrom, string $dateTo): array;
    /** @return array<string,mixed> 归因原始数据 */
    public function getAttributionData(string $dateFrom, string $dateTo): array;
}

interface ReportGeneratorInterface
{
    /** @param ContentMetrics[] $metrics */
    public function generateReport(array $metrics, string $type, string $dateFrom, string $dateTo): string;
}
