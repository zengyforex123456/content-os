<?php
declare(strict_types=1);
namespace App\Modules\Distribution\Domain;

/** 平台发布端口 — 每平台一个适配器实现此接口 */
interface PlatformAdapterInterface
{
    /** @param array<string,mixed> $options 封面/标签/尺寸等 */
    public function publish(string $title, string $body, array $options = []): PublishResult;

    /** 平台标识: wechat/zhihu/juejin/xiaohongshu */
    public function platform(): string;

    /** 平台是否可用 (OAuth 授权状态) */
    public function isAvailable(): bool;
}
