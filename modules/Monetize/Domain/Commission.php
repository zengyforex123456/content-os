<?php
declare(strict_types=1);
namespace App\Modules\Monetize\Domain;

/** 佣金值对象 — 不可变 */
class Commission
{
    private const DEFAULT_RATE = 0.20;  // 标准分佣 20%

    public function __construct(
        public readonly int    $referrerUserId,
        public readonly int    $newUserId,
        public readonly int    $amount,       // 分 (cent)
        public readonly float  $rate = self::DEFAULT_RATE,
        public readonly string $source = '',
        public readonly ?int   $id = null,
    ) {
        if ($amount <= 0) throw new \DomainException('佣金金额必须大于 0');
        if ($rate <= 0 || $rate > 0.5) throw new \DomainException('佣金费率在 0-50% 之间');
    }

    /** 佣金金额 = 交易金额 × 费率 */
    public function commissionAmount(): int
    {
        return (int)round($this->amount * $this->rate);
    }

    /** 佣金金额 (元) */
    public function commissionYuan(): float
    {
        return round($this->commissionAmount() / 100, 2);
    }

    /** 是否达到提现门槛 (≥ 100 元) */
    public function canWithdraw(): bool
    {
        return $this->commissionAmount() >= 10000;
    }

    /** 来源标签 */
    public function sourceLabel(): string
    {
        return match ($this->source) {
            'blog'    => '博客推荐',
            'wechat'  => '公众号推荐',
            'referral' => '用户邀请',
            'partner' => '合作伙伴',
            default   => '其他渠道',
        };
    }

    /** 是否为高收益渠道 (费率 > 20%) */
    public function isPremium(): bool
    {
        return $this->rate > self::DEFAULT_RATE;
    }
}
