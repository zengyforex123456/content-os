<?php
/**
 * Converge Skeleton — 最小入口 (10 行)
 *
 * 所有基建能力 (Hooks, Auth, ModuleLoader, CI/CD, 自愈) 来自 converge/core。
 * 你只写业务逻辑 — modules/ 下的模块代码。
 */
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/env.php';

use Converge\Core\Hook\Hooks;
use Converge\Core\Module\ModuleLoader;

// 1. 加载所有业务模块 (拓扑排序)
$loader = new ModuleLoader(__DIR__ . '/../modules');
$order  = $loader->resolve();
$count  = $loader->loadAll();

// 2. 路由分发 (最小实现 — 替换为你的 Router)
$page = $_GET['page'] ?? 'home';
Hooks::doAction("page.{$page}");

// 3. 渲染
echo "<!DOCTYPE html><html lang='zh'><head><meta charset='UTF-8'>"
   . "<title>My App</title></head><body>"
   . "<p>Converge skeleton ready. {$count} modules loaded. Page: {$page}</p>"
   . "</body></html>";
