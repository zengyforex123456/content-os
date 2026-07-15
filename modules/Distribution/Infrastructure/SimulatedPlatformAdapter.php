<?php
declare(strict_types=1);
namespace App\Modules\Distribution\Infrastructure;

use App\Modules\Distribution\Domain\PlatformAdapterInterface;
use App\Modules\Distribution\Domain\PublishResult;

/** MVP 模拟平台适配器 — 不实际发布，返回模拟成功结果 */
class SimulatedPlatformAdapter implements PlatformAdapterInterface
{
    public function __construct(
        private string $platformName,
        private bool   $available = true,
    ) {}

    public function platform(): string
    {
        return $this->platformName;
    }

    public function isAvailable(): bool
    {
        return $this->available;
    }

    public function publish(string $title, string $body, array $options = []): PublishResult
    {
        if (!$this->available) {
            return PublishResult::fail("平台 '{$this->platformName}' 未授权或不可用");
        }

        // 模拟发布延迟
        $fakeUrl = match ($this->platformName) {
            'wechat' => 'https://mp.weixin.qq.com/s/' . bin2hex(random_bytes(8)),
            'zhihu'  => 'https://zhuanlan.zhihu.com/p/' . random_int(100000000, 999999999),
            'juejin' => 'https://juejin.cn/post/' . bin2hex(random_bytes(8)),
            default  => 'https://' . $this->platformName . '.com/post/' . bin2hex(random_bytes(4)),
        };

        $fakeId = bin2hex(random_bytes(12));

        return PublishResult::ok($fakeUrl, $fakeId);
    }
}
