# 写文章 · 封面图上传 实现说明

> 在「专注写作」编辑器基础上，增加封面图上传能力，上传的图片直接作为文章封面。

## 改动清单

### 后端
- **Migration** `2026_07_12_001000_add_cover_image_to_notes_table.php`：`notes` 表新增 `cover_image`（nullable，位于 content 之后），已 `migrate` 成功。
- **Note 模型**：`$fillable` 加入 `cover_image`；新增 `cover_image_url` 访问器，返回 **根相对路径** `/storage/covers/xxx.jpg`（刻意不用 `asset()`，避免 `APP_URL=http://localhost` 在 9090 开发端口下指向错误端口）。
- **NoteController**：
  - `store()`：上传封面存至 `storage/app/public/covers`。
  - `update()`：支持「上传新图（自动删旧图）」与「`remove_cover` 移除」。
  - `autosave()`：同样支持封面上传/移除，并返回 `cover_url` 供前端回写。
  - 引入 `Illuminate\Support\Facades\Storage`。
- **Form Requests**：`Store`/`Update` 均增加 `cover_image`(image|mimes:jpeg,png,jpg,webp,gif|max:5120) 与 `remove_cover`(boolean)。
- 执行 `php artisan storage:link`（`public/storage` → `storage/app/public`）。

### 前端
- **app.css**：`.cover-dropzone`（虚线拖拽区，hover/dragover 变品牌色）、`.cover-preview-img`、`.cover-link-btn`（更换/移除链接）。
- **article-editor-script.blade.php**：
  - 新增 `coverPreview` / `coverChanged` / `coverRemoved` 状态。
  - `onCoverChange()`：点击或拖拽选图、本地预览、标脏、触发防抖自动保存。
  - `removeCover()`：清空选择并标记移除。
  - `buildFormData()`：用 `FormData` 携带封面文件 / `remove_cover`。
  - `autosaveSave()`：改为 `fetch` + `FormData`；成功后用 `cover_url` 回写预览并清空 file input，避免重复上传。
- **notes/index.blade.php / edit.blade.php**：根元素加 `data-initial-cover`（编辑页回填已有封面）；发布设置区新增「封面图」块（隐藏 file input + 拖拽区 + 预览 + 更换/移除）。
- **home.blade.php**：文章列表右侧有封面则显示封面图，无封面回退到原鼠尾草占位块；空状态术语统一为「文章」。
- **notes/show.blade.php**：详情页顶部展示封面图。

## 使用方式
1. 登录后进入「写文章」（或编辑已有文章）。
2. 在底部「发布设置 → 封面图」点击或拖拽上传图片（建议 16:9，JPG/PNG/WebP，≤5MB）。
3. 上传后即时预览；可「更换封面」或「移除封面」。
4. 发布后，封面会显示在首页文章列表与文章详情页顶部。

## 验证结果
- `npm run build` 通过；`migrate` 成功；`storage:link` 成功。
- Tinker 模拟上传：文件落盘、`cover_image_url` 返回 `/storage/covers/...`（相对路径）。
- 视图渲染：create / edit / show 均无报错且含封面标记。
- 路由健康：`/` → 200，`/notes` → 302（需登录）。

## 注意事项
- 封面图访问地址使用根相对路径，因此在 `localhost:9090` 或正式域名下都能正确加载。
- 数据库迁移新增 `cover_image` 列；若需回滚可用对应 down 方法。
