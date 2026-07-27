# High Scalability 风格设计规范

参考截图整体给人的感觉是：**极度干净、大量留白、以内容为中心、薄荷绿作为唯一强调色**。下面把它拆成可落地的设计 token。

## 1. 配色

| Token | 色值 | 用途 |
|-------|------|------|
| `primary` | `#5AC596` | 主按钮、强调链接、hover 状态 |
| `primary-hover` | `#4DB583` | 主按钮 hover |
| `text` | `#111827` | 标题、正文主色 |
| `text-secondary` | `#6B7280` | 摘要、meta、辅助文字 |
| `surface` | `#F9FAFB` | 输入框背景、标签背景 |
| `border` | `#E5E7EB` | 分隔线、卡片边框、输入框边框 |
| `background` | `#FFFFFF` | 页面背景 |

## 2. 字体

- 字体族保持 `Instrument Sans`（项目已配置）。
- 需要在 `vite.config.js` 中增加 `700` 字重，否则大标题不够“重”。

| 层级 | 字号 | 字重 | 行高 | 用途 |
|------|------|------|------|------|
| 页面大标题 | `text-4xl` (~36px) | 700 | tight | 首页/页脚大标题 |
| 区块标题 | `text-2xl` (~24px) | 700 | tight | 表单页标题 |
| 文章标题 | `text-xl` (~20px) | 700 | snug | 列表标题 |
| 正文 | `text-base` (~16px) | 400 | relaxed | 摘要、内容 |
| meta/小字 | `text-sm` (~14px) | 400 | normal | 日期、作者 |
| 标签 | `text-xs` (~12px) | 500 | normal | 分类、tag |

## 3. 间距与留白

- 内容区最大宽度：文章列表 `max-w-4xl`（~896px），单篇文章/表单 `max-w-2xl`（~672px）。
- 页面内边距：`px-4`。
- 模块之间：`py-12`、`mb-16`、`mt-20`。
- 文章列表项之间用 `border-b` 分隔，而不是卡片阴影。
- 卡片/模块内部：`p-6`。

## 4. 组件样式

### 主按钮
```html
<a class="rounded-full bg-primary px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-hover transition">
  Subscribe
</a>
```

### 次按钮/描边按钮
```html
<a class="rounded-full border border-border bg-white px-5 py-2.5 text-sm font-medium text-text hover:bg-surface transition">
  ✍️ 写笔记
</a>
```

### 导航栏
- 白色背景、底部细线 `border-b`、无阴影。
- 左中右三段式：左侧页面链接、中间 Logo、右侧操作。

### 文章卡片
- 无圆角阴影，只有底部分隔线。
- 左图右文结构，缩略图 `w-40 h-24 rounded-lg`。
- 标题 hover 时透明度降低。

### 输入框 + 内嵌按钮
```html
<form class="relative max-w-md mx-auto rounded-full border border-border bg-surface p-1">
  <input class="w-full bg-transparent pl-4 pr-28 py-2.5 text-sm outline-none placeholder:text-text-secondary">
  <button class="absolute right-1 top-1 bottom-1 rounded-full bg-primary px-5 text-sm font-medium text-white hover:bg-primary-hover transition">
    Subscribe
  </button>
</form>
```

### 标签
```html
<span class="rounded-full border border-border bg-surface px-2.5 py-0.5 text-xs text-text-secondary">
  Laravel
</span>
```

## 5. 整体布局

```
+-----------------------------------------------------------+
|  首页  Profile  Contact us        My Blog       Sign in [Subscribe] |
+-----------------------------------------------------------+
|                                                           |
|                    My Blog                                |
|            记录学习与生活的每一刻                          |
|                                                           |
|  +------+  Kafka 101                                      |
|  |      |  摘要文字...                                    |
|  +------+  By My Blog — 09 May 2024                       |
|  ------------------------------------------------------   |
|  +------+  Capturing A Billion Emo(j)i-ons                |
|  |      |  ...                                            |
|  +------+  By My Blog — 26 Mar 2024                       |
|                                                           |
|  分类               标签                                   |
|  -----------------  ------------------------------------  |
|                                                           |
|                    My Blog                                |
|        记录学习与生活的每一刻                              |
|         [email____________] [Subscribe]                   |
+-----------------------------------------------------------+
```
