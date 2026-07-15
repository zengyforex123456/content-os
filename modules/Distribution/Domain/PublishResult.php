<?php
declare(strict_types=1);
namespace App\Modules\Distribution\Domain;

/** 发布结果值对象 — 不可变 */
class PublishResult
{
    public function __construct(
        public readonly bool   $success,
        public readonly string $message,
        public readonly string $remoteUrl = '',
        public readonly string $remoteId = '',
    ) {}

    public static function ok(string $url, string $id): self
    {
        return new self(success: true, message: '发布成功', remoteUrl: $url, remoteId: $id);
    }

    public static function fail(string $reason): self
    {
        return new self(success: false, message: $reason);
    }
}
