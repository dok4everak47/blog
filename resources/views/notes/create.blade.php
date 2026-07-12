<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>写文章 · My Blog</title>
  @vite(['resources/css/app.css', 'resources/js/editor.js', 'resources/js/app.js'])
</head>

<body class="bg-bg min-h-screen text-text">
  <x-nav />

  <div x-data="articleEditor()"
       x-cloak
       data-tags='@json($tags)'
       data-initial-selected='@json([])'
       data-note-id=""
       data-initial-cover=""
       data-server-errors='@json(isset($errors) ? $errors->all() : [])'
       data-autosave-url="{{ route('notes.autosave') }}"
       data-update-url="{{ route('notes.update', '__ID__') }}"
       data-create-category-url="{{ route('categories.store') }}">

    <main class="max-w-4xl mx-auto px-4 sm:px-6 pt-6 pb-28">
      {{-- 顶部：面包屑 + 保存状态 --}}
      <div class="flex items-center justify-between mb-6">
        <nav class="flex items-center gap-2 text-sm text-text-secondary">
          <a href="{{ route('dashboard') }}" class="hover:text-primary transition">Dashboard</a>
          <span class="text-text-muted">/</span>
          <span class="text-text font-medium">写文章</span>
        </nav>
        <div class="flex items-center gap-2 text-sm">
          <span class="h-2 w-2 rounded-full transition-colors duration-200"
                :class="dirty ? 'bg-gold' : 'bg-sage'"></span>
          <span class="text-text-secondary" x-text="savedLabel || '新文章'"></span>
        </div>
      </div>

      <form x-ref="form" action="{{ route('notes.store') }}" method="POST"
            @submit="beforeSubmit($event)">
        @csrf

        {{-- 校验错误横幅 --}}
        <div x-ref="errorBanner" x-show="errors.length" x-cloak
             class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          <p class="font-medium mb-1">请修正以下问题：</p>
          <ul class="list-disc pl-5 space-y-0.5">
            <template x-for="err in errors" :key="err">
              <li x-text="err"></li>
            </template>
          </ul>
        </div>

        {{-- 写作区卡片：标题 + 工具栏 + 正文 / 预览 --}}
        <div class="card-editor">
          <input x-ref="title" type="text" name="title" maxlength="255"
                 class="title-input" placeholder="请输入文章标题..."
                 @input="onChange()">
          <div class="flex justify-end mt-1">
            <span class="text-xs text-text-muted"><span x-text="titleLen"></span>/255</span>
          </div>

          @include('components.markdown-toolbar')

          <div class="editor-body" :class="viewMode">
            <textarea x-ref="content" name="content"
                      class="editor-textarea" placeholder="开始写作吧，支持 Markdown 语法…"
                      @input="onChange()"
                      @keydown="onEditorKeydown($event)"
                      @keyup="updateActive()"
                      @mouseup="updateActive()"
                      @click="updateActive()"></textarea>

            <div class="md-preview article-content" x-show="viewMode !== 'edit'" x-cloak x-html="previewHtml"></div>
          </div>
        </div>

        {{-- 发布设置：分类 + Slug --}}
        <div class="mt-8 grid gap-5 sm:grid-cols-2">
          <div>
            <label class="block text-sm font-medium text-text mb-2">分类</label>
            <select x-ref="category" name="category_id" class="field-control" @change="onCategoryChange()">
              <option value="">不选分类</option>
              @foreach ($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
              @endforeach
              <option value="__NEW__">+ 新建分类…</option>
            </select>

            {{-- 内联新建分类 --}}
            <div x-show="newCategoryOpen" x-cloak class="mt-2 flex items-center gap-2">
              <input x-ref="newCategoryInput" type="text" x-model="newCategoryName"
                     class="field-control flex-1" placeholder="输入新分类名称"
                     @keydown.enter.prevent="createCategory()">
              <button type="button" class="btn-primary px-3 py-1.5 text-sm" @click="createCategory()" :disabled="catSaving">
                <span x-show="catSaving" class="spinner spinner-sm" x-cloak></span>
                <span x-text="catSaving ? '创建中' : '确定'"></span>
              </button>
              <button type="button" class="btn-ghost px-3 py-1.5 text-sm" @click="cancelNewCategory()">取消</button>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-text mb-2">URL Slug</label>
            <input x-ref="slug" type="text" name="slug" class="field-control"
                   placeholder="自动生成" @input="onSlugInput()">
          </div>
        </div>

        {{-- 封面图 --}}
        <div class="mt-6">
          <label class="block text-sm font-medium text-text mb-3">封面图</label>

          <input x-ref="coverInput" type="file" name="cover_image" accept="image/*"
                 class="hidden" @change="onCoverChange()">

          {{-- 未设置封面：点击或拖拽上传 --}}
          <div x-show="!coverPreview" @click="$refs.coverInput.click()"
               @dragover.prevent="$el.classList.add('dragover')"
               @dragleave.prevent="$el.classList.remove('dragover')"
               @drop.prevent="$refs.coverInput.files = $event.dataTransfer.files; onCoverChange()"
               class="cover-dropzone cursor-pointer">
            <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-sm">点击或拖拽上传封面图</p>
            <p class="text-xs text-text-muted mt-0.5">建议 16:9，JPG / PNG / WebP，≤ 5MB</p>
          </div>

          {{-- 已设置封面：预览 + 操作 --}}
          <div x-show="coverPreview" x-cloak class="cover-preview-wrap">
            <img :src="coverPreview" alt="封面预览" class="cover-preview-img">
            <div class="flex items-center gap-4 mt-3">
              <button type="button" class="cover-link-btn" @click="$refs.coverInput.click()">更换封面</button>
              <button type="button" class="cover-link-btn text-red-600" @click="removeCover()">移除封面</button>
            </div>
          </div>
        </div>

        {{-- 标签 --}}
        <div class="mt-6">
          <div class="flex items-center justify-between mb-3">
            <label class="block text-sm font-medium text-text">标签</label>
            <div class="flex items-center gap-3 text-xs">
              <button type="button" class="text-text-secondary hover:text-primary transition" @click="selectAllTags()">全选</button>
              <span class="text-text-muted">·</span>
              <button type="button" class="text-text-secondary hover:text-primary transition" @click="clearTags()">清空</button>
            </div>
          </div>
          <div class="flex flex-wrap gap-2">
            <template x-for="tag in allTags" :key="tag.id">
              <button type="button" class="tag-chip"
                      :class="{ 'active': selectedTags.includes(tag.id) }"
                      @click="toggleTag(tag.id)"
                      x-text="tag.name"></button>
            </template>
          </div>
          <div x-ref="tagInputs"></div>
        </div>

        {{-- 底部操作区 --}}
        <div class="mt-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <p class="text-sm text-text-secondary">
            <span x-text="stats.words"></span> 字 · 约 <span x-text="stats.mins"></span> 分钟阅读
          </p>
          <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="btn-ghost">取消</a>
            <button type="button" class="btn-ghost" @click="saveDraft()" :disabled="saving">
              存为草稿
            </button>
            <button type="submit" class="btn-primary" :disabled="saving">
              <span x-show="saving" class="spinner" x-cloak></span>
              <span x-text="saving ? '正在保存...' : '发布文章'"></span>
            </button>
          </div>
        </div>
      </form>
    </main>

    {{-- 保存成功 Toast --}}
    <div x-show="toast" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="toast" x-text="toastMsg"></div>
  </div>

  @include('components.article-editor-script')
</body>
</html>
