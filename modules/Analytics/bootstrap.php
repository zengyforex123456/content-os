<?php
declare(strict_types=1);
use Converge\Core\Hook\Hooks;

Hooks::addFilter('ui.dock.panels', function(array $panels): array {
    $panels[] = ['id' => 'analytics', 'label' => '数据分析', 'icon' => '📊', 'href' => '?page=analytics'];
    return $panels;
});

Hooks::addAction('page.analytics', function() {
    $funnelStages = [
        ['name' => '发布内容', 'count' => 12, 'rate' => '100%'],
        ['name' => '内容浏览', 'count' => 1200, 'rate' => '28.3%'],
        ['name' => '访问主页', 'count' => 340, 'rate' => '25%'],
        ['name' => '添加微信', 'count' => 85, 'rate' => '25.9%'],
        ['name' => '开始试用', 'count' => 22, 'rate' => '36.4%'],
        ['name' => '付费', 'count' => 8, 'rate' => '—'],
    ];
    $attribution = [
        ['factor' => '发布时间段', 'correlation' => '50%', 'insight' => '晚上 18-22 点播放量最高'],
        ['factor' => '话题类型', 'correlation' => '40%', 'insight' => '技术架构类话题分享率更高'],
        ['factor' => '标题长度', 'correlation' => '30%', 'insight' => '标题 15-25 字互动率最高'],
    ];
?>
<div class="page-header">
    <h1>📊 数据分析</h1>
    <p>跨平台数据聚合 · 转化漏斗 · 爆款归因 · 周报月报</p>
</div>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-value">12</div><div class="stat-label">总内容数</div></div>
    <div class="stat-card"><div class="stat-value">12,000</div><div class="stat-label">总播放量</div></div>
    <div class="stat-card"><div class="stat-value">3</div><div class="stat-label">爆款内容</div></div>
    <div class="stat-card"><div class="stat-value">75</div><div class="stat-label">漏斗健康分</div></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-md)">
    <!-- 漏斗 -->
    <div class="card">
        <h2>📉 转化漏斗 (30天)</h2>
        <div style="margin-top:16px">
        <?php foreach ($funnelStages as $i => $s): ?>
            <div style="margin-bottom:12px">
                <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px">
                    <span><?= $s['name'] ?></span>
                    <span style="font-weight:600"><?= $s['count'] ?> <span style="color:var(--content-secondary);font-weight:400">(<?= $s['rate'] ?>)</span></span>
                </div>
                <div style="height:8px;background:var(--surface-base);border-radius:4px;overflow:hidden">
                    <?php $w = $i === 0 ? 100 : round($s['count'] / $funnelStages[0]['count'] * 100); ?>
                    <div style="height:100%;width:<?= $w ?>%;background:<?= $i < 2 ? 'var(--color-accent)' : ($i < 4 ? 'var(--color-warning)' : 'var(--color-success)') ?>;border-radius:4px"></div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>

    <!-- 归因 -->
    <div class="card">
        <h2>🔍 爆款归因 Top 3</h2>
        <?php foreach ($attribution as $a): ?>
        <div style="padding:12px;margin-bottom:8px;background:#F8FAFC;border-radius:8px;border-left:3px solid var(--color-accent)">
            <div style="font-weight:600;margin-bottom:4px"><?= $a['factor'] ?>
                <span class="badge badge-completed" style="margin-left:8px">相关性 <?= $a['correlation'] ?></span>
            </div>
            <div style="font-size:13px;color:var(--content-secondary)"><?= $a['insight'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="card" style="margin-top:var(--space-md)">
    <h2>📈 平台数据分布</h2>
    <table style="width:100%;border-collapse:collapse">
        <thead><tr style="text-align:left;border-bottom:2px solid var(--border-default)">
            <th style="padding:12px 8px">平台</th>
            <th style="padding:12px 8px">内容数</th>
            <th style="padding:12px 8px">总播放</th>
            <th style="padding:12px 8px">平均互动率</th>
            <th style="padding:12px 8px">爆款数</th>
        </tr></thead>
        <tbody>
            <tr style="border-bottom:1px solid var(--border-default)">
                <td style="padding:10px 8px">掘金</td><td style="padding:10px 8px">5</td><td style="padding:10px 8px">6,200</td><td style="padding:10px 8px">7.2%</td><td style="padding:10px 8px">2</td>
            </tr>
            <tr style="border-bottom:1px solid var(--border-default)">
                <td style="padding:10px 8px">知乎</td><td style="padding:10px 8px">4</td><td style="padding:10px 8px">3,800</td><td style="padding:10px 8px">5.1%</td><td style="padding:10px 8px">1</td>
            </tr>
            <tr style="border-bottom:1px solid var(--border-default)">
                <td style="padding:10px 8px">公众号</td><td style="padding:10px 8px">3</td><td style="padding:10px 8px">2,000</td><td style="padding:10px 8px">3.8%</td><td style="padding:10px 8px">0</td>
        </tr></tbody>
    </table>
</div>
<?php
});
