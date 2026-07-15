# {{PROJECT_NAME}} — 基于 Converge 骨架

> 模块化单体 · 六边形架构 · Hooks 插件系统 · CI/CD 门禁
> converge-core v1.0 提供全部基建能力

## 架构铁律 (Claude 必须遵守)

### 1. 分层依赖 (L1→L4, 不可反向)
```
Controller (≤15行/方法) → UseCase → RepositoryInterface ← Infrastructure
    (HTTP入口)              (编排)      (端口,在Domain)     (适配器)
```

### 2. 模块结构 (每个模块必须)
```
modules/{Name}/
├── Domain/{Name}.php                   ← 实体, 纯业务规则, 零 IO
├── Domain/{Name}RepositoryInterface.php ← 端口
├── Application/{Name}UseCase.php        ← 用例编排
├── Infrastructure/Mysql{Name}Repository.php ← 适配器
└── Controller/{Name}Controller.php      ← HTTP入口, ≤15行/方法
```

### 3. 禁止模式
- ❌ Domain/ 下出现 `new mysqli`, `new PDO`, `use Illuminate\`
- ❌ Controller 方法 > 15 行
- ❌ 跨模块直接 `use` 其他模块的类
- ❌ 文件 > 150 行
- ❌ 函数 > 50 行

### 4. 必须使用
- ✅ 跨模块通信走 Hooks: `Hooks::addAction()` / `Hooks::doAction()`
- ✅ 数据访问走 RepositoryInterface (构造注入)
- ✅ 不可变实体: `public readonly` + `transitionTo() → new self()`

## 关键类路径 (converge-core)

| 用途 | 类 | 命名空间 |
|------|------|------|
| Hooks | `Hooks` | `Converge\Core\Hook` |
| 模块加载 | `ModuleLoader` | `Converge\Core\Module` |
| 安全编码 | `AlpineHelper` | `Converge\Core\Helper` |
| 认证 | `Auth` | `Converge\Security` |
| 熔断 | `CircuitBreaker` | `Converge\Foundation\Resilience` |
| 日志 | `StructuredLogger` | `Converge\Foundation\Observability` |
| 数据库 | `DatabaseInterface` | `Converge\Contracts` |
| 缓存 | `ArrayCache` | `Converge\Foundation\System\Cache` |
| 断言 | `AssertionEngine` | `Converge\Verify` |
| 国际化 | `Locale` / `__()` | `Converge\I18n` |

## 命令

```bash
# 创建新模块
bash ../converge-core/scripts/dev/module-scaffold.sh {Name}

# 验证
php ../converge-core/scripts/dev/verify-modules.php

# 架构门禁
bash ../converge-core/scripts/ci/enforce-architecture.sh

# 本地运行
php -S localhost:8080 -t public/
```

## 代码示例

### 创建实体 (Domain)
```php
class Product {
    public function __construct(
        public readonly string $name,
        public readonly float $price,
        public readonly string $status = 'draft',
    ) {}
    public function activate(): self {
        return new self(name: $this->name, price: $this->price, status: 'active');
    }
}
```

### 定义端口 (Domain)
```php
interface ProductRepositoryInterface {
    public function save(Product $p): Product;
}
```

### 实现适配器 (Infrastructure)
```php
class MysqlProductRepository implements ProductRepositoryInterface {
    public function __construct(private DatabaseInterface $db) {}
    public function save(Product $p): Product { /* SQL */ }
}
```

### 用例编排 (Application)
```php
class CreateProductUseCase {
    public function __construct(private ProductRepositoryInterface $repo) {}
    public function execute(string $name, float $price): Product {
        return $this->repo->save(new Product(name: $name, price: $price));
    }
}
```

## 验证清单 (提交前)

- [ ] `php ../converge-core/scripts/dev/verify-modules.php` → 0 failures
- [ ] `bash ../converge-core/scripts/ci/enforce-architecture.sh` → 0 阻断
- [ ] Domain 层 0 处 IO 调用
- [ ] 跨模块通信仅通过 Hooks
