<?php
/** Pipeline 模块 Bootstrap */
declare(strict_types=1);
use Converge\Core\Hook\Hooks;

Hooks::addFilter('ui.dock.panels', function(array $panels): array {
    $panels[] = ['id' => 'pipeline', 'label' => '内容管理', 'icon' => '✍️', 'href' => '?page=pipeline'];
    return $panels;
});

Hooks::addAction('page.pipeline', function() {
    $contents = [
        ['title' => '为什么 Laravel 比 WordPress 更适合 SaaS', 'platform' => 'juejin', 'status' => 'published', 'words' => 1200],
        ['title' => '一人公司如何做 SEO — 架构师视角', 'platform' => 'zhihu', 'status' => 'review', 'words' => 850],
        ['title' => 'SaaS 定价策略：从免费到企业版', 'platform' => 'wechat', 'status' => 'draft', 'words' => 320],
    ];
    $statusLabel = ['draft' => '草稿', 'review' => '审核中', 'published' => '已发布'];
    $statusBadge = ['draft' => 'badge-draft', 'review' => 'badge-completed', 'published' => 'badge-published'];
?>
<div class="page-header">
    <h1>✍️ 内容流水线</h1>
    <p>草稿 → 审核 → 发布 → 复盘 — 内容生产流水线</p>
</div>

<div class="flow-steps">
    <div class="flow-step done">📝 选题</div>
    <div class="flow-arrow">→</div>
    <div class="flow-step active">✍️ 撰写</div>
    <div class="flow-arrow">→</div>
    <div class="flow-step">🔍 审核</div>
    <div class="flow-arrow">→</div>
    <div class="flow-step">🚀 发布</div>
    <div class="flow-arrow">→</div>
    <div class="flow-step">📊 复盘</div>
</div>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-value"><?= count($contents) ?></div><div class="stat-label">内容总数</div></div>
    <div class="stat-card"><div class="stat-value">1</div><div class="stat-label">已发布</div></div>
    <div class="stat-card"><div class="stat-value">2370</div><div class="stat-label">总字数</div></div>
</div>

<div class="card">
    <h2>内容列表</h2>
    <div style="margin-bottom:16px">
        <a href="?page=ai" class="btn btn-primary">🤖 AI 生成新内容</a>
    </div>
    <table style="width:100%;border-collapse:collapse">
        <thead><tr style="text-align:left;border-bottom:2px solid var(--border-default)">
            <th style="padding:12px 8px">标题</th>
            <th style="padding:12px 8px">平台</th>
            <th style="padding:12px 8px">字数</th>
            <th style="padding:12px 8px">状态</th>
        </tr></thead>
        <tbody>
        <?php foreach ($contents as $c): ?>
        <tr style="border-bottom:1px solid var(--border-default)">
            <td style="padding:10px 8px"><?= htmlspecialchars($c['title']) ?></td>
            <td style="padding:10px 8px"><?= $c['platform'] ?></td>
            <td style="padding:10px 8px"><?= $c['words'] ?></td>
            <td style="padding:10px 8px"><span class="badge <?= $statusBadge[$c['status']] ?>"><?= $statusLabel[$c['status']] ?></span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
});
