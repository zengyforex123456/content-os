<?php
declare(strict_types=1);
use Converge\Core\Hook\Hooks;

Hooks::addFilter('ui.dock.panels', function(array $panels): array {
    $panels[] = ['id' => 'distribution', 'label' => '分发管理', 'icon' => '🚀', 'href' => '?page=distribution'];
    return $panels;
});

Hooks::addAction('page.distribution', function() {
    $tasks = [
        ['title' => '如何用六边形架构重构 SaaS', 'platform' => 'juejin', 'scheduled' => '2026-07-16 09:00', 'status' => 'scheduled'],
        ['title' => '独立开发者的 10 个变现陷阱', 'platform' => 'zhihu', 'scheduled' => '2026-07-15 18:00', 'status' => 'published'],
        ['title' => 'SaaS 定价策略：从免费到企业版', 'platform' => 'wechat', 'scheduled' => '2026-07-17 12:00', 'status' => 'failed'],
    ];
    $platforms = [
        'wechat' => ['name' => '微信公众号', 'adapter' => '模拟', 'cover' => '900×383'],
        'zhihu' => ['name' => '知乎', 'adapter' => '模拟', 'cover' => '1200×630'],
        'juejin' => ['name' => '掘金', 'adapter' => '模拟', 'cover' => '1080×540'],
    ];
    $statusLabel = ['scheduled'=>'已排程','publishing'=>'发布中','published'=>'已发布','failed'=>'失败'];
    $statusBadge = ['scheduled'=>'badge-completed','publishing'=>'badge-completed','published'=>'badge-published','failed'=>'badge-draft'];
?>
<div class="page-header">
    <h1>🚀 分发管理</h1>
    <p>多平台排程发布 · 尺寸/标签自动适配 · 失败重试</p>
</div>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-value"><?= count($platforms) ?></div><div class="stat-label">已接入平台</div></div>
    <div class="stat-card"><div class="stat-value">3</div><div class="stat-label">待发布任务</div></div>
    <div class="stat-card"><div class="stat-value">1</div><div class="stat-label">今日已发布</div></div>
    <div class="stat-card"><div class="stat-value">MVP</div><div class="stat-label">适配器模式</div></div>
</div>

<div class="card">
    <h2>平台适配器</h2>
    <table style="width:100%;border-collapse:collapse">
        <thead><tr style="text-align:left;border-bottom:2px solid var(--border-default)">
            <th style="padding:12px 8px">平台</th>
            <th style="padding:12px 8px">适配器</th>
            <th style="padding:12px 8px">封面尺寸</th>
            <th style="padding:12px 8px">标签适配</th>
            <th style="padding:12px 8px">操作</th>
        </tr></thead>
        <tbody>
        <?php foreach ($platforms as $k => $p): ?>
        <tr style="border-bottom:1px solid var(--border-default)">
            <td style="padding:10px 8px"><?= $p['name'] ?></td>
            <td style="padding:10px 8px"><span class="badge badge-completed"><?= $p['adapter'] ?></span></td>
            <td style="padding:10px 8px"><?= $p['cover'] ?></td>
            <td style="padding:10px 8px;font-size:12px;color:var(--content-secondary)">SaaS + 架构</td>
            <td style="padding:10px 8px">
                <button class="btn" onclick="alert('OAuth 授权 (MVP 跳过)')">🔗 授权</button>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h2>分发任务</h2>
    <div style="margin-bottom:16px;display:flex;gap:8px">
        <button class="btn btn-primary" onclick="alert('对接 API: POST /api/distribution/schedule')">+ 新建排程</button>
        <button class="btn" onclick="alert('对接 API: POST /api/distribution/execute')">▶ 执行待发布</button>
    </div>
    <table style="width:100%;border-collapse:collapse">
        <thead><tr style="text-align:left;border-bottom:2px solid var(--border-default)">
            <th style="padding:12px 8px">内容</th>
            <th style="padding:12px 8px">平台</th>
            <th style="padding:12px 8px">预定时间</th>
            <th style="padding:12px 8px">状态</th>
        </tr></thead>
        <tbody>
        <?php foreach ($tasks as $t): ?>
        <tr style="border-bottom:1px solid var(--border-default)">
            <td style="padding:10px 8px"><?= htmlspecialchars($t['title']) ?></td>
            <td style="padding:10px 8px"><?= $t['platform'] ?></td>
            <td style="padding:10px 8px"><?= $t['scheduled'] ?></td>
            <td style="padding:10px 8px"><span class="badge <?= $statusBadge[$t['status']] ?>"><?= $statusLabel[$t['status']] ?></span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
});
