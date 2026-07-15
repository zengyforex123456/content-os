<?php
declare(strict_types=1);
namespace App\Modules\Monetize\Infrastructure;

use App\Modules\Monetize\Domain\Commission;
use App\Modules\Monetize\Domain\CommissionRepositoryInterface;
use Converge\Contracts\DatabaseInterface;

class MysqlCommissionRepository implements CommissionRepositoryInterface
{
    public function __construct(private DatabaseInterface $db) {}

    public function save(Commission $c): Commission
    {
        $this->db->prepare('INSERT INTO commissions (referrer_user_id, new_user_id, amount, rate, source) VALUES (?, ?, ?, ?, ?)')
            ->execute([$c->referrerUserId, $c->newUserId, $c->amount, $c->rate, $c->source]);
        $id = $this->db->lastInsertId();
        return new Commission(
            referrerUserId: $c->referrerUserId, newUserId: $c->newUserId,
            amount: $c->amount, rate: $c->rate, source: $c->source, id: (int)$id,
        );
    }

    /** @return Commission[] */
    public function findByReferrer(int $userId): array
    {
        $rows = $this->db->prepare('SELECT * FROM commissions WHERE referrer_user_id=? ORDER BY id DESC')
            ->execute([$userId])->fetchAll();
        return array_map([$this, 'hydrate'], $rows);
    }

    public function totalByReferrer(int $userId): int
    {
        $row = $this->db->prepare(
            'SELECT COALESCE(SUM(ROUND(amount * rate)), 0) as total FROM commissions WHERE referrer_user_id=?'
        )->execute([$userId])->fetch();
        return (int)($row['total'] ?? 0);
    }

    private function hydrate(array $row): Commission
    {
        return new Commission(
            referrerUserId: (int)$row['referrer_user_id'], newUserId: (int)$row['new_user_id'],
            amount: (int)$row['amount'], rate: (float)$row['rate'],
            source: $row['source'] ?? '', id: (int)$row['id'],
        );
    }
}
