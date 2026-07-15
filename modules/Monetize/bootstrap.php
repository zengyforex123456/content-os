<?php
declare(strict_types=1);
use Converge\Core\Hook\Hooks;

Hooks::addFilter('ui.dock.panels', function(array $panels): array {
    $panels[] = ['id' => 'monetize', 'label' => '变现管理', 'icon' => '💰', 'href' => '?page=monetize'];
    return $panels;
});

Hooks::addAction('page.monetize', function() {
    $plans = [
        ['level'=>'free','name'=>'免费版','price'=>'¥0','features'=>'选题库(3个)·AI生成(3次/月)·单平台','users'=>28],
        ['level'=>'basic','name'=>'基础版','price'=>'¥99/年','features'=>'选题库无限·AI 30次/月·3平台·基础数据','users'=>12],
        ['level'=>'pro','name'=>'高级版','price'=>'¥299/年','features'=>'无限AI·全平台·漏斗分析·归因·分销','users'=>5],
        ['level'=>'enterprise','name'=>'企业版','price'=>'¥999/年','features'=>'私有部署·API接入·定制开发·专属客服','users'=>1],
    ];
    $commissions = [
        ['source'=>'博客推荐','count'=>8,'amount'=>126000,'yuan'=>1260],
        ['source'=>'用户邀请','count'=>3,'amount'=>89500,'yuan'=>895],
        ['source'=>'公众号','count'=>2,'amount'=>39800,'yuan'=>398],
    ];
?>
<div class="page-header">
    <h1>💰 变现管理</h1>
    <p>会员体系 · 知识付费 · 联盟分销 · 私域变现</p>
</div>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-value">46</div><div class="stat-label">付费用户</div></div>
    <div class="stat-card"><div class="stat-value">¥3,580</div><div class="stat-label">月经常性收入</div></div>
    <div class="stat-card"><div class="stat-value">¥2,553</div><div class="stat-label">累计佣金</div></div>
    <div class="stat-card"><div class="stat-value">20%</div><div class="stat-label">标准分佣率</div></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-md)">
    <!-- 套餐 -->
    <div class="card">
        <h2>📦 会员套餐</h2>
        <?php foreach ($plans as $p): ?>
        <div style="padding:16px;margin-bottom:8px;background:#F8FAFC;border-radius:8px;border:2px solid <?= $p['level']==='pro'?'var(--color-accent)':'var(--border-default)' ?>">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                <span style="font-weight:700;font-size:16px"><?= $p['name'] ?></span>
                <span style="font-weight:700;color:var(--color-accent)"><?= $p['price'] ?></span>
            </div>
            <div style="font-size:13px;color:var(--content-secondary);margin-bottom:8px"><?= $p['features'] ?></div>
            <div style="display:flex;justify-content:space-between;align-items:center">
                <span style="font-size:12px;color:var(--content-tertiary)"><?= $p['users'] ?> 用户</span>
                <span class="badge badge-<?= $p['level']==='free'?'draft':'published' ?>"><?= $p['level']==='free'?'免费':'付费' ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- 佣金 -->
    <div class="card">
        <h2>🤝 联盟分销</h2>
        <p style="font-size:13px;color:var(--content-secondary);margin-bottom:16px">标准分佣 20% · 二级分销 · 实时追踪</p>
        <?php foreach ($commissions as $c): ?>
        <div style="padding:12px;margin-bottom:8px;background:#F8FAFC;border-radius:8px">
            <div style="display:flex;justify-content:space-between;align-items:center">
                <span style="font-weight:600"><?= $c['source'] ?></span>
                <span style="font-weight:700;color:var(--color-success)">¥<?= $c['yuan'] ?></span>
            </div>
            <div style="font-size:12px;color:var(--content-tertiary);margin-top:4px"><?= $c['count'] ?> 笔 · <?= $c['amount'] ?> 分 (cent)</div>
        </div>
        <?php endforeach; ?>
        <div style="margin-top:16px;padding:12px;background:#EDE9FE;border-radius:8px;text-align:center">
            <span style="font-size:14px;font-weight:600">累计佣金 ¥2,553 · 可提现 ✅</span>
        </div>
        <div style="margin-top:12px;display:flex;gap:8px">
            <button class="btn btn-primary" onclick="alert('对接 API: POST /api/monetize/subscribe')">💳 升级套餐</button>
            <button class="btn" onclick="alert('对接 API: GET /api/monetize/commission')">📊 佣金明细</button>
        </div>
    </div>
</div>
<?php
});
