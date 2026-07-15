<?php
declare(strict_types=1);
namespace App\Modules\Monetize\Application;

use App\Modules\Monetize\Domain\Commission;
use App\Modules\Monetize\Domain\CommissionRepositoryInterface;

class CalculateCommissionUseCase
{
    public function __construct(
        private CommissionRepositoryInterface $repo,
    ) {}

    /** 计算并记录佣金 */
    public function execute(int $referrerId, int $newUserId, int $orderAmount, string $source = '', ?float $customRate = null): Commission
    {
        $commission = new Commission(
            referrerUserId: $referrerId, newUserId: $newUserId,
            amount: $orderAmount, rate: $customRate ?? 0.20, source: $source,
        );
        return $this->repo->save($commission);
    }

    /** 某人佣金汇总 */
    public function summary(int $userId): array
    {
        $commissions = $this->repo->findByReferrer($userId);
        $total       = $this->repo->totalByReferrer($userId);

        $bySource = [];
        foreach ($commissions as $c) {
            $label = $c->sourceLabel();
            $bySource[$label] = ($bySource[$label] ?? 0) + $c->commissionAmount();
        }

        return [
            'userId'          => $userId,
            'totalCommission' => $total,
            'totalYuan'       => round($total / 100, 2),
            'canWithdraw'     => $total >= 10000,
            'referralCount'   => count($commissions),
            'bySource'        => $bySource,
        ];
    }
}
