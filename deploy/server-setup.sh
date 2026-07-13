#!/bin/bash
#
# 服务器环境初始化脚本 — 在 Ubuntu 22.04/24.04 或 Debian 11/12 上安装运行 Laravel Blog 所需的全部依赖
#
# 用法：
#   chmod +x deploy/server-setup.sh
#   sudo ./deploy/server-setup.sh
#
# 装完后再执行 deploy/deploy.sh 部署应用代码
#
set -e

# ---------------------------------------------------------------------------
# 配置项（按需修改）
# ---------------------------------------------------------------------------
APP_DB_NAME="blog"
APP_DB_USER="dok4ever"
# 生成随机密码；如需指定，改成 APP_DB_PASS="你的密码"
APP_DB_PASS=$(openssl rand -base64 24)

# ---------------------------------------------------------------------------
# 检测系统发行版
# ---------------------------------------------------------------------------
if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS_ID=$ID
    OS_CODENAME=$VERSION_CODENAME
else
    echo "❌ 无法检测系统发行版，请手动确认系统为 Ubuntu 或 Debian"
    exit 1
fi

echo "=========================================="
echo " Laravel Blog 服务器环境初始化"
echo " 系统: ${OS_ID} ${OS_CODENAME}"
echo "=========================================="
echo ""
echo "⚠️  数据库密码将在初始化后写入 /root/db-password.txt"
echo ""
read -p "按回车继续，或 Ctrl+C 取消..."

# ---------------------------------------------------------------------------
# 1. PHP 8.4 + 扩展
# ---------------------------------------------------------------------------
# 安装 add-apt-repository（Debian 需要额外装 software-properties-common）
sudo apt update
sudo apt install -y ca-certificates lsb-release curl gnupg2 unzip git

if [ "$OS_ID" = "ubuntu" ]; then
    echo ">>> [Ubuntu] 添加 ondrej PHP PPA..."
    sudo apt install -y software-properties-common
    sudo add-apt-repository ppa:ondrej/php -y
    sudo apt update
elif [ "$OS_ID" = "debian" ]; then
    echo ">>> [Debian] 添加 SURY PHP 仓库..."
    sudo curl -sSLo /usr/share/keyrings/debsuryorg-archive-keyring.gpg https://packages.sury.org/debsuryorg-archive-keyring.gpg
    echo "deb [signed-by=/usr/share/keyrings/debsuryorg-archive-keyring.gpg] https://packages.sury.org/debian/ ${OS_CODENAME} main" | sudo tee /etc/apt/sources.list.d/sury-php.list
    sudo apt update
else
    echo "❌ 不支持的发行版: $OS_ID（仅支持 Ubuntu / Debian）"
    exit 1
fi

echo ">>> 安装 PHP 8.4 及扩展..."
sudo apt install -y \
    php8.4-fpm \
    php8.4-cli \
    php8.4-mbstring \
    php8.4-xml \
    php8.4-gd \
    php8.4-pgsql \
    php8.4-zip \
    php8.4-curl \
    php8.4-bcmath \
    php8.4-intl \
    php8.4-opcache \
    php8.4-dev \
    unzip \
    git

# ---------------------------------------------------------------------------
# 2. Redis PHP 扩展（PECL 编译安装，ondrej PPA 不提供 php8.4-redis 包）
# ---------------------------------------------------------------------------
echo ">>> 安装 Redis PHP 扩展..."
sudo pecl install redis
echo "extension=redis.so" | sudo tee /etc/php/8.4/mods-available/redis.ini
sudo phpenmod -v 8.4 redis

# php8.4-dev 只是编译依赖，装完 redis 后可卸载
sudo apt remove -y php8.4-dev
sudo apt autoremove -y

# ---------------------------------------------------------------------------
# 3. Composer
# ---------------------------------------------------------------------------
echo ">>> 安装 Composer..."
if ! command -v composer &>/dev/null; then
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
fi

# ---------------------------------------------------------------------------
# 4. Node.js 22 LTS
# ---------------------------------------------------------------------------
echo ">>> 安装 Node.js 22 LTS..."
if ! command -v node &>/dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
    sudo apt install -y nodejs
fi

# ---------------------------------------------------------------------------
# 5. PostgreSQL
# ---------------------------------------------------------------------------
echo ">>> 安装 PostgreSQL..."
sudo apt install -y postgresql postgresql-contrib

echo ">>> 创建数据库和用户..."
sudo -u postgres psql -c "CREATE USER ${APP_DB_USER} WITH PASSWORD '${APP_DB_PASS}';" 2>/dev/null || \
    echo "用户 ${APP_DB_USER} 已存在，跳过"
sudo -u postgres psql -c "CREATE DATABASE ${APP_DB_NAME} OWNER ${APP_DB_USER};" 2>/dev/null || \
    echo "数据库 ${APP_DB_NAME} 已存在，跳过"
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE ${APP_DB_NAME} TO ${APP_DB_USER};"

# ---------------------------------------------------------------------------
# 6. Redis
# ---------------------------------------------------------------------------
echo ">>> 安装 Redis..."
sudo apt install -y redis-server

# ---------------------------------------------------------------------------
# 7. Nginx
# ---------------------------------------------------------------------------
echo ">>> 安装 Nginx..."
sudo apt install -y nginx

# ---------------------------------------------------------------------------
# 8. Supervisor
# ---------------------------------------------------------------------------
echo ">>> 安装 Supervisor..."
sudo apt install -y supervisor

# ---------------------------------------------------------------------------
# 9. 启动 + 开机自启
# ---------------------------------------------------------------------------
echo ">>> 启动服务并设置开机自启..."
sudo systemctl enable --now php8.4-fpm
sudo systemctl enable --now nginx
sudo systemctl enable --now postgresql
sudo systemctl enable --now redis-server
sudo systemctl enable --now supervisor

# ---------------------------------------------------------------------------
# 10. 保存数据库密码
# ---------------------------------------------------------------------------
echo ">>> 保存数据库密码到 /root/db-password.txt..."
cat > /root/db-password.txt << DBPASS
数据库连接信息（填入 .env）：
  DB_CONNECTION=pgsql
  DB_HOST=127.0.0.1
  DB_PORT=5432
  DB_DATABASE=${APP_DB_NAME}
  DB_USERNAME=${APP_DB_USER}
  DB_PASSWORD=${APP_DB_PASS}
DBPASS
chmod 600 /root/db-password.txt

# ---------------------------------------------------------------------------
# 11. 验证
# ---------------------------------------------------------------------------
echo ""
echo "=========================================="
echo " ✅ 环境初始化完成"
echo "=========================================="
echo ""
echo "版本信息："
php -v | head -1
composer --version 2>/dev/null | head -1
node --version
psql --version 2>/dev/null
redis-server --version 2>/dev/null | head -1
nginx -v 2>&1
echo ""
echo "下一步："
echo "  1. 克隆项目代码到 /var/www/blog"
echo "  2. 查看数据库密码：cat /root/db-password.txt"
echo "  3. 在 .env 中填入数据库密码"
echo "  4. 执行 deploy/deploy.sh init 部署应用"
