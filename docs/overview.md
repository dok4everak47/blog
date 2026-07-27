# 博客核心缺失功能补全 — 工作总结

> 日期：2026-07-12
> 执行者：Senior Developer（高级开发工程师）
> 背景：基于 2026-07-12 的 code review 报告，用户授权直接补全核心缺失

---

## 完成内容

### 🔴 P0 安全修复（6 项）

| 修复 | 文件 |
|------|------|
| show() 过滤草稿（未登录访问草稿→404） | `NoteController.php` + `Note.php` 加 `scopePublished()` |
| 全部写操作调用 `$this->authorize()` | `NoteController.php` store/edit/update/destroy/autosave |
| autosave 路由加 `throttle:30,1` | `routes/web.php` |
| destroy 删除时清理封面图文件 | `NoteController.php` |
| marked/dompurify 本地化（移除 CDN） | `resources/js/editor.js` + `vite.config.js` + create/edit blade |

### 🟢 P2 博客核心功能（7 项）

| 功能 | 路由 | Controller / 视图 |
|------|------|-------------------|
| 文章列表页 + 分页 | `GET /notes` | `NoteController@index` → `notes/index.blade.php` |
| 写文章表单 | `GET /notes/create` | `NoteController@create` → `notes/create.blade.php` |
| 分类页 | `GET /categories/{category}` | `CategoryController@show` → `categories/show.blade.php` |
| 标签页 | `GET /tags/{tag}` | `TagController@show` → `tags/show.blade.php` |
| 搜索 | `GET /search?q=` | `SearchController@index` → `search.blade.php` |
| 上一篇/下一篇 + 相关文章 | — | `NoteController@show` 增强 + `notes/show.blade.php` |
| 导航搜索框 + 文章链接 | — | `components/nav.blade.php` |

### 🟡 P1 数据完整性（1 项）

- 新迁移 `2026_07_12_002000_add_unique_indexes`：
  - `note_tag` 加复合唯一索引 `unique(['note_id', 'tag_id'])`
  - `tags.name` / `categories.name` 加唯一索引

### 架构清理

- 抽 `HomeController`（home/about/contact）、`DashboardController`（dashboard）
- 移除 `routes/web.php` 里 4 个闭包路由
- 路由加 `whereNumber()` 约束避免 `/notes/create` 被 `{note}` 抢匹配

---

## 验证结果

| 检查 | 命令 | 结果 |
|------|------|------|
| Blade 编译 | `php artisan view:cache` | ✅ 全部通过 |
| 路由注册 | `php artisan route:list` | ✅ 14 条核心路由 |
| 前端打包 | `npm run build` | ✅ 284ms，editor.js 68KB |
| 数据库迁移 | `php artisan migrate` | ✅ 唯一索引已建 |

---

## 新增/修改文件清单

**新增**：
- `app/Http/Controllers/HomeController.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Controllers/TagController.php`
- `app/Http/Controllers/SearchController.php`
- `resources/views/notes/create.blade.php`
- `resources/views/categories/show.blade.php`
- `resources/views/tags/show.blade.php`
- `resources/views/search.blade.php`
- `resources/js/editor.js`
- `database/migrations/2026_07_12_002000_add_unique_indexes.php`

**修改**：
- `app/Models/Note.php`（加 scope）
- `app/Http/Controllers/NoteController.php`（重写：安全+列表+create+show 增强）
- `app/Http/Controllers/CategoryController.php`（加 show）
- `routes/web.php`（重写）
- `resources/views/notes/index.blade.php`（改为列表）
- `resources/views/notes/edit.blade.php`（CDN→本地）
- `resources/views/notes/show.blade.php`（加上一篇/下一篇/相关文章/标签链接）
- `resources/views/home.blade.php`（分类标签可点击+查看全部）
- `resources/views/dashboard.blade.php`（写文章链接改 create）
- `resources/views/components/nav.blade.php`（搜索框+文章链接+写文章指向 create）
- `vite.config.js`（input 加 editor.js）
- `package.json`（加 marked/dompurify 依赖）

---

## 仍未做（后续可继续）

报告里 P1/P3 中改动较大或非本次核心的项：
- 布局统一（`layouts/app` vs 独立 HTML）、两套导航合并
- `status` 改 PHP Enum
- 加 `user_id` 字段（多作者/真正 IDOR 防护）
- `NotePolicy` 当前仍全放行（需 user_id 才能真正生效）
- 暗黑模式、SEO meta、RSS/Sitemap、评论系统、excerpt 字段、图片缩略图

如需继续推进，可参考 `code-review-2026-07-12.md` 的 P1/P3 清单。
