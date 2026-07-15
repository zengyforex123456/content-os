<?php
declare(strict_types=1);
namespace App\Modules\Monetize\Controller;

use App\Modules\Monetize\Application\CalculateCommissionUseCase;
use App\Modules\Monetize\Application\SubscribeUseCase;
use App\Modules\Monetize\Infrastructure\MysqlCommissionRepository;
use App\Modules\Monetize\Infrastructure\MysqlSubscriptionRepository;
use Converge\Contracts\DatabaseInterface;

class MonetizeController
{
    public function __construct(private DatabaseInterface $db) {}

    /** POST /api/monetize/subscribe — 试用/升级 */
    public function subscribe(): void
    {
        $repo    = new MysqlSubscriptionRepository($this->db);
        $useCase = new SubscribeUseCase($repo);
        $action  = $_POST['action'] ?? 'trial';
        $userId  = (int)($_POST['user_id'] ?? 0);

        try {
            $sub = match ($action) {
                'upgrade' => $useCase->upgrade($userId, $_POST['plan'] ?? 'basic'),
                'renew'   => $useCase->renew($userId),
                default   => $useCase->startTrial($userId),
            };
            header('Content-Type: application/json');
            echo json_encode(['ok' => true, 'data' => ['plan' => $sub->plan->name(), 'status' => $sub->status, 'daysLeft' => $sub->daysRemaining()]]);
        } catch (\Throwable $e) {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /** GET /api/monetize/commission?user_id=1 — 佣金汇总 */
    public function commission(): void
    {
        $repo    = new MysqlCommissionRepository($this->db);
        $useCase = new CalculateCommissionUseCase($repo);
        $userId  = (int)($_GET['user_id'] ?? 0);
        $result  = $useCase->summary($userId);

        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'data' => $result]);
    }
}
