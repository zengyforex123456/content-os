<?php
declare(strict_types=1);
namespace App\Modules\Monetize\Domain;

/** 套餐值对象 — 不可变, 定义4级会员 */
class Plan
{
    public const FREE       = 'free';
    public const BASIC      = 'basic';
    public const PRO        = 'pro';
    public const ENTERPRISE = 'enterprise';

    /** 降级规则: 每个等级只能降到的级别列表 (空=不可降) */
    private const DOWNGRADE_ALLOWED = [
        self::ENTERPRISE => [self::PRO, self::BASIC],
        self::PRO        => [self::BASIC, self::FREE],
        self::BASIC      => [self::FREE],
        self::FREE       => [],
    ];

    /** 升级规则: 每个等级可升到的级别 */
    private const UPGRADE_ALLOWED = [
        self::FREE       => [self::BASIC, self::PRO, self::ENTERPRISE],
        self::BASIC      => [self::PRO, self::ENTERPRISE],
        self::PRO        => [self::ENTERPRISE],
        self::ENTERPRISE => [],
    ];

    /** 套餐定义 */
    private const PLANS = [
        self::FREE => ['name' => '免费版', 'price' => 0, 'priceLabel' => '¥0/月',
            'features' => ['选题库(3个)', 'AI生成(3次/月)', '单平台发布']],
        self::BASIC => ['name' => '基础版', 'price' => 9900, 'priceLabel' => '¥99/年',
            'features' => ['选题库(无限)', 'AI生成(30次/月)', '3平台发布', '基础数据']],
        self::PRO => ['name' => '高级版', 'price' => 29900, 'priceLabel' => '¥299/年',
            'features' => ['选题库(无限)', 'AI生成(无限)', '全平台发布', '漏斗分析', '爆款归因', '联盟分销']],
        self::ENTERPRISE => ['name' => '企业版', 'price' => 99900, 'priceLabel' => '¥999/年',
            'features' => ['高级版全部', '私有部署', 'API接入', '定制开发', '专属客服']],
    ];

    private function __construct(
        public readonly string $level,
    ) {}

    public static function of(string $level): self
    {
        if (!isset(self::PLANS[$level])) {
            throw new \DomainException("无效套餐等级: {$level}");
        }
        return new self($level);
    }

    public function name(): string { return self::PLANS[$this->level]['name']; }
    public function price(): int { return self::PLANS[$this->level]['price']; }
    public function priceLabel(): string { return self::PLANS[$this->level]['priceLabel']; }

    /** @return string[] */
    public function features(): array { return self::PLANS[$this->level]['features']; }

    /** 检查是否可以升级到目标等级 */
    public function canUpgradeTo(string $target): bool
    {
        if (!isset(self::PLANS[$target])) throw new \DomainException("无效套餐: {$target}");
        return in_array($target, self::UPGRADE_ALLOWED[$this->level] ?? [], true);
    }

    /** 检查是否可以降级到目标等级 */
    public function canDowngradeTo(string $target): bool
    {
        if (!isset(self::PLANS[$target])) throw new \DomainException("无效套餐: {$target}");
        return in_array($target, self::DOWNGRADE_ALLOWED[$this->level] ?? [], true);
    }

    /** 是否高于目标等级 */
    public function isHigherThan(self $other): bool
    {
        $order = [self::FREE => 0, self::BASIC => 1, self::PRO => 2, self::ENTERPRISE => 3];
        return ($order[$this->level] ?? 0) > ($order[$other->level] ?? 0);
    }

    /** 所有可用套餐 */
    public static function all(): array
    {
        return array_map(fn($k) => self::of($k), array_keys(self::PLANS));
    }

    /** 免费版是否有某项功能 */
    public function hasFeature(string $name): bool
    {
        return in_array($name, self::PLANS[$this->level]['features'], true);
    }
}
