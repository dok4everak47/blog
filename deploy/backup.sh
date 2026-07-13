#!/usr/bin/env bash
# ============================================================================
# Laravel Blog 数据库备份脚本
# ============================================================================
# 用法：
#   bash deploy/backup.sh
#
# 自动化（crontab -e）：
#   # 每天凌晨 3 点备份
#   0 3 * * * cd /var/www/blog && bash deploy/backup.sh >> storage/logs/backup.log 2>&1
#
# 支持：PostgreSQL / MySQL / SQLite
# 保留策略：最近 30 天的备份
# ============================================================================

set -e

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_DIR"

# 备份目录
BACKUP_DIR="storage/app/backups"
mkdir -p "$BACKUP_DIR"

# 保留天数
KEEP_DAYS=30

# 备份文件名（带时间戳）
TIMESTAMP=$(date '+%Y%m%d_%H%M%S')
BACKUP_FILE="$BACKUP_DIR/blog_${TIMESTAMP}.sql"

# 读取 .env 中的数据库配置
DB_CONNECTION=$(grep -E "^DB_CONNECTION=" .env | cut -d= -f2)
DB_HOST=$(grep -E "^DB_HOST=" .env | cut -d= -f2)
DB_PORT=$(grep -E "^DB_PORT=" .env | cut -d= -f2)
DB_DATABASE=$(grep -E "^DB_DATABASE=" .env | cut -d= -f2)
DB_USERNAME=$(grep -E "^DB_USERNAME=" .env | cut -d= -f2)
DB_PASSWORD=$(grep -E "^DB_PASSWORD=" .env | cut -d= -f2)

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

info()  { echo -e "${GREEN}[✓]${NC} $1"; }
warn()  { echo -e "${YELLOW}[!]${NC} $1"; }
error() { echo -e "${RED}[✗]${NC} $1"; }

echo "=========================================="
echo "  数据库备份"
echo "  时间: $(date '+%Y-%m-%d %H:%M:%S')"
echo "=========================================="

# ---------------------------------------------------------------------------
# 根据数据库类型选择备份方式
# ---------------------------------------------------------------------------
case "$DB_CONNECTION" in
    sqlite)
        DB_PATH="${DB_DATABASE:-database/database.sqlite}"
        if [[ -f "$DB_PATH" ]]; then
            # SQLite 备份用 .dump 导出为 SQL 文本（便于跨版本恢复）
            # 用 .backup 命令更安全（在线备份，不会阻塞写入）
            info "SQLite 数据库备份: $DB_PATH"
            sqlite3 "$DB_PATH" ".dump" > "$BACKUP_FILE"
            # 同时备份原始文件（双保险）
            cp "$DB_PATH" "$BACKUP_DIR/blog_${TIMESTAMP}.sqlite"
            info "原始文件已备份: $BACKUP_DIR/blog_${TIMESTAMP}.sqlite"
        else
            error "SQLite 文件不存在: $DB_PATH"
            exit 1
        fi
        ;;

    mysql)
        info "MySQL 数据库备份: $DB_DATABASE"
        if command -v mysqldump &>/dev/null; then
            mysqldump \
                --host="${DB_HOST:-127.0.0.1}" \
                --port="${DB_PORT:-3306}" \
                --user="$DB_USERNAME" \
                --password="$DB_PASSWORD" \
                --single-transaction \
                --quick \
                --routines \
                --triggers \
                "$DB_DATABASE" > "$BACKUP_FILE"
        else
            error "mysqldump 命令不存在，请安装 mysql-client"
            exit 1
        fi
        ;;

    pgsql)
        info "PostgreSQL 数据库备份: $DB_DATABASE"
        if command -v pg_dump &>/dev/null; then
            PGPASSWORD="$DB_PASSWORD" pg_dump \
                --host="${DB_HOST:-127.0.0.1}" \
                --port="${DB_PORT:-5432}" \
                --username="$DB_USERNAME" \
                --format=custom \
                --no-owner \
                --no-privileges \
                "$DB_DATABASE" > "${BACKUP_FILE}.dump"
            # 同时导出纯 SQL 文本（便于人工查看）
            PGPASSWORD="$DB_PASSWORD" pg_dump \
                --host="${DB_HOST:-127.0.0.1}" \
                --port="${DB_PORT:-5432}" \
                --username="$DB_USERNAME" \
                --format=plain \
                --no-owner \
                --no-privileges \
                "$DB_DATABASE" > "$BACKUP_FILE"
        else
            error "pg_dump 命令不存在，请安装 postgresql-client"
            exit 1
        fi
        ;;

    *)
        error "不支持的数据库类型: $DB_CONNECTION"
        exit 1
        ;;
esac

# ---------------------------------------------------------------------------
# 压缩备份文件
# ---------------------------------------------------------------------------
info "压缩备份文件"
gzip -f "$BACKUP_FILE"
BACKUP_FILE="${BACKUP_FILE}.gz"
# PostgreSQL custom format 也压缩
if [[ -f "${BACKUP_FILE%.gz}.dump" ]]; then
    gzip -f "${BACKUP_FILE%.gz}.dump"
fi
info "备份完成: $BACKUP_FILE ($(du -h "$BACKUP_FILE" | cut -f1))"

# ---------------------------------------------------------------------------
# 清理过期备份
# ---------------------------------------------------------------------------
info "清理 ${KEEP_DAYS} 天前的旧备份"
DELETED_COUNT=$(find "$BACKUP_DIR" -name "blog_*.gz" -o -name "blog_*.sqlite" -mtime +$KEEP_DAYS -print -delete 2>/dev/null | wc -l)
if [[ $DELETED_COUNT -gt 0 ]]; then
    info "已删除 $DELETED_COUNT 个旧备份"
else
    info "无过期备份"
fi

# ---------------------------------------------------------------------------
# 同步备份到远程（可选，需配置）
# ---------------------------------------------------------------------------
# 如果配置了远程备份目录，可取消注释启用
# REMOTE_BACKUP_DIR="${BACKUP_REMOTE_DIR:-}"
# if [[ -n "$REMOTE_BACKUP_DIR" ]]; then
#     info "同步备份到远程: $REMOTE_BACKUP_DIR"
#     rsync -az "$BACKUP_DIR/" "$REMOTE_BACKUP_DIR/"
# fi

echo ""
info "备份完成！"
echo "备份位置: $PROJECT_DIR/$BACKUP_DIR"
echo ""
