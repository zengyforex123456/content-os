<?php
/** Content 模块 Bootstrap */
declare(strict_types=1);
use Converge\Core\Hook\Hooks;

Hooks::addFilter('ui.dock.panels', function(array $panels): array {
    $panels[] = ['id' => 'topics', 'label' => '选题库', 'icon' => '📝', 'href' => '?page=topics'];
    return $panels;
});

Hooks::addAction('page.topics', function() {
    $topics = [
        ['title' => '如何用六边形架构重构 SaaS', 'platform' => 'juejin', 'keywords' => ['架构','重构','SaaS'], 'status' => 'ready'],
        ['title' => '独立开发者的 10 个变现陷阱', 'platform' => 'zhihu', 'keywords' => ['独立开发','变现'], 'status' => 'draft'],
        ['title' => '从 0 到 1 搭建 CI/CD 流水线', 'platform' => 'juejin', 'keywords' => ['CI/CD','DevOps'], 'status' => 'published'],
    ];
?>
<div class="page-header">
    <h1>📝 选题库</h1>
    <p>爆款公式 + 关键词 + 对标库 — 选题不再是玄学</p>
    <div style="margin-top:16px">
        <button class="btn btn-primary" onclick="alert('对接 API: POST /api/topics')">+ 新建选题</button>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-value"><?= count($topics) ?></div><div class="stat-label">总选题</div></div>
    <div class="stat-card"><div class="stat-value">2</div><div class="stat-label">待就绪</div></div>
    <div class="stat-card"><div class="stat-value">1</div><div class="stat-label">已发布</div></div>
</div>

<div class="card">
    <h2>选题列表</h2>
    <table style="width:100%;border-collapse:collapse">
        <thead><tr style="text-align:left;border-bottom:2px solid var(--border-default)">
            <th style="padding:12px 8px">标题</th>
            <th style="padding:12px 8px">平台</th>
            <th style="padding:12px 8px">关键词</th>
            <th style="padding:12px 8px">状态</th>
        </tr></thead>
        <tbody>
        <?php foreach ($topics as $t): ?>
        <tr style="border-bottom:1px solid var(--border-default)">
            <td style="padding:10px 8px"><?= htmlspecialchars($t['title']) ?></td>
            <td style="padding:10px 8px"><?= $t['platform'] ?></td>
            <td style="padding:10px 8px"><?= implode(', ', $t['keywords']) ?></td>
            <td style="padding:10px 8px"><span class="badge badge-<?= $t['status'] ?>"><?= $t['status'] ?></span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
});
