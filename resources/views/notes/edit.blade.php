<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>编辑文章</title>
  @vite(['resources/css/app.css', 'resources/js/editor.js', 'resources/js/app.js'])
</head>

<body class="bg-bg min-h-screen text-text">
  <x-nav />

  <div x-data="articleEditor()"
       x-cloak
       data-tags='@json($tags)'
       data-initial-selected='@json($note->tags->pluck('id')->toArray())'
       data-note-id="{{ $note->id }}"
       data-initial-cover="{{ $note->cover_image_url ?? '' }}"
       data-autosave-url="{{ route('notes.autosave') }}"
       data-update-url="{{ route('notes.update', '__ID__') }}"
       data-create-tag-url="{{ route('tags.store') }}"
       data-upload-image-url="{{ route('notes.upload-image') }}">

    <main class="max-w-4xl mx-auto px-4 sm:px-6 pt-6 pb-28">
      {{-- 顶部：面包屑 + 保存状态 --}}
      <div class="flex items-center justify-between mb-6">
        <nav class="flex items-center gap-2 text-sm text-text-secondary">
          <a href="{{ route('dashboard') }}" class="hover:text-primary transition">Dashboard</a>
          <span class="text-text-muted">/</span>
          <span class="text-text font-medium">编辑文章</span>
        </nav>
        <div class="flex items-center gap-2 text-sm">
          <span class="h-2 w-2 rounded-full transition-colors duration-200"
                :class="dirty ? 'bg-gold' : 'bg-sage'"></span>
          <span class="text-text-secondary" x-text="savedLabel || '已保存'"></span>
        </div>
      </div>

      <form x-ref="form" action="{{ route('notes.update', $note) }}" method="POST"
            @submit="beforeSubmit($event)">
        @csrf
        @method('PUT')

        {{-- 写作区卡片：标题 + 工具栏 + 正文 / 预览 --}}
        <div class="card-editor">
          <input x-ref="title" type="text" name="title"
                 class="title-input" placeholder="请输入文章标题..."
                 value="{{ $note->title }}" @input="onChange()">

          @include('components.markdown-toolbar')

          <div class="editor-body" :class="viewMode">
            <textarea x-ref="content" name="content"
                      class="editor-textarea" placeholder="开始写作吧，支持 Markdown 语法…"
                      @input="onChange()"
                      @keydown="onEditorKeydown($event)"
                      @keyup="updateActive()"
                      @mouseup="updateActive()"
                      @click="updateActive()">{{ $note->content }}</textarea>

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
                <option value="{{ $category->id }}" {{ $note->category_id == $category->id ? 'selected' : '' }}>
                  {{ $category->name }}
                </option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-text mb-2">URL Slug</label>
            <input x-ref="slug" type="text" name="slug" class="field-control"
                   placeholder="自动生成" value="{{ $note->slug ?? '' }}" @input="onSlugInput()">
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
          <label class="block text-sm font-medium text-text mb-3">标签</label>
          <div class="flex flex-wrap gap-2 mb-3">
            <template x-for="tag in allTags" :key="tag.id">
              <button type="button" class="tag-chip"
                      :class="{ 'active': selectedTags.includes(tag.id) }"
                      @click="toggleTag(tag.id)"
                      x-text="tag.name"></button>
            </template>
          </div>

          {{-- 新建标签 --}}
          <div class="flex items-center gap-2">
            <input type="text"
                   x-model="newTagName"
                   @keydown.enter.prevent="createTag()"
                   placeholder="输入新标签后回车"
                   maxlength="30"
                   class="flex-1 px-3 py-1.5 text-sm border border-border rounded-lg bg-bg text-text focus:outline-none focus:border-primary transition">
            <button type="button"
                    @click="createTag()"
                    :disabled="!newTagName.trim() || tagCreating"
                    class="px-3 py-1.5 text-sm rounded-lg border border-border text-text-secondary hover:text-primary hover:border-primary transition disabled:opacity-50 disabled:cursor-not-allowed">
              <span x-show="!tagCreating">添加</span>
              <span x-show="tagCreating" x-cloak>添加中...</span>
            </button>
          </div>

          <div x-ref="tagInputs"></div>
        </div>

        {{-- 底部操作区 --}}
        <div class="mt-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <p class="text-sm text-text-secondary">
            <span x-text="stats.words"></span> 字 · 约 <span x-text="stats.mins"></span> 分钟阅读
          </p>
          <div class="flex items-center gap-3">
            <button type="button" class="btn-ghost" @click="saveDraft()" :disabled="saving">
              存为草稿
            </button>
            <button type="submit" class="btn-primary" :disabled="saving">
              <span x-show="saving" class="spinner" x-cloak></span>
              <span x-text="saving ? '正在保存...' : '更新文章'"></span>
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

    {{-- 插入图片弹窗（上传本地图片 / 粘贴链接） --}}
    @include('components.image-insert-modal')
  </div>

  @include('components.article-editor-script')
</body>
</html>
