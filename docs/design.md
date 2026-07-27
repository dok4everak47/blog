# Blog 项目设计规范

> **版本**：v1.0 | **更新日期**：2026-07-13 | **基于**：Laravel 13.8

---

## 目录

1. [项目概览](#1-项目概览)
2. [技术栈](#2-技术栈)
3. [架构分层](#3-架构分层)
4. [数据库设计](#4-数据库设计)
5. [路由设计](#5-路由设计)
6. [控制器规范](#6-控制器规范)
7. [模型规范](#7-模型规范)
8. [视图与组件规范](#8-视图与组件规范)
9. [CSS 设计体系](#9-css-设计体系)
10. [JavaScript 规范](#10-javascript-规范)
11. [安全设计](#11-安全设计)
12. [测试策略](#12-测试策略)
13. [命名约定速查](#13-命名约定速查)
14. [部署清单](#14-部署清单)

---

## 1. 项目概览

这是一个个人博客系统，支持 Markdown 写作、分类标签管理、评论系统、暗黑模式、SEO、RSS/Sitemap 等完整博客功能。

- **目标用户**：个人博主（单作者，预留多作者扩展）
- **设计风格**：「莫兰迪」暖调配色 + 极简卡片式布局 + 专注写作体验
- **设计参考**：Medium（编辑器）/ Notion（工具栏）/ GitHub（代码块）/ ryqi.top（Hero 下拉效果）

---

## 2. 技术栈

### 后端
| 类别 | 技术 | 版本 |
|---|---|---|
| 语言 | PHP | 8.3+ |
| 框架 | Laravel | 13.8 |
| 数据库 | SQLite（开发）/ MySQL（生产） | — |
| 认证 | Laravel Breeze | — |
| 缓存 | database 驱动（开发）/ Redis（生产推荐） | — |

### 前端
| 类别 | 技术 | 版本 |
|---|---|---|
| CSS 框架 | Tailwind CSS | v4 |
| JS 框架 | Alpine.js | 3 |
| 构建工具 | Vite | 8 |
| Markdown 解析 | marked | 本地 npm 包 |
| XSS 净化 | DOMPurify | 本地 npm 包 |
| 代码高亮 | Prism.js | 本地 npm 包 |

### 关键依赖
| 包名 | 用途 |
|---|---|
| `marked` | Markdown → HTML 转换 |
| `dompurify` | HTML XSS 净化 |
| `alpinejs` | 声明式前端交互 |
| `prismjs` | 代码块语法高亮（10 语言） |
| `tailwindcss` | 原子化 CSS 框架 |

---

## 3. 架构分层

```
┌────────────────────────────────┐
│         Blade Views            │  ← 视图层（layout + page + component）
├────────────────────────────────┤
│         Controllers            │  ← 控制层（请求分发 / 视图组装）
├────────────────────────────────┤
│      Form Requests / Enum      │  ← 验证层 + 枚举
├────────────────────────────────┤
│    Models + Policies           │  ← 业务模型 + 授权策略
├────────────────────────────────┤
│     Migration + Seeder         │  ← 数据层
├────────────────────────────────┤
│  Middleware / Service Provider │  ← 横切层
└────────────────────────────────┘
```

### 核心设计原则

1. **瘦控制器**：复杂逻辑下沉到 Model / Service / Form Request
2. **Policy 授权**：所有写操作必须经 `$this->authorize()` 调用 Policy
3. **Form Request**：复杂验证独立为 FormRequest 类
4. **Enum 替代裸字符串**：状态等枚举值用 PHP 8.1 Backed Enum
5. **组件化**：复用 UI 片段抽取为 Blade 组件（`<x-nav />`、`<x-footer />` 等）

---

## 4. 数据库设计

### 4.1 表结构总览

```
users ──1:N──> notes ──N:N──> tags  (note_tag 中间表)
  │              │
  │              └──1:N──> comments  (自引用嵌套 1 层)
  │
  └── N:1 (隐式) categories <──1:N── notes

site_settings (键值对结构化存储)
```

### 4.2 notes 表（核心）

| 列名 | 类型 | 约束 | 说明 |
|---|---|---|---|
| `id` | bigint PK | auto | |
| `user_id` | bigint FK | →users.id CASCADE | 作者（安全关键） |
| `category_id` | bigint FK nullable | →categories.id SET NULL | 分类（删分类不删文章） |
| `title` | varchar 255 | NOT NULL | 文章标题 |
| `slug` | varchar 255 unique nullable | | URL 路径（生成但默认暂不用） |
| `excerpt` | text nullable | | 摘要（独立字段，手动优先自动） |
| `content` | text | NOT NULL | Markdown 正文 |
| `cover_image` | varchar nullable | | 封面图路径（相对 `storage/`） |
| `thumbnail_url` | varchar nullable | | 缩略图路径（400px GD 生成） |
| `status` | varchar | DEFAULT 'published' | 状态枚举：draft/published/archived |
| `created_at` / `updated_at` | timestamp | | Laravel 标准时间戳 |

### 4.3 索引策略

```sql
-- notes 表
INDEX idx_notes_status (status)
INDEX idx_notes_published (status, created_at)       -- 列表/首页核心查询
INDEX idx_notes_user (user_id)                        -- 作者过滤
INDEX idx_notes_category (category_id)                -- 分类页

-- note_tag 中间表
UNIQUE (note_id, tag_id)                              -- 防重复关联

-- 其他
UNIQUE tags(name)                                     -- 标签名唯一
UNIQUE categories(name)                               -- 分类名唯一
UNIQUE site_settings(key)                             -- 站点设置键唯一
```

### 4.4 迁移文件清单（21 个）

```
0001_01_01_000000_create_users_table
0001_01_01_000001_create_cache_table
0001_01_01_000002_create_jobs_table
2026_07_09_165542_create_notes_table
2026_07_10_024517_add_title_and_to_notes_table
2026_07_10_073445_create_tags_table
2026_07_10_074353_create_note_tag_table
2026_07_10_075917_add_tag_id_to_note_tag_table
2026_07_10_090855_create_categories_table
2026_07_10_090918_add_category_id_to_notes_table
2026_07_12_000001_add_status_and_slug_to_notes_table
2026_07_12_001000_add_cover_image_to_notes_table
2026_07_12_002000_add_unique_indexes            ← 唯一索引补全
2026_07_12_010000_add_user_id_to_notes_table     ← 安全地基
2026_07_12_093411_create_site_settings_table
2026_07_12_104955_create_comments_table
2026_07_12_105443_add_excerpt_to_notes_table
2026_07_12_105652_add_thumbnail_url_to_notes_table
2026_07_12_132621_add_is_admin_to_users_table    ← 管理员角色
2026_07_12_134717_add_indexes_to_notes_table     ← 复合索引
2026_07_12_134916_change_category_id_on_delete_to_set_null
```

---

## 5. 路由设计

### 5.1 路由分组策略

```php
// routes/web.php — 三层路由结构

// ── 公开区（无需登录）────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
Route::get('/notes/{note}', [NoteController::class, 'show'])->name('notes.show');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/tags/{tag}', [TagController::class, 'show'])->name('tags.show');
Route::get('/rss', [FeedController::class, 'rss'])->name('rss');
Route::get('/sitemap.xml', [FeedController::class, 'sitemap'])->name('sitemap');

// ── 需登录区 ────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {
    // 仪表盘
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 文章管理
    Route::get('/notes/create', [NoteController::class, 'create'])->name('notes.create');
    Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
    Route::get('/notes/{note}/edit', [NoteController::class, 'edit'])->name('notes.edit');
    Route::put('/notes/{note}', [NoteController::class, 'update'])->name('notes.update');
    Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');
    Route::post('/notes/autosave', [NoteController::class, 'autosave'])->name('notes.autosave')
        ->middleware('throttle:30,1');
    Route::post('/notes/upload-image', [NoteController::class, 'uploadImage'])
        ->middleware('throttle:30,1');

    // 分类内联创建
    Route::post('/categories', [CategoryController::class, 'store'])
        ->middleware('admin');

    // 管理员：站点设置
    Route::post('/settings/hero-image', [DashboardController::class, 'updateHeroImage'])
        ->middleware('admin')->middleware('throttle:30,1');

    // 评论
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store')
        ->middleware('throttle:10,1');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])
        ->name('comments.destroy');

    // Breeze 自带 Profile 路由
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Breeze 认证路由（自动注册）
require __DIR__.'/auth.php';
```

### 5.2 路由设计原则

1. **Restful**：资源路由用标准 HTTP 动词（GET/POST/PUT/DELETE）
2. **路由模型绑定**：所有 `{note}`/`{category}`/`{tag}`/`{comment}` 走隐式绑定
3. **路由名语义化**：`notes.index`、`notes.create`、`notes.show` 等
4. **whereNumber 约束**：`{note}` 路由在 `notes.create` 等之前定义以避免参数冲突
5. **限流分层**：
   - 注册：`throttle:5,1`（5次/分钟）
   - 评论：`throttle:10,1`（10次/分钟）
   - 自动保存/图片上传/封面设置：`throttle:30,1`（30次/分钟）
6. **管理员路由**：站点设置 + 分类创建需 `admin` 中间件

---

## 6. 控制器规范

### 6.1 控制器目录与职责

| 控制器 | 文件 | 职责 |
|---|---|---|
| `HomeController` | `app/Http/Controllers/HomeController.php` | 首页 Hero/文章列表；关于页；联系页 |
| `DashboardController` | `app/Http/Controllers/DashboardController.php` | 仪表盘统计+列表；Hero 图/About 内容管理 |
| `NoteController` | `app/Http/Controllers/NoteController.php` | 文章 CRUD + 自动保存 + 图片上传 + 封面更新 |
| `CategoryController` | `app/Http/Controllers/CategoryController.php` | 分类页列表 + 内联快速创建（JSON 接口） |
| `TagController` | `app/Http/Controllers/TagController.php` | 标签页列表 + 内联快速创建（JSON 接口） |
| `SearchController` | `app/Http/Controllers/SearchController.php` | 全文搜索（标题+正文模糊匹配） |
| `CommentController` | `app/Http/Controllers/CommentController.php` | 评论创建 + 删除 |
| `FeedController` | `app/Http/Controllers/FeedController.php` | RSS 2.0 + Sitemap XML |
| `ProfileController` | `app/Http/Controllers/ProfileController.php` | Breeze 自带，用户 Profile 编辑 |

### 6.2 控制器方法命名

```
index()   → 列表页（公开）
show()    → 详情页（公开）
create()  → 创建表单（登录）
store()   → 执行创建（登录）
edit()    → 编辑表单（登录）
update()  → 执行更新（登录）
destroy() → 执行删除（登录）

// 特殊操作
autosave()        → JSON 自动保存接口
uploadImage()     → JSON 图片上传接口
updateCover()     → JSON 封面更换接口
updateHeroImage() → JSON Hero 背景图更新接口
updateAboutContent() → JSON About 内容更新接口
```

### 6.3 控制器编写规则

1. **必须调用 Policy**：所有修改操作用 `$this->authorize('action', $model)`
2. **用户归属**：`NoteController` 创建时走 `auth()->user()->notes()->create()` 而非设置 `user_id`
3. **JSON 接口**：自动保存 / 图片上传 / 封面更新用 `response()->json()`
4. **显式返回类型**：所有方法声明 `: View` / `: RedirectResponse` / `: JsonResponse`
5. **草稿过滤**：公开查询用 `->published()` scope
6. **N+1 预防**：列表查询用 `->with('tags', 'category')` 预加载
7. **limit 20 主题禁止**：不使用 `this->authorizeResource` 的批量授权快捷方式，逐方法显式调用 `$this->authorize()`

---

## 7. 模型规范

### 7.1 模型列表

| 模型 | 表 | 关键设计 |
|---|---|---|
| `User` | `users` | `is_admin` boolean cast；`notes()` hasMany |
| `Note` | `notes` | `status` Enum cast；`user_id` 不进 fillable；多个 scope |
| `Category` | `categories` | `notes()` hasMany |
| `Tag` | `tags` | `notes()` belongsToMany |
| `Comment` | `comments` | `parent_id` 自引用；`replies()` hasMany |
| `SiteSetting` | `site_settings` | 键值存储 + 缓存读取 |

### 7.2 Note 模型设计（核心模型）

```php
// casts
protected function casts(): array
{
    return [
        'status' => NoteStatus::class,   // PHP 8.1 Backed Enum
    ];
}

// fillable / guarded
protected $fillable = [
    'title', 'content', 'slug', 'status', 'excerpt',
    'category_id', 'cover_image', 'thumbnail_url',
];
// user_id 不在 fillable 中 — 只能通过 auth()->user()->notes()->create() 赋值

// $with 预加载
protected $with = ['category', 'tags'];  // 减少 N+1

// scopes
scopePublished()  // where status = published
scopeDraft()      // where status = draft
scopeForUser($u)  // where user_id = $u->id

// accessors（注意用 $this->attributes[] 避免无限递归）
getCoverImageUrlAttribute()
getThumbnailUrlAttribute()
```

### 7.3 Model 编写规则

1. **fillable vs guarded**：优先 `$fillable` 白名单（不用 `$guarded`）
2. **user_id 不进 fillable**：安全关键 — 防止表单伪造文章归属
3. **accessor 与列同名**：用 `$this->attributes['col']` 取值，避免无限递归
4. **显式 $with**：常用关联预加载写在模型类上
5. **Enum cast**：状态字段用 PHP 8.1 Backed Enum + Laravel 原生 cast
6. **HasFactory**：所有模型引入 `HasFactory` trait
7. **外键声明**：`belongsTo` / `hasMany` 明确指定 foreign key（不依赖约定默认）
8. **静态辅助方法**：`Note::generateExcerpt()`、`Note::generateThumbnail()` 等

### 7.4 Enum 定义

```php
// app/Enums/NoteStatus.php
enum NoteStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string { ... }
    public function isPublic(): bool { ... }
}
```

---

## 8. 视图与组件规范

### 8.1 Blade 布局架构

```
资源文件
├── layouts/
│   ├── blog.blade.php           ← 统一公开布局（所有面向前端页面）
│   └── app.blade.php            ← 统一后台布局（Dashboard/Profile）
│
├── components/
│   ├── nav.blade.php            ← 全局导航（公开+登录双态）
│   ├── footer.blade.php         ← 全局页脚
│   ├── markdown-toolbar.blade.php ← Markdown 编辑工具栏（14 按钮）
│   ├── image-insert-modal.blade.php ← 插入图片弹窗
│   └── article-editor-script.blade.php ← 编辑器 Alpine.js 组件
│
├── home.blade.php               ← 首页（Hero + 文章列表）
├── notes/
│   ├── index.blade.php          ← 文章列表（分页卡片）
│   ├── show.blade.php           ← 文章详情（封面+正文+评论+TOC）
│   ├── create.blade.php         ← 写文章（编辑器）
│   └── edit.blade.php           ← 编辑文章（编辑器）
├── categories/show.blade.php    ← 分类页
├── tags/show.blade.php          ← 标签页
├── search.blade.php             ← 搜索结果页
├── contact.blade.php            ← 联系页
├── profile.blade.php            ← 关于页
├── dashboard.blade.php          ← 仪表盘（站点设置+文章管理）
├── profile/edit.blade.php       ← 个人资料编辑
├── errors/
│   ├── 404.blade.php
│   ├── 500.blade.php
│   └── 503.blade.php
└── feeds/
    ├── rss.blade.php            ← RSS XML 模板
    └── sitemap.blade.php        ← Sitemap XML 模板
```

### 8.2 布局组件层级

```
blog.blade.php（公开页统一父布局）
  ├── <head> vite/app.css/app.js + @stack(scripts) + @yield(seo)
  ├── <x-nav />（全局导航栏）
  ├── <main class="flex-1"> @yield(content) </main>
  └── <x-footer />（全局页脚）

app.blade.php（后台页统一父布局）
  ├── <head> 同上
  ├── <x-nav />（后台也共享导航）
  ├── <main> @yield(content) </main>
  └── <x-footer />
```

### 8.3 Blade 编写规则

1. **统一用 `@extends('layouts.blog')`**（公开页）或 `@extends('layouts.app')`（后台页）
2. **每页设 `@section('title')`** — 用于 `<title>` 标签
3. **每页设 `@section('seo')`** — 公开页的 OG/Twitter/meta description
4. **`@section('content')` 包裹主体内容**
5. **`@stack('scripts')` 用于页面级 JS**
6. **组件通信**：Blade 组件内用 Alpine.js `x-data` + `data-*` 属性传参
7. **表单**：都用 `@csrf` + `@method`，文件上传加 `enctype="multipart/form-data"`
8. **XSS 防护**：展示层：
   - 用户 HTML → `Str::purify()` 或 `DOMPurify.sanitize()`
   - Markdown → `Str::markdown($str, ['html_input' => 'strip', 'allow_unsafe_links' => false])`
   - 前端预览 → `DOMPurify.sanitize(marked.parse(raw))`

---

## 9. CSS 设计体系

### 9.1 莫兰迪暖调色板

```css
@theme {
    /* 亮色主题 */
    --color-bg:             #FDFCFA    /* 页面背景（米白） */
    --color-surface:        #F9F8F6    /* 卡片/区域底色 */
    --color-surface-2:      #FFFFFF    /* 纯白（编辑器/弹窗） */
    --color-border:         #EDEAE5    /* 细边框 */
    --color-border-strong:  #E0DCD6    /* 强调边框 */
    --color-text:           #2C2A28    /* 正文 */
    --color-text-secondary: #8C8884    /* 辅助文字 */
    --color-text-muted:     #A9A6A1    /* 弱化文字 */
    --color-primary:        #D88C8C    /* 品牌色（莫兰迪粉） */
    --color-primary-hover:  #C77A7A    /* 品牌悬停 */
    --color-primary-light:  #FAF0F0    /* 品牌浅底 */
    --color-sage:           #9BB5A4    /* 鼠尾草绿 */
    --color-sage-hover:     #8AA592
    --color-sage-light:     #EEF3F0
    --color-gold:           #C9A66B    /* 金棕 */
    --color-gold-hover:     #B8955A
    --color-gold-light:     #F5F0E8
}
```

### 9.2 CSS 文件组织

```
resources/css/
├── app.css              ← 主入口：@import tailwindcss + 自定义 class
├── dark-mode.css        ← 暗黑模式变量覆盖（独立文件避免 Tailwind v4 剥离）
└── prism.css            ← Prism.js Catppuccin 主题（亮+暗双态）
```

### 9.3 CSS 编写规则

1. **优先 Tailwind utility**：布局/间距/排版用 Tailwind class
2. **自定义 class 命名**：kebab-case，语义前缀（`card-`、`hero-`、`md-`、`btn-`）
3. **自定义 class 场景**：
   - 编辑器专属（`.card-editor` / `.title-input` / `.editor-textarea` / `.md-toolbar`）
   - Hero 区（`.hero-*`）
   - 封面操作（`.cover-dropzone` / `.cover-preview-img`）
   - 状态组件（`.spinner` / `.toast` / `.tag-chip`）
4. **暗黑模式**：
   - 策略：class-based（`.dark` on `<html>`）+ `prefers-color-scheme` fallback
   - 独立 `dark-mode.css` 文件（Tailwind v4 `@layer base` 内变量块会被剥离）
   - 亮色变量在 `@theme` 块，暗色覆盖在 `dark-mode.css` 的 `.dark {}` 选择器
5. **Vite 入口**：`app.css` 为主入口；`dark-mode.css` 通过 `@import` 纳入编译链
6. **勿在 Blade 内联大段 `<style>`** — 提取到 CSS 文件

---

## 10. JavaScript 规范

### 10.1 JS 文件组织

```
resources/js/
├── app.js       ← 主入口：Alpine.start() + Prism + 主题切换
└── editor.js    ← 编辑器依赖：marked + DOMPurify 本地打包
```

### 10.2 Alpine.js 使用模式

**全局状态组件**：不做嵌套 x-data，每页一个根组件。

```
页面内组件命名规则：
- articleEditor()      — 写文章/编辑文章页
- coverManager()       — Dashboard 封面管理
- heroImageManager()   — Dashboard Hero 图设置
- aboutForm()          — Dashboard About 编辑
```

**组件传参**：
```html
<!-- 用 data-* 属性传初始值 -->
<div x-data="articleEditor()"
     data-server-errors="{{ json_encode($errors->all()) }}"
     data-create-category-url="{{ route('categories.store') }}"
     data-upload-image-url="{{ route('notes.uploadImage') }}">
```

### 10.3 前端 JS 规则

1. **依赖本地化**：marked / DOMPurify 通过 npm + Vite 打包，**不用 CDN**
2. **XSS 双重防护**：前端 `DOMPurify.sanitize()` + 后端 `Str::markdown(html_input: 'strip')`
3. **自动保存防抖**：1.5 秒防抖，避免频繁请求
4. **未保存离开提示**：`window.addEventListener('beforeunload', ...)`
5. **Ctrl/Cmd+S**：保存草稿快捷键
6. **文件上传**：弹窗 modal 内的 `<input type=file>` 用 `x-ref` 引用 + 手动 `click()` 触发
7. **语言**：变量/函数用英文命名（驼峰），注释用中文

---

## 11. 安全设计

### 11.1 安全层次

```
Layer 1: 认证 (auth middleware)            → "你是谁"
Layer 2: 授权 (Policy + authorize())       → "你能做什么"
Layer 3: 输入验证 (Form Request)           → "数据格式正确"
Layer 4: 输出净化 (strip_tags + purify)    → "渲染内容安全"
Layer 5: 限流 (throttle middleware)        → "防滥用"
Layer 6: 安全头 (SecurityHeaders)          → "浏览器防护"
```

### 11.2 Policy 策略矩阵

| 操作 | NotePolicy | CommentPolicy |
|---|---|---|
| view | 已发布→所有人 / 草稿→仅作者 | — |
| create | 登录用户均可 | 登录用户均可 |
| update | 仅作者 | 仅评论作者 |
| delete | 仅作者 | 仅评论作者 |

### 11.3 安全配置清单

| 项 | 状态 | 说明 |
|---|---|---|
| `user_id` 不进 fillable | ✅ | 通过 `auth()->user()->notes()->create()` |
| Policy 逐方法调用 | ✅ | **不用** `authorizeResource`（会漏） |
| base Controller 含 `AuthorizesRequests` | ✅ | 否则 `$this->authorize()` 是死代码 |
| 草稿不公开 | ✅ | `scopePublished()` + Policy `view()` |
| XSS 双层防护 | ✅ | 前端 DOMPurify + 后端 `html_input: strip` |
| autosave 限流 | ✅ | `throttle:30,1` |
| 注册限流 | ✅ | `throttle:5,1` |
| 评论限流 | ✅ | `throttle:10,1` |
| admin 中间件 | ✅ | 站点设置分类创建 |
| SecurityHeaders 中间件 | ✅ | X-Frame-Options / X-Content-Type / Referrer-Policy |
| robots.txt | ✅ | 禁止后台路由索引 |
| APP_DEBUG | ✅ | 生产 `.env.production.example` 含 `false` |
| 路由 whereNumber | ✅ | 防 SQL 注入参数 |

---

## 12. 测试策略

### 12.1 测试结构

```
tests/
├── Feature/
│   ├── NoteAuthorizationTest.php   ← 授权/IDOR/草稿可见性/归属
│   ├── SearchTest.php              ← 搜索功能
│   └── UploadImageTest.php         ← 图片上传校验
└── Unit/ （Breeze 自带基础测试）
```

### 12.2 测试覆盖重点

- **授权测试**：未登录访问受保护路由 → 302/403；非作者改删他人文章 → 403
- **草稿隔离**：未登录/非作者访问草稿 → 403 Forbidden（非 404）
- **IDOR 防护**：直接 POST 修改他人文章 ID → 403
- **输入校验**：非法文件类型/超大文件/空必填字段
- **搜索功能**：标题匹配/正文匹配/草稿不出现

### 12.3 测试运行

```bash
php artisan test                    # 全部测试（当前 43 passed / 101 assertions）
php artisan test --filter=NoteAuth  # 按类名过滤
```

---

## 13. 命名约定速查

### PHP
| 实体 | 命名规则 | 示例 |
|---|---|---|
| Model | 单数 PascalCase | `Note`、`Category` |
| Controller | 单数 PascalCase + Controller | `NoteController` |
| Migration | `YYYY_MM_DD_HHmmss_描述.sql` | `2026_07_12_010000_add_user_id` |
| FormRequest | 动词 + Model + Request | `StoreNoteRequest` |
| Policy | Model + Policy | `NotePolicy` |
| Enum | 名词 PascalCase | `NoteStatus` |
| Middleware | 描述 + 动作 | `EnsureUserIsAdmin` |

### Blade
| 实体 | 命名规则 | 示例 |
|---|---|---|
| 布局 | `layouts/名字.blade.php` | `layouts/blog.blade.php` |
| 组件 | `components/名字.blade.php` | `components/nav.blade.php` |
| 页面 | 直接描述或 `model/动作.blade.php` | `home.blade.php`、`notes/create.blade.php` |

### CSS
| 实体 | 命名规则 | 示例 |
|---|---|---|
| 自定义 class | kebab-case + 语义前缀 | `.card-editor`、`.hero-title` |
| Tailwind theme token | `--color-语义` | `--color-primary` |

### 路由
| 实体 | 命名规则 | 示例 |
|---|---|---|
| 路由名 | `资源.动作` | `notes.index`、`notes.create` |

---

## 14. 部署清单

### 14.1 准备
- [ ] 复制 `.env.production.example` → `.env`，填入真实值
- [ ] `APP_ENV=production`，`APP_DEBUG=false`
- [ ] `APP_URL` 设真实域名
- [ ] 生成 `APP_KEY`：`php artisan key:generate`
- [ ] 改 `DB_*` 为 MySQL/PostgreSQL（生产不要用 SQLite）
- [ ] 改 `CACHE_STORE` 为 `redis`（生产不要用 database 缓存）
- [ ] 配置 `MAIL_*` 为真实 SMTP

### 14.2 部署步骤
```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 14.3 验证
- [ ] 首页加载正常
- [ ] 文章列表/详情正常
- [ ] 搜索功能正常
- [ ] RSS/Sitemap 可访问
- [ ] 暗黑模式切换正常
- [ ] 登录/注册流程正常
- [ ] robots.txt 含 Sitemap
- [ ] `APP_DEBUG=false`（访问不存在路由不暴露 trace）
- [ ] HTTPS 强制跳转

### 14.4 未完成待办
- [ ] 关联远程 Git 仓库 + 推送
- [ ] CDN/代理层配置（静态资源缓存、HTTP/2）
- [ ] 定时任务配置（如有定时发布需求）
- [ ] Queue Worker 配置（如有异步任务）
- [ ] 备份策略（数据库 + 上传文件）

---

> **文档维护**：每次重大架构变更后更新此文档。Git 提交信息中引用涉及的本节编号。
