<?php
declare(strict_types=1);
namespace App\Modules\Monetize\Infrastructure;

use App\Modules\Monetize\Domain\Plan;
use App\Modules\Monetize\Domain\Subscription;
use App\Modules\Monetize\Domain\SubscriptionRepositoryInterface;
use Converge\Contracts\DatabaseInterface;

class MysqlSubscriptionRepository implements SubscriptionRepositoryInterface
{
    public function __construct(private DatabaseInterface $db) {}

    public function save(Subscription $s): Subscription
    {
        if ($s->id !== null) {
            $this->db->prepare('UPDATE subscriptions SET plan=?, expires_at=?, status=? WHERE id=?')
                ->execute([$s->plan->level, $s->expiresAt, $s->status, $s->id]);
            return $s;
        }
        $this->db->prepare('INSERT INTO subscriptions (user_id, plan, expires_at, status) VALUES (?, ?, ?, ?)')
            ->execute([$s->userId, $s->plan->level, $s->expiresAt, $s->status]);
        $id = $this->db->lastInsertId();
        return new Subscription(userId: $s->userId, plan: $s->plan,
            expiresAt: $s->expiresAt, status: $s->status, id: (int)$id);
    }

    public function findByUserId(int $userId): ?Subscription
    {
        $row = $this->db->prepare('SELECT * FROM subscriptions WHERE user_id=?')->execute([$userId])->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function findActive(): array
    {
        $rows = $this->db->prepare("SELECT * FROM subscriptions WHERE status IN ('trial','active') AND expires_at > datetime('now')")->execute([])->fetchAll();
        return array_map([$this, 'hydrate'], $rows);
    }

    private function hydrate(array $row): Subscription
    {
        return new Subscription(
            userId: (int)$row['user_id'], plan: Plan::of($row['plan']),
            expiresAt: $row['expires_at'], status: $row['status'], id: (int)$row['id'],
        );
    }
}
