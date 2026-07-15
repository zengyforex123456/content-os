<?php
declare(strict_types=1);
namespace App\Modules\Monetize\Domain;

/** 订阅实体 — 用户·套餐·到期·状态 */
class Subscription
{
    public const STATUS_TRIAL    = 'trial';
    public const STATUS_ACTIVE   = 'active';
    public const STATUS_EXPIRED  = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    private const VALID_TRANSITIONS = [
        self::STATUS_TRIAL    => [self::STATUS_ACTIVE, self::STATUS_EXPIRED],
        self::STATUS_ACTIVE   => [self::STATUS_EXPIRED, self::STATUS_CANCELLED],
        self::STATUS_EXPIRED  => [self::STATUS_ACTIVE],
        self::STATUS_CANCELLED => [],
    ];

    public function __construct(
        public readonly int    $userId,
        public readonly Plan   $plan,
        public readonly string $expiresAt,  // ISO 8601
        public readonly string $status = self::STATUS_TRIAL,
        public readonly ?int   $id = null,
    ) {}

    /** 激活 (试用→付费) */
    public function activate(): self { return $this->transition(self::STATUS_ACTIVE); }

    /** 取消 */
    public function cancel(): self { return $this->transition(self::STATUS_CANCELLED); }

    /** 续费 */
    public function renew(string $newExpiresAt): self
    {
        if ($this->status !== self::STATUS_EXPIRED && $this->status !== self::STATUS_ACTIVE) {
            throw new \DomainException('只有已过期或活跃订阅可续费');
        }
        return new self(userId: $this->userId, plan: $this->plan,
            expiresAt: $newExpiresAt, status: self::STATUS_ACTIVE, id: $this->id);
    }

    /** 升级套餐 */
    public function upgrade(Plan $newPlan, string $newExpiresAt): self
    {
        if (!$this->plan->canUpgradeTo($newPlan->level)) {
            throw new \DomainException("不可从 {$this->plan->name()} 升级到 {$newPlan->name()}");
        }
        return new self(userId: $this->userId, plan: $newPlan,
            expiresAt: $newExpiresAt, status: self::STATUS_ACTIVE, id: $this->id);
    }

    /** 是否有效 */
    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_TRIAL, self::STATUS_ACTIVE], true)
            && strtotime($this->expiresAt) > time();
    }

    /** 剩余天数 */
    public function daysRemaining(): int
    {
        return max(0, (int)ceil((strtotime($this->expiresAt) - time()) / 86400));
    }

    private function transition(string $to): self
    {
        $allowed = self::VALID_TRANSITIONS[$this->status] ?? [];
        if (!in_array($to, $allowed, true)) {
            throw new \DomainException("订阅状态不可从 {$this->status} 转换为 {$to}");
        }
        return new self(userId: $this->userId, plan: $this->plan,
            expiresAt: $this->expiresAt, status: $to, id: $this->id);
    }
}
