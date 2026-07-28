# Project Context

## 项目概述

个人博客系统，基于 Laravel 13 构建。支持 Markdown 写作、分类标签、暗黑模式、SEO、RSS。

## 技术栈

### 后端
- **PHP** 8.4（通过 Nix flake 统一版本）
- **Laravel** ^13.8
- **PostgreSQL**（生产）/ SQLite（开发 / 测试）
- **认证** Laravel Breeze

### 前端
- **Tailwind CSS** v4
- **Alpine.js** 3
- **Vite** 8
- **marked** + **DOMPurify**（Markdown 渲染 + XSS 净化）
- **Prism.js**（代码高亮）
- **Cropper.js**（图片裁剪）

### 开发环境
- **Nix flakes** — `nix develop` 进入统一环境
- **direnv** — 进目录自动加载 Nix shell
- **ClashX** — 7890 端口代理（GFW）

## 数据模型

```
User ──1:N── Note ──N:M── Category
                 ├──N:M── Tag
                 └──1:N── Comment
```

### 关键模型字段
- **Note**: title, content, slug, excerpt, status (draft/published), category_id, user_id, cover_image, thumbnail_url, published_at, views
- **Category**: name, slug
- **Tag**: name, slug
- **Comment**: content, note_id, user_id
- **SiteSetting**: key, value

## 路由结构

| 路径 | 说明 |
|------|------|
| `/` | 首页 |
| `/notes` | 文章列表 |
| `/notes/{note}` | 文章详情 |
| `/search?q=` | 全文搜索 |
| `/categories/{category}` | 分类页 |
| `/tags/{tag}` | 标签页 |
| `/feed.xml` | RSS |
| `/sitemap.xml` | Sitemap |
| `/dashboard` | 后台（需登录） |
| `/notes/create` | 写文章 |
| `/login`、`/register` | 认证 |

## 测试

```bash
# 本地跑测试（必须带 APP_ENV=testing，否则 CSRF 不过）
APP_ENV=testing php artisan test

# 跑完 43 个测试，~1.5s
```

- **PHPUnit** 43 个测试，101 个断言
- SQLite `:memory:` 数据库，`RefreshDatabase` trait
- **CI**：GitHub Actions 每次推送自动跑

## 常见问题

### CSRF bypass in tests
Laravel 13 测试环境中 CSRF 不会自动关闭。通过 `bootstrap/app.php` 中的 `getenv('APP_ENV') === 'testing'` 条件绕过。**必须**在 OS 级别传 `APP_ENV=testing`（`phpunit.xml` 的 `<env>` 不保证调 `putenv()`）。

### Nix devShell
- `nix develop` — 全栈环境（PHP + Node + PostgreSQL）
- `nix develop .#frontend` — 仅前端（Node）
- `nix develop .#backend` — 仅后端（PHP + Composer）

### PHP 版本
composer.lock 依赖 Symfony 8.x，需要 **PHP ≥8.4**。flake 已锁定 `php84`。
