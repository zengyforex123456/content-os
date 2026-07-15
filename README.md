# Converge Skeleton — 新项目模板

> 模块化单体 · 整洁架构 · CI/CD · 金丝雀 · 自愈 — 开箱即用

## 5 分钟启动

```bash
git clone converge-skeleton my-app
cd my-app
bash scripts/reset-for-new-project.sh MyApp
php -S localhost:8080 -t public/
```

## 创建第一个模块

```bash
bash ../converge-core/scripts/dev/module-scaffold.sh Order
```

自动生成 `modules/Order/` 含整洁架构四层骨架。

## 目录

```
my-app/
├── modules/          ← 你的业务模块 (每个模块 Domain/Application/Infrastructure/Controller)
├── public/           ← Web 入口
├── config/           ← 环境配置
├── vendor/           ← converge/core + 依赖
└── .github/          ← CI/CD 流水线
```

## 能力清单 (来自 converge/core)

| 能力 | 说明 |
|------|------|
| Hooks 插件 | Action + Filter，对标 WordPress |
| 模块加载 | 依赖感知拓扑排序 |
| 认证 | RBAC + CSRF + AdminGate |
| 容错 | CircuitBreaker + RetryHandler |
| 可观测 | StructuredLogger + HealthChecker |
| 断言引擎 | 6层22项运行时验证 |
| 架构门禁 | enforce-architecture.sh 五原则 |
| CI/CD | GitHub Actions 部署流水线 |

## 写代码规则

- 业务逻辑放 `modules/*/Domain/` — 零框架依赖
- 数据库操作放 `modules/*/Infrastructure/` — implements Domain 接口
- Controller ≤15 行/方法
- 跨模块通信走 Hooks
