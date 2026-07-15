<?php
/**
 * Content-OS 入口 — 模块化单体 · Hooks 路由 · converge-ui 设计令牌
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

// 2. 路由分发
$page = $_GET['page'] ?? 'dashboard';
$title = match ($page) {
    'topics'    => '选题库',
    'pipeline'  => '内容流水线',
    'ai'        => 'AI 写作',
    'dashboard' => '工作台',
    default     => $page,
};

// 3. 渲染
$panels = Hooks::applyFilters('ui.dock.panels', []);
?>
<!DOCTYPE html>
<html lang="zh" class="content-os">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Content-OS — <?= htmlspecialchars($title) ?></title>
<link rel="stylesheet" href="../converge-ui/css/skeleton.css">
<link rel="stylesheet" href="../converge-ui/css/toast.css">
<style>
:root {
    --color-primary: #1E3A5F; --color-accent: #7C3AED;
    --surface-base: #F8FAFC; --surface-card: #FFFFFF;
    --content-primary: #1E293B; --content-secondary: #64748B;
    --border-default: #E2E8F0;
    --space-md: 16px; --space-lg: 24px; --radius-md: 8px;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Inter, system-ui, -apple-system, sans-serif; background: var(--surface-base); color: var(--content-primary); line-height: 1.6; }
.app { display: flex; min-height: 100vh; }
.sidebar { width: 240px; background: var(--surface-card); border-right: 1px solid var(--border-default); padding: var(--space-md); flex-shrink: 0; }
.sidebar-brand { font-size: 18px; font-weight: 700; padding: 12px 8px; margin-bottom: var(--space-lg); border-bottom: 1px solid var(--border-default); }
.sidebar-nav { list-style: none; }
.sidebar-nav a { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: var(--radius-md); text-decoration: none; color: var(--content-secondary); font-size: 14px; transition: all .15s; }
.sidebar-nav a:hover, .sidebar-nav a.active { background: #F1F5F9; color: var(--content-primary); }
.sidebar-nav a.active { background: #EDE9FE; color: var(--color-accent); font-weight: 600; }
.sidebar-nav .nav-icon { font-size: 18px; width: 24px; text-align: center; }
.main { flex: 1; padding: var(--space-lg); max-width: 1200px; }
.page-header { margin-bottom: var(--space-lg); }
.page-header h1 { font-size: 24px; font-weight: 700; }
.page-header p { color: var(--content-secondary); font-size: 14px; margin-top: 4px; }
.card { background: var(--surface-card); border: 1px solid var(--border-default); border-radius: var(--radius-md); padding: var(--space-lg); margin-bottom: var(--space-md); }
.card h2 { font-size: 16px; margin-bottom: 12px; }
.btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border: 1px solid var(--border-default); border-radius: var(--radius-md); background: var(--surface-card); color: var(--content-primary); font-size: 14px; cursor: pointer; text-decoration: none; transition: all .15s; }
.btn:hover { background: #F1F5F9; }
.btn-primary { background: var(--color-accent); color: #fff; border-color: var(--color-accent); }
.btn-primary:hover { opacity: .9; }
.badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; }
.badge-draft { background: #FEF3C7; color: #92400E; }
.badge-published { background: #DCFCE7; color: #166534; }
.badge-completed { background: #DBEAFE; color: #1E40AF; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: var(--space-md); margin-bottom: var(--space-lg); }
.stat-card { background: var(--surface-card); border: 1px solid var(--border-default); border-radius: var(--radius-md); padding: var(--space-md); text-align: center; }
.stat-value { font-size: 32px; font-weight: 700; color: var(--color-accent); }
.stat-label { font-size: 12px; color: var(--content-secondary); margin-top: 4px; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px; color: var(--content-secondary); }
.form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px 12px; border: 1px solid var(--border-default); border-radius: var(--radius-md); font-size: 14px; font-family: inherit; }
.form-group textarea { min-height: 120px; resize: vertical; }
.flow-steps { display: flex; gap: var(--space-md); align-items: center; margin-bottom: var(--space-lg); flex-wrap: wrap; }
.flow-step { display: flex; align-items: center; gap: 8px; padding: 12px 20px; background: var(--surface-card); border: 2px solid var(--border-default); border-radius: var(--radius-md); font-size: 14px; font-weight: 600; }
.flow-step.done { border-color: #22C55E; }
.flow-step.active { border-color: var(--color-accent); background: #EDE9FE; }
.flow-arrow { font-size: 20px; color: var(--content-tertiary); }
</style>
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="sidebar-brand">📡 Content-OS</div>
        <ul class="sidebar-nav">
            <li><a href="?page=dashboard" class="<?= $page==='dashboard'?'active':'' ?>"><span class="nav-icon">🏠</span> 工作台</a></li>
            <?php foreach ($panels as $panel): ?>
            <li><a href="<?= $panel['href'] ?? '?page='.$panel['id'] ?>" class="<?= $page===$panel['id']?'active':'' ?>"><span class="nav-icon"><?= $panel['icon'] ?? '📄' ?></span> <?= $panel['label'] ?? $panel['id'] ?></a></li>
            <?php endforeach; ?>
        </ul>
    </aside>
    <main class="main" id="app-content">
        <?php
        ob_start();
        Hooks::doAction("page.{$page}");
        $content = ob_get_clean();

        if (trim($content) === '') {
            echo '<div class="page-header"><h1>' . htmlspecialchars($title) . '</h1></div>';
        } else {
            echo $content;
        }
        ?>
    </main>
</div>
</body>
</html>
