<?php
declare(strict_types=1);
namespace App\Modules\Monetize\Domain;

interface SubscriptionRepositoryInterface
{
    public function save(Subscription $s): Subscription;
    public function findByUserId(int $userId): ?Subscription;
    public function findActive(): array;
}

interface CommissionRepositoryInterface
{
    public function save(Commission $c): Commission;
    /** @return Commission[] */
    public function findByReferrer(int $userId): array;
    public function totalByReferrer(int $userId): int;
}
