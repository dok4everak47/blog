{{-- Markdown 工具栏（写文章 / 编辑文章 共用）
     依赖父级 x-data="articleEditor()" 作用域中的状态与方法：
     - active（按钮高亮状态）
     - viewMode（编辑 / 分屏 / 预览）
     - applyCommand(cmd) / insertLink() / insertImage() / setViewMode(mode)
     参考 GitHub / Obsidian / Hashnode 的极简风格。 --}}
<div class="md-toolbar" role="toolbar" aria-label="Markdown 编辑工具栏">

  {{-- 文本样式 --}}
  <div class="md-toolbar-group">
    <button type="button" class="md-btn md-text" data-tip="粗体 (Ctrl+B)" title="粗体 (Ctrl+B)"
            :class="{ 'active': active.bold }" @click="applyCommand('bold')"><b>B</b></button>
    <button type="button" class="md-btn md-text" data-tip="斜体 (Ctrl+I)" title="斜体 (Ctrl+I)"
            :class="{ 'active': active.italic }" @click="applyCommand('italic')"><i>I</i></button>
    <button type="button" class="md-btn md-text" data-tip="删除线" title="删除线"
            :class="{ 'active': active.strike }" @click="applyCommand('strike')"><s>S</s></button>
  </div>

  <span class="md-sep" aria-hidden="true"></span>

  {{-- 标题 --}}
  <div class="md-toolbar-group">
    <button type="button" class="md-btn md-text" data-tip="一级标题" title="一级标题"
            :class="{ 'active': active.h1 }" @click="applyCommand('h1')">H1</button>
    <button type="button" class="md-btn md-text" data-tip="二级标题" title="二级标题"
            :class="{ 'active': active.h2 }" @click="applyCommand('h2')">H2</button>
    <button type="button" class="md-btn md-text" data-tip="三级标题" title="三级标题"
            :class="{ 'active': active.h3 }" @click="applyCommand('h3')">H3</button>
  </div>

  <span class="md-sep" aria-hidden="true"></span>

  {{-- 列表与结构 --}}
  <div class="md-toolbar-group">
    <button type="button" class="md-btn" data-tip="无序列表" title="无序列表"
            :class="{ 'active': active.ul }" @click="applyCommand('ul')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M9 6h12M9 12h12M9 18h12"/><circle cx="4" cy="6" r="1.4" fill="currentColor" stroke="none"/><circle cx="4" cy="12" r="1.4" fill="currentColor" stroke="none"/><circle cx="4" cy="18" r="1.4" fill="currentColor" stroke="none"/>
      </svg>
    </button>
    <button type="button" class="md-btn" data-tip="有序列表" title="有序列表"
            :class="{ 'active': active.ol }" @click="applyCommand('ol')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M10 6h11M10 12h11M10 18h11"/><text x="2" y="8.5" font-size="7" fill="currentColor" stroke="none" font-family="sans-serif">1</text><text x="2" y="14.5" font-size="7" fill="currentColor" stroke="none" font-family="sans-serif">2</text><text x="2" y="20.5" font-size="7" fill="currentColor" stroke="none" font-family="sans-serif">3</text>
      </svg>
    </button>
    <button type="button" class="md-btn" data-tip="任务列表" title="任务列表"
            :class="{ 'active': active.task }" @click="applyCommand('task')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="4" width="6" height="6" rx="1.5"/><path d="M5 7l1.2 1.2L9 6"/><rect x="3" y="14" width="6" height="6" rx="1.5"/><path d="M13 7h8M13 17h8"/>
      </svg>
    </button>
    <button type="button" class="md-btn" data-tip="引用" title="引用"
            :class="{ 'active': active.quote }" @click="applyCommand('quote')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M3 21c3 0 7-1 7-8V5H3v8h4M14 21c3 0 7-1 7-8V5h-7v8h4"/>
      </svg>
    </button>
  </div>

  <span class="md-sep" aria-hidden="true"></span>

  {{-- 代码 --}}
  <div class="md-toolbar-group">
    <button type="button" class="md-btn" data-tip="行内代码" title="行内代码"
            :class="{ 'active': active.code }" @click="applyCommand('code')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M8 7l-5 5 5 5M16 7l5 5-5 5"/>
      </svg>
    </button>
    <button type="button" class="md-btn" data-tip="代码块" title="代码块" @click="applyCommand('codeblock')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9.5 9.5l-3 3 3 3M14.5 9.5l3 3-3 3"/>
      </svg>
    </button>
  </div>

  <span class="md-sep" aria-hidden="true"></span>

  {{-- 插入 --}}
  <div class="md-toolbar-group">
    <button type="button" class="md-btn" data-tip="插入链接 (Ctrl+K)" title="插入链接 (Ctrl+K)" @click="applyCommand('link')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M10 13a5 5 0 0 0 7 0l2-2a5 5 0 0 0-7-7l-1 1"/><path d="M14 11a5 5 0 0 0-7 0l-2 2a5 5 0 0 0 7 7l1-1"/>
      </svg>
    </button>
    <button type="button" class="md-btn" data-tip="插入图片" title="插入图片" @click="applyCommand('image')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="M21 16l-5-5-6 6"/>
      </svg>
    </button>
    <button type="button" class="md-btn" data-tip="插入表格" title="插入表格" @click="applyCommand('table')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18M3 15h18M9 4v16M15 4v16"/>
      </svg>
    </button>
    <button type="button" class="md-btn" data-tip="分割线" title="分割线" @click="applyCommand('hr')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M4 12h16"/><path d="M8 8v8M16 8v8"/>
      </svg>
    </button>
  </div>

  <span class="md-spacer" aria-hidden="true"></span>

  {{-- 视图切换：编辑 / 分屏 / 预览 --}}
  <div class="md-viewtoggle" role="group" aria-label="视图模式">
    <button type="button" :class="{ 'active': viewMode === 'edit' }" @click="setViewMode('edit')">编辑</button>
    <button type="button" :class="{ 'active': viewMode === 'split' }" @click="setViewMode('split')">分屏</button>
    <button type="button" :class="{ 'active': viewMode === 'preview' }" @click="setViewMode('preview')">预览</button>
  </div>
</div>
