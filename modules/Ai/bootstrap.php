<?php
/** AI 模块 Bootstrap */
declare(strict_types=1);
use Converge\Core\Hook\Hooks;

Hooks::addFilter('ui.dock.panels', function(array $panels): array {
    $panels[] = ['id' => 'ai', 'label' => 'AI 写作', 'icon' => '🤖', 'href' => '?page=ai'];
    return $panels;
});

Hooks::addAction('page.ai', function() {
    $titles = [
        '微信公众号'  => '吸引点击、情绪共鸣、简洁有力',
        '知乎'       => '深度长文、理性克制、带数据支撑',
        '掘金'       => '技术干货、代码示例、实用导向',
        '小红书'     => '口语化、emoji、生活化表达',
    ];
?>
<div class="page-header">
    <h1>🤖 AI 写作助手</h1>
    <p>DeepSeek 驱动 · SaaS 垂直话术 · 平台差异化生成</p>
</div>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-value">4</div><div class="stat-label">支持平台</div></div>
    <div class="stat-card"><div class="stat-value">3</div><div class="stat-label">内容类型</div></div>
    <div class="stat-card"><div class="stat-value">DeepSeek</div><div class="stat-label">当前模型</div></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-md)">
    <!-- 标题生成 -->
    <div class="card">
        <h2>📋 标题生成</h2>
        <p style="font-size:13px;color:var(--content-secondary);margin-bottom:16px">根据话题 + 平台，生成 3 个高点击率标题候选</p>
        <div class="form-group">
            <label>话题</label>
            <input type="text" id="title-topic" placeholder="输入话题, 如: 六边形架构实战" value="如何用六边形架构重构 SaaS 项目">
        </div>
        <div class="form-group">
            <label>目标平台</label>
            <select id="title-platform">
                <?php foreach ($titles as $p => $hint): ?>
                <option value="<?= $p ?>"><?= $p ?> — <?= $hint ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>关键词 (逗号分隔)</label>
            <input type="text" id="title-keywords" placeholder="架构, 重构, SaaS" value="架构, 重构, SaaS">
        </div>
        <button class="btn btn-primary" onclick="generateTitle()">🤖 生成标题</button>
        <div id="title-result" style="margin-top:16px"></div>
    </div>

    <!-- 正文生成 -->
    <div class="card">
        <h2>📝 正文生成</h2>
        <p style="font-size:13px;color:var(--content-secondary);margin-bottom:16px">SaaS 垂直话术, 800-1500 字专业内容</p>
        <div class="form-group">
            <label>话题</label>
            <input type="text" id="copy-topic" placeholder="输入话题" value="如何用六边形架构重构 SaaS 项目">
        </div>
        <div class="form-group">
            <label>选定标题</label>
            <input type="text" id="copy-title" placeholder="选一个生成的标题, 或自己写">
        </div>
        <div class="form-group">
            <label>目标平台</label>
            <select id="copy-platform">
                <?php foreach ($titles as $p => $hint): ?>
                <option value="<?= $p ?>"><?= $p ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn btn-primary" onclick="generateCopy()">🤖 生成正文</button>
        <div id="copy-result" style="margin-top:16px"></div>
    </div>
</div>

<script>
function generateTitle() {
    var r = document.getElementById('title-result');
    r.innerHTML = '<p style="color:var(--content-secondary)">⏳ AI 生成中...</p>';
    // POST to API endpoint
    var kw = document.getElementById('title-keywords').value;
    fetch('/api/ai/generate-title', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'topic_title=' + encodeURIComponent(document.getElementById('title-topic').value)
            + '&platform=' + encodeURIComponent(document.getElementById('title-platform').value)
            + '&keywords=' + encodeURIComponent(JSON.stringify(kw ? kw.split(',').map(s=>s.trim()).filter(Boolean) : []))
    }).then(res => res.json()).then(data => {
        if (data.ok && data.data.candidates) {
            var html = '<h3 style="margin-bottom:8px">生成结果:</h3>';
            data.data.candidates.forEach(function(c, i) {
                html += '<div style="padding:8px;margin-bottom:4px;background:#F8FAFC;border-radius:4px;cursor:pointer" onclick="document.getElementById(\'copy-title\').value=this.textContent">' + (i+1) + '. ' + c + '</div>';
            });
            html += '<p style="font-size:11px;color:var(--content-secondary);margin-top:8px">点击标题可复制到正文生成</p>';
            r.innerHTML = html;
        } else {
            r.innerHTML = '<p style="color:var(--color-error)">❌ ' + (data.error || '生成失败') + '</p><p style="font-size:12px;color:var(--content-secondary)">请确保 DEEPSEEK_API_KEY 环境变量已配置</p>';
        }
    }).catch(function(e) {
        r.innerHTML = '<p style="color:var(--color-error)">❌ 网络错误: ' + e.message + '</p><p style="font-size:12px;color:var(--content-secondary)">API 端点未就绪 — Domain 层已就绪，等待 Infrastructure 接入</p>';
    });
}

function generateCopy() {
    var r = document.getElementById('copy-result');
    r.innerHTML = '<p style="color:var(--content-secondary)">⏳ AI 生成中 (可能需要 10-30 秒)...</p>';
    var kw = document.getElementById('title-keywords').value;
    fetch('/api/ai/generate-copy', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'topic_title=' + encodeURIComponent(document.getElementById('copy-topic').value)
            + '&selected_title=' + encodeURIComponent(document.getElementById('copy-title').value)
            + '&platform=' + encodeURIComponent(document.getElementById('copy-platform').value)
            + '&keywords=' + encodeURIComponent(JSON.stringify(kw ? kw.split(',').map(s=>s.trim()).filter(Boolean) : []))
    }).then(res => res.json()).then(data => {
        if (data.ok && data.data.output) {
            r.innerHTML = '<h3 style="margin-bottom:8px">生成结果:</h3>'
                + '<div style="white-space:pre-wrap;padding:16px;background:#F8FAFC;border-radius:8px;max-height:400px;overflow-y:auto;font-size:14px;line-height:1.8">' + data.data.output + '</div>'
                + '<p style="font-size:11px;color:var(--content-secondary);margin-top:8px">字数: ' + data.data.output.length + ' · 状态: ' + data.data.status + '</p>';
        } else {
            r.innerHTML = '<p style="color:var(--color-error)">❌ ' + (data.error || '生成失败') + '</p><p style="font-size:12px;color:var(--content-secondary)">请确保 DEEPSEEK_API_KEY 环境变量已配置</p>';
        }
    }).catch(function(e) {
        r.innerHTML = '<p style="color:var(--color-error)">❌ 网络错误: ' + e.message + '</p><p style="font-size:12px;color:var(--content-secondary)">API 端点未就绪 — Domain 层已就绪，等待 Infrastructure 接入</p>';
    });
}
</script>
<?php
});
