<?php
declare(strict_types=1);
namespace App\Modules\Analytics\Controller;

use App\Modules\Analytics\Application\AggregateMetricsUseCase;
use App\Modules\Analytics\Application\AttributionAnalysisUseCase;
use App\Modules\Analytics\Application\FunnelAnalysisUseCase;
use App\Modules\Analytics\Infrastructure\MysqlAnalyticsRepository;
use Converge\Contracts\DatabaseInterface;

class AnalyticsController
{
    public function __construct(
        private DatabaseInterface $db,
    ) {}

    /** GET /api/analytics/content?id=1 — 单篇内容指标 */
    public function contentMetrics(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $repo    = new MysqlAnalyticsRepository($this->db);
        $useCase = new AggregateMetricsUseCase($repo);
        $result  = $useCase->forContent($id);

        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'data' => $result]);
    }

    /** GET /api/analytics/funnel?from=...&to=... — 漏斗分析 */
    public function funnel(): void
    {
        $from = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
        $to   = $_GET['to'] ?? date('Y-m-d');
        $repo    = new MysqlAnalyticsRepository($this->db);
        $useCase = new FunnelAnalysisUseCase($repo);
        $stages  = $useCase->execute($from, $to);
        $health  = $useCase->healthScore($stages);

        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'data' => [
            'stages' => array_map(fn($s) => [
                'name' => $s->name, 'count' => $s->count,
                'conversionRate' => $s->conversionRate(), 'dropoff' => $s->dropoff(),
            ], $stages),
            'healthScore' => $health,
        ]]);
    }

    /** GET /api/analytics/attribution?from=...&to=... — 归因分析 */
    public function attribution(): void
    {
        $from = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
        $to   = $_GET['to'] ?? date('Y-m-d');
        $repo    = new MysqlAnalyticsRepository($this->db);
        $useCase = new AttributionAnalysisUseCase($repo);
        $result  = $useCase->execute($from, $to);

        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'data' => [
            'summary' => $result->summary(),
            'hasHighConfidence' => $result->hasHighConfidence(),
            'topFactor' => $result->topFactor()?->name,
        ]]);
    }

    /** GET /api/analytics/ranking?platform=juejin — 内容排名 */
    public function ranking(): void
    {
        $platform = $_GET['platform'] ?? 'juejin';
        $repo    = new MysqlAnalyticsRepository($this->db);
        $useCase = new AggregateMetricsUseCase($repo);
        $result  = $useCase->ranking($platform);

        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'data' => $result]);
    }
}
