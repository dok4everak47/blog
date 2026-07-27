<p align="center">
  <picture>
    <source media="(prefers-color-scheme: light)" srcset="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </picture>
</p>

<h3 align="center">📝 个人博客系统</h3>

<p align="center">
  基于 Laravel 13 构建，支持 Markdown 写作、分类标签、暗黑模式、SEO、RSS
</p>

---

## 功能特性

| 模块 | 说明 |
|------|------|
| 📝 **Markdown 写作** | 全功能 Markdown 编辑器，支持实时预览 |
| 🏷️ **分类 / 标签** | 多级分类 + 标签系统，文章归属清晰 |
| 💬 **评论系统** | 基于数据库的评论区 |
| 🌙 **暗黑模式** | Tailwind CSS 暗色主题，自动跟随系统 |
| 🔍 **搜索** | 站点全文搜索 |
| 📡 **RSS / Sitemap** | `/feed.xml` + `/sitemap.xml` |
| 🖼️ **图片裁剪** | 集成 Cropper.js，上传即裁剪 |
| ✨ **代码高亮** | Prism.js，支持 10+ 语言 |
| 🛡️ **CSP 安全头** | 自定义安全策略头部 |
| 🚀 **SEO** | 友好的元信息、结构化数据 |

## 技术栈

### 后端

| 类别 | 技术 | 版本 |
|------|------|------|
| 语言 | PHP | ^8.3 |
| 框架 | Laravel | ^13.8 |
| 数据库 | PostgreSQL / SQLite | — |
| 认证 | Laravel Breeze | — |

### 前端

| 类别 | 技术 | 版本 |
|------|------|------|
| CSS 框架 | Tailwind CSS | v4 |
| JS 框架 | Alpine.js | 3 |
| 构建工具 | Vite | 8 |
| Markdown | marked + DOMPurify | — |
| 代码高亮 | Prism.js | — |

### 数据模型

```
User ──1:N── Note ──N:M── Category
                 ├──N:M── Tag
                 └──1:N── Comment
```

## 快速开始

### Nix（推荐）

本项目使用 Nix flakes 管理开发环境：

```bash
# 进入全栈开发环境
nix develop

# 或仅前端 / 仅后端
nix develop .#frontend
nix develop .#backend
```

### 手动

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
```

### 开发

```bash
composer dev
# 同时启动: php artisan serve + queue:listen + pail + vite
```

## 项目结构

```
app/
├── Http/Controllers/    # Web + API 控制器
├── Http/Middleware/      # CSP 安全头等中间件
├── Models/               # 数据模型
│   ├── Note.php          # 文章
│   ├── Category.php      # 分类
│   ├── Tag.php           # 标签
│   ├── Comment.php       # 评论
│   └── SiteSetting.php   # 站点配置
├── Http/Requests/        # 表单验证
└── Policies/             # 授权策略

routes/
├── web.php               # 前台路由
├── auth.php              # 认证路由
└── console.php           # 计划任务

resources/
├── views/                # Blade 模板
├── css/app.css           # Tailwind 入口
└── js/                   # Alpine.js + 编辑器
```

## 构建

```bash
# 生产构建
npm run build

# 测试
composer test
```

## 部署

参考 [DEPLOYMENT.md](./docs/DEPLOYMENT.md)。

## 许可证

[MIT](./LICENSE)
