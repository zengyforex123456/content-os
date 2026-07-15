# Content-OS — SaaS 自媒体六边形系统

> 六边形架构 · Hooks 插件系统 · converge-core 基建
> PRD: `.claude/prd.md` · 能力目录: `.claude/reference/capability-catalog.md`
> 开发流程: `.claude/reference/dev-workflow.md` · 设计令牌: `.claude/reference/design-tokens.md`

## 架构铁律

### 依赖方向 (不可反向)
```
Controller(≤15行/方法) → Application(UseCase) → Domain(实体+端口)
                                        Infrastructure → Domain(端口)
```
Domain 定义接口，Infrastructure 实现。Controller 只做参数提取+调用+返回。

### 模块目录 (每个模块必须)
```
modules/{Name}/
├── Domain/{Name}.php                    ← 实体, 纯业务, 零 IO
├── Domain/{Name}RepositoryInterface.php ← 数据端口
├── Application/{Name}UseCase.php        ← 用例编排
├── Infrastructure/Mysql{Name}Repository.php ← 适配器
├── Controller/{Name}Controller.php      ← HTTP入口
└── bootstrap.php                        ← Hook注册+路由
```

### 禁止 ❌
- Domain/ 出现 `new mysqli`, `new PDO`, `use Illuminate\`
- Controller 方法 > 15 行 · 文件 > 150 行 · 函数 > 50 行
- 跨模块直接 `use` 其他模块的类
- 硬编码颜色/间距: `#3b82f6` / `width:300px`

### 必须 ✅
- 跨模块通信: `Hooks::addAction()` / `Hooks::doAction()` / `Hooks::addFilter()`
- 数据访问: `RepositoryInterface` (构造注入)
- 不可变实体: `public readonly` + 状态转换返回 `new self()`
- UI 颜色: `var(--color-primary)` 等 CSS 变量
- 编码顺序: Domain → Port → UseCase → Infrastructure → Controller → bootstrap

## 命令

```bash
bash ../converge-core/scripts/dev/module-scaffold.sh {Name}  # 生成模块骨架
php ../converge-core/scripts/dev/verify-modules.php          # 模块契约 (4项断言)
bash ../converge-core/scripts/ci/enforce-architecture.sh     # 架构门禁 (9项)
php vendor/bin/phpunit                                       # 单元测试
php -S localhost:8080 -t public/                             # 本地运行
```

## 常用类速查

| 需要什么 | 类 | 命名空间 |
|------|------|------|
| 插件系统 | `Hooks` | `Converge\Core\Hook` |
| 模块加载 | `ModuleLoader` | `Converge\Core\Module` |
| 数据库接口 | `DatabaseInterface` | `Converge\Contracts` |
| 认证 | `Auth` | `Converge\Security` |
| 权限 | `Permission` | `Converge\Security` |
| CSRF | `Csrf` | `Converge\Security` |
| 断路器 | `CircuitBreaker` | `Converge\Foundation\Resilience` |
| 重试 | `RetryHandler` | `Converge\Foundation\Resilience` |
| 日志 | `StructuredLogger` | `Converge\Foundation\Observability` |
| 健康检查 | `HealthChecker` | `Converge\Foundation\Observability` |
| 功能开关 | `FeatureRegistry` | `Converge\Foundation\System` |
| 数据库实现 | `MysqlAdapter` | `Converge\Foundation\System` |
| 翻译 | `__()` | 全局函数 (I18n) |
| 生产断言 | `AssertionEngine` | `Converge\Verify` |
| Alpine安全 | `AlpineHelper` | `Converge\Core\Helper` |

> 完整 40+ 类目录 → `.claude/reference/capability-catalog.md`

## 实体模板

```php
// 1. Domain — 实体, 状态转换返回 new self()
class Topic {
    public const DRAFT = 'draft', PUBLISHED = 'published', ARCHIVED = 'archived';
    public function __construct(
        public readonly string $title,
        public readonly string $status = self::DRAFT,
    ) {}
    public function publish(): self {
        if ($this->status !== self::DRAFT) throw new \DomainException('只有草稿可发布');
        return new self(title: $this->title, status: self::PUBLISHED);
    }
}

// 2. Domain — 端口
interface TopicRepositoryInterface {
    public function save(Topic $t): Topic;
    /** @return Topic[] */ public function findByStatus(string $s): array;
}

// 3. Infrastructure — 适配器, 构造注入 DatabaseInterface
class MysqlTopicRepository implements TopicRepositoryInterface {
    public function __construct(private \Converge\Contracts\DatabaseInterface $db) {}
}
```

> 完整 Product 模块示例 → `.claude/reference/dev-workflow.md`

## UI 设计令牌

只使用 CSS 变量: `--color-primary` `--color-accent` `--color-success` `--color-warning` `--color-error` `--surface-base` `--surface-card` `--content-primary` `--content-secondary` `--border-default` `--space-xs(4)` `--space-sm(8)` `--space-md(16)` `--space-lg(24)` `--space-xl(32)` `--radius-sm(4)` `--radius-md(8)` `--radius-lg(12)` `--radius-full(9999)`

组件: PageShell `../converge-ui/views/layout/_shell.php` · Dock `_dock-sidebar.php` · 命令面板 `_cmd-palette.php` · 日期切换器 `_date-switcher.php`

菜单注册: `Hooks::addFilter('ui.dock.panels', fn($p) => [...$p, ['id'=>'x','label'=>'名称','icon'=>'📝','order'=>10]]);`

> 完整令牌值 + 组件路径 → `.claude/reference/design-tokens.md`

## 模块地图

| 模块 | 目录 | 需求 |
|------|------|------|
| A1 选题库 | `modules/Content/` | R1 | ✅ |
| A3 内容流水线 | `modules/Pipeline/` | R3 | ✅ |
| B AI适配器 | `modules/Ai/` | R5-R6 | ✅ |
| C 分发适配器 | `modules/Distribution/` | R7-R8 | ✅ |
| D 数据适配器 | `modules/Analytics/` | R9-R11 | ✅ |
| E 变现适配器 | `modules/Monetize/` | R12-R13 | ✅ |

## 数据库约定

表名小写复数 `topics` · 主键 `id INT AUTO_INCREMENT PRIMARY KEY` · 时间戳 `created_at` `updated_at` · 状态 ENUM · JSON 类型 · 迁移 `database/migrations/` 按序号

## 验证清单

- [ ] `verify-modules.php` → 0 failures
- [ ] `enforce-architecture.sh` → 0 阻断
- [ ] Domain 零 IO · 跨模块仅 Hooks · 文件 ≤150行
