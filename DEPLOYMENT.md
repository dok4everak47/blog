# 部署指南

> Laravel 13 Blog 部署文档
> 适用环境：Ubuntu 24.04 LTS + Nginx + PHP 8.4

---

## 一、环境要求

| 组件 | 版本 | 说明 |
|------|------|------|
| PHP | 8.4+ | 需安装扩展：fpm, cli, mbstring, xml, gd, pgsql, zip, curl, bcmath, intl, opcache |
| Composer | 2.x | PHP 依赖管理 |
| Node.js | 22+ | 前端构建 |
| npm | 10+ | 随 Node.js 安装 |
| Nginx | 1.20+ | Web 服务器 |
| PostgreSQL | 15+ | 生产数据库 |
| Redis | 6+ | 缓存（可选） |

### 安装依赖（Ubuntu 24.04）

```bash
# 推荐：直接使用项目自带的一键初始化脚本
sudo bash deploy/server-setup.sh
```

脚本会自动安装 PHP 8.4 + 扩展 / Composer / Node.js 22 / PostgreSQL / Redis / Nginx / Supervisor，并创建数据库用户。

---

## 二、首次部署

### 1. 克隆代码

```bash
sudo mkdir -p /var/www/blog
sudo chown $USER:$USER /var/www/blog
git clone <你的仓库地址> /var/www/blog
cd /var/www/blog
```

### 2. 配置环境变量

```bash
bash deploy/deploy.sh init
```

脚本会自动从 `.env.example` 复制 `.env` 并生成 `APP_KEY`。**然后必须手动编辑 `.env`**：

```bash
nano .env
```

**生产环境必改的配置项：**

```env
APP_NAME=你的博客名称
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# 数据库（PostgreSQL）
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=blog
DB_USERNAME=dok4ever
DB_PASSWORD=你的强密码

# 邮件（必须配置，否则注册/找回密码功能失效）
MAIL_MAILER=smtp
MAIL_HOST=smtp.qq.com
MAIL_PORT=465
MAIL_USERNAME=your-email@qq.com
MAIL_PASSWORD=你的SMTP授权码
MAIL_FROM_ADDRESS=your-email@qq.com
MAIL_FROM_NAME="${APP_NAME}"

# 是否开放注册（默认关闭，博客通常不需要）
# ALLOW_REGISTRATION=true
```

### 3. 数据库（已由 server-setup.sh 自动创建）

如果使用 `server-setup.sh`，数据库和用户已自动创建。密码保存在 `/root/db-password.txt`。

### 4. 执行部署

```bash
bash deploy/deploy.sh init
```

脚本会自动完成：
- 检查 `.env` 配置
- 生成 `APP_KEY`
- `composer install --no-dev`
- `npm install && npm run build`
- `php artisan migrate --force`
- `php artisan db:seed --force`（创建管理员账号）
- `php artisan storage:link`
- 缓存优化（config / route / view / event）
- 权限设置

### 5. 创建管理员账号

部署脚本会运行 seeder 创建默认管理员：

- 邮箱：`admin@blog.com`
- 密码：`password`

**上线后立即修改密码！**

也可以手动创建管理员：

```bash
php artisan tinker
```

```php
$user = new App\Models\User();
$user->name = '你的名字';
$user->email = '你的邮箱';
$user->password = bcrypt('你的密码');
$user->is_admin = true;
$user->save();
```

### 6. 配置 Nginx

```bash
sudo cp deploy/nginx.conf.example /etc/nginx/sites-available/blog.conf
sudo nano /etc/nginx/sites-available/blog.conf
# 修改 server_name 和 root 路径
sudo ln -s /etc/nginx/sites-available/blog.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 7. 申请 SSL 证书

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
```

Certbot 会自动修改 Nginx 配置并开启 HTTPS。

### 8. 启动队列 Worker

```bash
sudo cp deploy/queue-worker.conf.example /etc/supervisor/conf.d/blog-worker.conf
sudo nano /etc/supervisor/conf.d/blog-worker.conf
# 确认路径和 user 正确
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start blog-worker:*
sudo supervisorctl status
```

### 9. 配置定时备份

```bash
crontab -e
```

添加以下内容：

```cron
# 每天凌晨 3 点备份数据库
0 3 * * * cd /var/www/blog && bash deploy/backup.sh >> storage/logs/backup.log 2>&1

# Laravel 调度器（每分钟执行）
* * * * * cd /var/www/blog && php artisan schedule:run >> /dev/null 2>&1
```

---

## 三、日常更新

代码推送到 Git 后，在服务器执行：

```bash
cd /var/www/blog
bash deploy/deploy.sh
```

脚本会自动进入维护模式 → 拉取代码 → 安装依赖 → 迁移 → 缓存优化 → 退出维护模式。

---

## 四、常用运维命令

```bash
# 查看应用日志
tail -f storage/logs/laravel.log

# 查看队列状态
php artisan queue:status
sudo supervisorctl status

# 查看失败任务
php artisan queue:failed

# 重试失败任务
php artisan queue:retry all

# 清除缓存
php artisan optimize:clear

# 进入维护模式
php artisan down

# 退出维护模式
php artisan up

# 手动备份
bash deploy/backup.sh

# 重启队列 worker（代码更新后）
php artisan queue:restart
```

---

## 五、常见问题

### Q1: 图片上传后显示 404

**原因**：没有创建 storage 软链接。

**解决**：`php artisan storage:link`

### Q2: 页面没有样式

**原因**：前端资源没有构建。

**解决**：`npm install && npm run build`

### Q3: 注册/找回密码邮件不发送

**原因**：`.env` 中 `MAIL_MAILER=log`，邮件只写日志不真发。

**解决**：配置真实的 SMTP 服务器。

### Q4: 502 Bad Gateway

**原因**：PHP-FPM 没启动或 socket 路径不对。

**解决**：
```bash
sudo systemctl status php8.4-fpm
# 检查 Nginx 配置中的 fastcgi_pass 路径
ls /run/php/
```

### Q5: 权限错误（storage 无法写入）

**解决**：
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Q6: 想开放用户注册

在 `.env` 中添加：

```env
ALLOW_REGISTRATION=true
```

然后 `php artisan config:cache`。

---

## 六、生产环境 Checklist

部署上线前逐项确认：

- [ ] `.env` 中 `APP_ENV=production`
- [ ] `.env` 中 `APP_DEBUG=false`
- [ ] `APP_KEY` 已生成
- [ ] `APP_URL` 指向真实域名
- [ ] 数据库使用 PostgreSQL（生产推荐）
- [ ] `php artisan storage:link` 已执行
- [ ] `npm run build` 已执行
- [ ] SSL 证书已配置（HTTPS）
- [ ] 邮件 SMTP 已配置
- [ ] Supervisor 队列 worker 在运行
- [ ] 定时备份已配置
- [ ] 管理员密码已修改
- [ ] 公开注册已关闭（默认）
- [ ] `php artisan config:cache` 已执行
- [ ] `php artisan route:cache` 已执行
