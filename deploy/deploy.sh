#!/usr/bin/env bash
# ============================================================================
# Laravel Blog 部署脚本
# ============================================================================
# 用法：
#   1. 首次部署：bash deploy.sh init
#   2. 日常更新：bash deploy.sh
#
# 前提条件：
#   - PHP 8.3+ / Composer / Node.js 20+ / npm
#   - Nginx 或 Apache（root 指向 public/）
#   - 已配置好 .env 文件（首次部署时手动 cp .env.example .env 并编辑）
# ============================================================================

set -e

# 项目根目录（脚本所在目录的上一级）
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_DIR"

# 颜色输出
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

info()  { echo -e "${GREEN}[✓]${NC} $1"; }
warn()  { echo -e "${YELLOW}[!]${NC} $1"; }
error() { echo -e "${RED}[✗]${NC} $1"; }

echo "=========================================="
echo "  Laravel Blog 部署脚本"
echo "  目录: $PROJECT_DIR"
echo "  时间: $(date '+%Y-%m-%d %H:%M:%S')"
echo "=========================================="
echo ""

# ---------------------------------------------------------------------------
# 首次初始化
# ---------------------------------------------------------------------------
if [[ "$1" == "init" ]]; then
    info "首次部署模式"

    # 检查 .env 是否存在
    if [[ ! -f .env ]]; then
        warn ".env 不存在，从 .env.example 复制"
        cp .env.example .env
        warn "请编辑 .env 配置生产环境后再运行：nano .env"
        warn "至少配置：APP_NAME / APP_URL / APP_ENV=production / APP_DEBUG=false / DB_*"
        warn "配置完成后再次运行：bash deploy.sh"
        exit 0
    fi

    # 生成 APP_KEY
    if grep -q "^APP_KEY=$" .env; then
        info "生成 APP_KEY"
        php artisan key:generate --force
    fi

    # 创建 SQLite 数据库文件（如果用 SQLite）
    if grep -q "^DB_CONNECTION=sqlite" .env && [[ ! -f database/database.sqlite ]]; then
        info "创建 SQLite 数据库文件"
        touch database/database.sqlite
    fi
fi

# ---------------------------------------------------------------------------
# 检查 .env
# ---------------------------------------------------------------------------
if [[ ! -f .env ]]; then
    error ".env 文件不存在！请先运行：bash deploy.sh init"
    exit 1
fi

# ---------------------------------------------------------------------------
# 进入维护模式
# ---------------------------------------------------------------------------
info "进入维护模式"
php artisan down

# ---------------------------------------------------------------------------
# 拉取最新代码
# ---------------------------------------------------------------------------
if [[ -d .git ]]; then
    info "拉取最新代码"
    git pull --ff-only
fi

# ---------------------------------------------------------------------------
# 安装依赖
# ---------------------------------------------------------------------------
info "安装 PHP 依赖（--no-dev 优化体积）"
composer install --no-dev --optimize-autoloader --no-interaction

info "安装前端依赖"
npm install --ignore-scripts

info "构建前端资源"
npm run build

# ---------------------------------------------------------------------------
# 数据库迁移
# ---------------------------------------------------------------------------
info "执行数据库迁移"
php artisan migrate --force

# ---------------------------------------------------------------------------
# 缓存优化
# ---------------------------------------------------------------------------
info "缓存配置"
php artisan config:cache

info "缓存路由"
php artisan route:cache

info "缓存视图"
php artisan view:cache

info "缓存事件"
php artisan event:cache

# ---------------------------------------------------------------------------
# Storage 软链接
# ---------------------------------------------------------------------------
info "创建 storage 软链接"
php artisan storage:link

# ---------------------------------------------------------------------------
# 清理旧缓存
# ---------------------------------------------------------------------------
info "清理旧缓存"
php artisan optimize:clear
php artisan config:cache
php artisan route:cache

# ---------------------------------------------------------------------------
# 权限设置
# ---------------------------------------------------------------------------
info "设置目录权限"
chmod -R 775 storage bootstrap/cache
# 尝试将所有者改为 www-data（Ubuntu/Debian 默认），失败则忽略
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || warn "无法修改所有者（非 root 用户），请手动执行：sudo chown -R www-data:www-data storage bootstrap/cache"

# ---------------------------------------------------------------------------
# 重启队列 worker
# ---------------------------------------------------------------------------
info "重启队列 worker"
php artisan queue:restart 2>/dev/null || warn "无队列 worker 在运行（需配置 Supervisor）"

# ---------------------------------------------------------------------------
# 退出维护模式
# ---------------------------------------------------------------------------
info "退出维护模式"
php artisan up

echo ""
echo "=========================================="
info "部署完成！"
echo "=========================================="
echo ""
echo "后续检查："
echo "  1. 访问网站确认正常"
echo "  2. 查看错误日志：tail -f storage/logs/laravel.log"
echo "  3. 确认队列 worker 运行：sudo supervisorctl status"
echo ""
