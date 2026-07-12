# UI 风格指南 v2 — 合荒小站暖调极简版

基于参考截图 `Screenshot 2026-07-11 at 23-51-07.png` 提取。

---

## 1. 配色

| Token | HEX | 用途 |
|-------|-----|------|
| `--color-bg` | `#FDFCFA` | 页面背景 |
| `--color-surface` | `#F9F8F6` | 卡片背景、Hero 左侧 |
| `--color-surface-2` | `#FFFFFF` | 纯卡片背景 |
| `--color-border` | `#EDEAE5` | 边框、分割线 |
| `--color-text` | `#2C2A28` | 主文字 |
| `--color-text-secondary` | `#8C8884` | 次要文字、meta |
| `--color-primary` | `#D88C8C` | 玫瑰粉，强调小标题、标签、hover |
| `--color-primary-hover` | `#C77A7A` | 主色 hover |
| `--color-primary-light` | `#FAF0F0` | 主色浅底 |
| `--color-sage` | `#9BB5A4` | 鼠尾草绿，配图区、次级强调 |
| `--color-sage-light` | `#EEF3F0` | 鼠尾草浅底 |
| `--color-gold` | `#C9A66B` | 暖金色，featured 数字、大标题点缀 |
| `--color-gold-light` | `#F5F0E8` | 暖金浅底 |

---

## 2. 字体

- 中文：`Noto Sans SC`（已配置）
- 英文：`Instrument Sans`（已配置）
- 标题：`font-bold` / `font-extrabold`，字间距适中
- 正文：`font-normal`，`leading-relaxed`（1.625）
- 小标签：`text-xs` / `text-sm`，`font-medium`，大写时加 `tracking-widest`

---

## 3. 间距

| 场景 | 值 |
|------|----|
| 页面水平内边距 | `px-4 sm:px-6 lg:px-8` |
| 内容最大宽度 | `max-w-5xl` / `max-w-6xl` |
| 区块垂直间距 | `py-16` / `py-20` |
| 卡片内边距 | `p-6 sm:p-8` |
| 卡片间距 | `gap-6` / `gap-8` |
| 表单组间距 | `space-y-5` |
| 标签与输入框 | `mb-2`（8px） |

---

## 4. 组件样式

### 卡片
- 圆角：`rounded-xl`（约 16px）
- 背景：纯白或 `surface`
- 边框：1px `border-border`
- 阴影：无 或 极浅 `0 1px 2px rgba(44,42,40,0.04)`
- hover：边框色加深或微移 `-translate-y-0.5`

### 按钮
- 主按钮：圆角 `rounded-lg`（10-12px），背景 `primary`，文字白色
- 次按钮：浅底 + 细边框
- 文字链接：品牌色 hover

### 输入框
- 高度 48px，左右 16px padding
- 圆角 `rounded-lg`
- 默认浅色边框，hover 加深，focus 品牌色 ring
- 无重阴影

### 导航
- 极简、吸顶、毛玻璃背景
- Logo 左、链接中、操作右
- 当前页高亮：品牌色

---

## 5. 整体布局

### 首页
1. 顶部两列网格：
   - 左侧：Welcome 卡片（站点名称 + overview）
   - 右侧：Featured / Hero 卡片（年份大数字 + 精选文章）
2. 下方文章列表：
   - 宽卡片左文右图（60:40）
   - 左侧：日期、标题、标签、摘要
   - 右侧：鼠尾草绿配图区
3. 简洁页脚

### 其他页面
- 统一使用 `bg-surface` 或 `bg-bg` 背景
- 内容居中，最大宽度 `max-w-2xl` / `max-w-3xl`
- 卡片容器、简洁标题

---

## 6. 响应式

- 移动端：顶部两列 → 单列
- 文章卡片：左文右图 → 图文上下堆叠
- 导航：汉堡菜单
