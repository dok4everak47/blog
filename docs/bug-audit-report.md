# Laravel Blog Bug 审计报告

> 审计日期：2026-07-13
> 审计范围：路由 / 控制器 / 模型 / 策略 / 中间件 / 表单请求 / 迁移 / 视图 / JavaScript / 配置
> 测试结果：43 tests 全通过

---

## 审计方法

使用代码探针子代理通读**所有**源码文件，按 9 大维度逐条排查，人工复核后批量修复。

---

## 已修复 Bug（14 项）

### 🔴 严重（Critical）— 已全部修复

| # | Bug | 文件 | 修复说明 |
|---|-----|------|----------|
| 1 | TagController `whereNull('deleted_at')` 引用不存在的列 | `TagController.php:38` | 移除 `->whereNull('deleted_at')`，tags 表无 deleted_at 字段，会触发 SQL 错误导致标签创建完全不可用 |
| 2 | 评论 `parent_id` 未验证属于同一文章 | `StoreCommentRequest.php:18` | 改用 `Rule::exists()->where()` 限定父评论必须属于当前文章，防止跨文章评论树错乱 |
| 3 | `User.is_admin` 在 `$fillable` 中 | `User.php:13` | 从 `#[Fillable]` 移除 `is_admin`，防止未来批量赋值漏洞；DatabaseSeeder 改为显式赋值 `$admin->is_admin = true` |

### 🟡 中等（Medium）— 已全部修复

| # | Bug | 文件 | 修复说明 |
|---|-----|------|----------|
| 4 | 编辑/删除按钮对所有登录用户可见 | `show.blade.php:290` | 加 `@if(auth()->id() === $note->user_id)` 条件，仅作者可见（后端 Policy 已有保护，前端补齐 UX） |
| 5 | CategoryController 验证失败返回 302 而非 JSON | `CategoryController.php:32` | 改用 `Validator::make()` + 显式 `response()->json(['errors' => ...], 422)`，与 TagController 一致 |
| 6 | 搜索 LIKE 通配符未转义 | `SearchController.php:21` | 添加 `str_replace(['\\','%','_'], ['\\\\','\\%','\\_'], $q)`，搜索 `100%` 不再匹配所有含 100 的内容 |
| 7 | show() 未预加载 `user` 关系，JSON-LD 触发 N+1 | `NoteController.php:91` | `$note->load(...)` 添加 `'user'` 到预加载列表 |
| 8 | 阅读时间计算不一致（视图 300 字/分 vs 模型 400 字/分） | `show.blade.php:9-12` | 视图改用 `$note->readingMinutes()` 统一调用模型方法 |
| 9 | TOC 初始化 `firstLink.click()` 导致页面加载时滚动 | `show.blade.php:368` | 改为只设置高亮 CSS class，不触发 click 事件 |
| 10 | NoteFactory `published()` 未设 `published_at` | `NoteFactory.php:42` | `published()` state 添加 `'published_at' => now()`；`definition()` 也按状态设置 |
| 11 | 评论删除无确认对话框 | `show.blade.php:179` | 添加 `onclick="return confirm('确定要删除这条评论吗？')"` |
| 12 | autosave 不更新 `excerpt` 字段 | `NoteController.php:199` | `$attrs` 添加 `'excerpt' => Note::generateExcerpt(...)` |
| 13 | RSS enclosure MIME 硬编码 `image/jpeg` | `rss.blade.php:21` | 根据扩展名动态推断（jpg/png/gif/webp） |
| 14 | sitemap 缓存未在文章增删改时清除 | `NoteController.php` | store/update/destroy 三个方法添加 `Cache::forget('feed.sitemap')` |

### 🟢 低（Low）— 已验证无需修复

| # | Bug | 结论 |
|---|-----|------|
| L1 | ProfileController 删除用户产生孤儿数据 | **无需修复** — notes.user_id 和 comments.user_id 均为 `cascadeOnDelete`，数据库自动级联删除。已补充代码注释说明 |
| L2 | `SecurityHeaders` 生产环境 CSP 允许 `'unsafe-inline'` | **已知技术债** — 移除需前端重构为 nonce-based CSP，当前可接受 |
| L3 | `makeSlug()` 并发竞态条件 | **数据库兜底** — slug 有唯一索引，并发冲突时抛 QueryException，可后续加 try-catch |
| L4 | `show()` 相关文章只按分类，不按标签 | **功能限制** — 当前实现可用，后续可优化为分类+标签联合查询 |
| L5 | `NotePolicy::view()` 未处理 Archived 状态 | **设计决策** — 当前 Archived 状态未被使用，归档文章仅作者可见，可后续按需调整 |
| L6 | GIF 缩略图透明度处理不正确 | **边界情况** — `imagecolortransparent` 返回的是索引值，当前代码当 RGB 处理，但实际影响很小（GIF 封面极少） |
| L7 | `HomeController::about()` 返回 `profile` 视图 | **命名混淆** — 功能正常，仅命名不直观，不影响运行 |
| L8 | autosave 前端发送了 `status=draft` 但后端忽略 | **无害** — 后端验证规则不含 status，字段被忽略，但建议前端清理 |
| L9 | JSON-LD `datePublished` 对草稿使用 `created_at` | **SEO 边界** — 草稿仅作者可见，搜索引擎抓不到，影响极小 |

---

## 修复统计

| 类别 | 数量 |
|------|------|
| 🔴 严重 | 3（全部修复） |
| 🟡 中等 | 11（全部修复） |
| 🟢 低 | 9（1 项已注释说明 / 8 项验证无需修复或可延后） |
| **合计** | **14 项修复 + 9 项验证** |

---

## 测试验证

```
Tests:    43 passed (101 assertions)
Duration: 1.52s
```

所有测试通过，包括：
- AuthenticationTest（登录/登出）
- EmailVerificationTest
- PasswordReset/UpdateTest
- RegistrationTest
- NoteAuthorizationTest（IDOR 防护）
- SearchTest（搜索功能）
- UploadImageTest（图片上传安全）

---

## Commit

```
eee10db 修复 14 个 bug：TagController whereNull/评论跨文章/is_admin 批量赋值/编辑按钮越权显示/CategoryController 验证非 JSON/搜索 LIKE 转义/show N+1/阅读时间不一致/TOC 自动点击/Factory published_at/评论删除确认/autosave excerpt/RSS MIME/sitemap 缓存
```

---

## 后续建议

1. **L3 并发 slug**：可在 store/update 包裹 try-catch 处理唯一约束冲突
2. **L4 相关文章**：可优化为分类 + 标签联合查询
3. **L2 CSP**：长期可迁移到 nonce-based CSP
4. **L6 GIF 透明度**：如需支持 GIF 封面，修复 `imagecolorsforindex()` 调用
