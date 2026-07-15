#!/bin/bash
# reset-for-new-project.sh — 一键重置为新项目
# 用法: bash scripts/reset-for-new-project.sh MyNewApp
# 效果: 清理模板数据 → 重置 composer.json → 初始化 Git

set -e

APP_NAME="${1:-my-app}"
echo "═══ 生成新项目: $APP_NAME ═══"

# 1. 重置 composer.json
sed -i "s|\"my-project/app\"|\"$APP_NAME/app\"|" composer.json

# 2. 复制 .env
cp .env.example .env

# 3. 清理 Git 历史
rm -rf .git
git init
git add .
git commit -m "chore: init from converge-skeleton"

# 4. 安装依赖
composer install

echo ""
echo "✅ $APP_NAME 已就绪！"
echo "   cd . && php -S localhost:8080 -t public/"
echo "   bash ../converge-core/scripts/dev/module-scaffold.sh YourFeature"
echo ""
echo "立即开始写业务代码: modules/ 目录下新建模块即可。"
