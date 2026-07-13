# Laravel 博客部署踩坑记：从 CSP 报错到服务器安全加固

> 把一个 Laravel 13 博客从本地开发推到生产服务器，比写代码本身还折腾。今天踩了四个坑，逐个记录下来，给同样在折腾部署的朋友一个参考。

## 一、CSP 报错：Alpine.js 和 Cropper 都被拦了

### 现象

浏览器 Console 突然开始刷屏：

```
EvalError: Refused to evaluate a string as JavaScript because 'unsafe-eval' is not an allowed source of script in the Content Security Policy directive: "script-src 'self' 'unsafe-inline'"
```

紧接着，上传图片做裁剪时又报：

```
Connecting to 'blob:https://luongchin.com/xxx' violates the following Content Security Policy directive: "connect-src 'self'"
```

### 原因

我在 `SecurityHeaders` 中间件里设了 CSP 安全头。开发环境为了方便调试，`script-src` 放了 `'unsafe-eval'`，但生产环境没放。Alpine.js 运行时依赖 `eval()`，直接被拦。

第二个错误同理——cropper 裁剪图片后生成 `blob:` URL，但生产环境的 `connect-src` 只允许 `'self'`，`blob:` 被拒。

### 修复

改 `app/Http/Middleware/SecurityHeaders.php`，生产环境 CSP 两处：

```php
// script-src 加 'unsafe-eval'
"script-src 'self' 'unsafe-inline' 'unsafe-eval'",

// connect-src 加 blob:
"connect-src 'self' blob:",
```

### 教训

CSP 是好东西，但开发环境和生产环境的策略必须同步测试。我在本地开发时从来没触发过这个错误，因为 CSP 策略比生产宽松。**部署前一定要在真实域名上测一遍所有交互功能。**

---

## 二、Markdown 工具栏溢出：CSS "出血"布局的坑

### 现象

Dashboard 页面的 Markdown 编辑器工具栏横向溢出，比卡片本身还宽。

### 原因

工具栏 `.md-toolbar` 用了"出血"布局——负 margin 让工具栏顶到父容器边缘，视觉上像贴着卡片边框。但 Dashboard 页面用的是普通卡片容器，父容器的 padding 和负 margin 不匹配，导致工具栏比卡片更宽。

第一次尝试加大卡片 padding 来"喂饱"负 margin，结果反而更宽了。

### 修复

新增 `.md-toolbar-contained` 修饰类，去掉负 margin，换成独立的边框和 padding：

```css
.md-toolbar-contained {
    margin: 0;
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--color-border);
    border-radius: 0.5rem;
}
```

在需要 contained 布局的地方加上这个类就行。

### 教训

CSS 的"出血"布局（负 margin + 正 padding）虽然好看，但它依赖父容器的精确尺寸。换一个不同的容器环境就会崩。**如果布局可能在不同容器中使用，就给它一个"contained"变体类，两种模式都支持。**

---

## 三、图片上传 403：数据库里管理员不是管理员

### 现象

首页 Hero 背景图上传失败，返回 403 Forbidden。

### 排查过程

这个坑花了最久，因为 403 的原因太隐蔽：

1. **第一步**：怀疑 PHP 上传大小限制 → 改 `upload_max_filesize` 从 2M 到 20M → 不行
2. **第二步**：怀疑 Nginx body size 限制 → 改 `client_max_body_size` 从 10M 到 25M → 不行
3. **第三步**：怀疑 `storage/uploads` 目录不存在 → 创建并 chown → 不行
4. **第四步**：发现前端 `catch` 静默吞错，连错误信息都不显示 → 改成显示 `err?.message`
5. **第五步**：看到 CSP 拦截 blob URL → 加了 `blob:` 到 `connect-src`
6. **第六步**：还是 403 → 怀疑 session 过期 → 让用户重新登录 → 不行
7. **最终根因**：用 tinker 查数据库，发现 **`is_admin` 字段是 `false`**！

### 原因

数据库里管理员用户的 `is_admin` 列值是 `false`。`EnsureUserIsAdmin` 中间件检查这个字段，发现是 false 就直接 `abort(403)`。

可能是创建用户时忘了设 `is_admin`，或者 factory 种子数据没包含这个字段。

### 修复

直接在服务器上用 tinker 修正：

```bash
php artisan tinker --execute="DB::table('users')->where('id',1)->update(['is_admin' => true]);"
```

### 教训

这是一个"数据层"的 bug，代码逻辑没问题，但数据状态不对。排查时我一直在看代码、看配置，完全没想到查数据库。**403 不一定是代码问题，也可能是数据问题——查数据库字段值比查代码更快。**

另外，前端 `catch` 静默吞错是排查的大敌。如果一开始就显示错误信息，我至少能早点看到是 403 而不是在那猜上传限制。

---

## 四、服务器安全加固：裸奔太危险了

检查服务器安全状况后发现几个问题：

| 问题 | 修复 |
|------|------|
| UFW 防火墙未启用 | 只开放 22/80/443，其余全部拒绝 |
| 无暴力破解防护 | 安装 fail2ban，SSH 3 次失败 → 封禁 1 小时 |
| SSH 允许 root + 密码登录 | 禁止 root 登录，禁止密码登录，只接受密钥 |

操作顺序很重要：**先开 UFW（放行 SSH）→ 再装 fail2ban → 最后改 SSH 配置**。如果先关密码登录再开防火墙，万一密钥有问题你就彻底锁在外面了。

SSH 配置修改前先备份：

```bash
sudo cp /etc/ssh/sshd_config /etc/ssh/sshd_config.bak.20260713
```

改完后重启 SSH，立刻用新连接验证：

```bash
sudo systemctl restart ssh
# 新开一个终端测试连接，确认密钥登录正常
```

---

## 小结

今天四个坑的共性：**都是"环境差异"导致的问题**。

- CSP 报错：开发环境策略宽松，生产环境严格 → 交互功能全部报废
- 工具栏溢出：本地编辑器容器和 Dashboard 容器 padding 不同 → 布局崩
- 403 上传：本地数据库 `is_admin` 正确，服务器数据库 `is_admin = false` → 权限拦
- 服务器裸奔：开发时图方便全开端口，上线后忘了关 → 安全隐患

部署不只是"把代码推上去"，还得确保数据、配置、安全策略在所有环境里一致。以后每推一次代码，我都会在生产环境跑一遍功能验证，不再相信"本地能跑就行"。
