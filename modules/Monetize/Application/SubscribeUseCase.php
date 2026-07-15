<?php
declare(strict_types=1);
namespace App\Modules\Monetize\Application;

use App\Modules\Monetize\Domain\Plan;
use App\Modules\Monetize\Domain\Subscription;
use App\Modules\Monetize\Domain\SubscriptionRepositoryInterface;

class SubscribeUseCase
{
    public function __construct(
        private SubscriptionRepositoryInterface $repo,
    ) {}

    /** 新用户试用注册 */
    public function startTrial(int $userId): Subscription
    {
        $existing = $this->repo->findByUserId($userId);
        if ($existing && $existing->isActive()) {
            throw new \DomainException('用户已有活跃订阅');
        }
        return $this->repo->save(new Subscription(
            userId: $userId, plan: Plan::of(Plan::FREE),
            expiresAt: date('c', strtotime('+14 days')),
        ));
    }

    /** 升级套餐 */
    public function upgrade(int $userId, string $planLevel): Subscription
    {
        $sub = $this->repo->findByUserId($userId)
            ?? throw new \DomainException('用户无订阅记录');
        if (!$sub->isActive()) throw new \DomainException('订阅已过期');

        $newPlan = Plan::of($planLevel);
        $newExpires = date('c', strtotime('+365 days'));
        return $this->repo->save($sub->upgrade($newPlan, $newExpires));
    }

    /** 续费 */
    public function renew(int $userId): Subscription
    {
        $sub = $this->repo->findByUserId($userId)
            ?? throw new \DomainException('用户无订阅记录');
        return $this->repo->save($sub->renew(date('c', strtotime('+365 days'))));
    }
}
