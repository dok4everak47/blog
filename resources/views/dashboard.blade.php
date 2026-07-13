<x-app-layout>
    <x-slot name="header">
        <div class="max-w-6xl mx-auto">
            <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-1">Dashboard</p>
            <h2 class="font-bold text-xl text-text leading-tight">
                {{ __('后台管理') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12 bg-bg">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            {{-- Welcome --}}
            <div class="rounded-2xl border border-border bg-surface-2 p-6 sm:p-8">
                <h1 class="text-2xl font-bold text-text">
                    你好，{{ Auth::user()->name }} 👋
                </h1>
                <p class="mt-2 text-text-secondary">
                    欢迎回到 Dashboard，从这里管理你的博客内容。
                </p>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="rounded-2xl border border-border bg-surface-2 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-text-secondary">文章总数</p>
                            <p class="text-3xl font-bold text-text mt-1">{{ $notesCount }}</p>
                        </div>
                        <span class="text-3xl">📝</span>
                    </div>
                </div>
                <div class="rounded-2xl border border-border bg-surface-2 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-text-secondary">分类数</p>
                            <p class="text-3xl font-bold text-text mt-1">{{ $categoriesCount }}</p>
                        </div>
                        <span class="text-3xl">📁</span>
                    </div>
                </div>
                <div class="rounded-2xl border border-border bg-surface-2 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-text-secondary">标签数</p>
                            <p class="text-3xl font-bold text-text mt-1">{{ $tagsCount }}</p>
                        </div>
                        <span class="text-3xl">🏷️</span>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="rounded-2xl border border-border bg-surface-2 p-6 sm:p-8">
                <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-4">快捷操作</p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('notes.create') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:bg-primary-hover hover:shadow-md hover:-translate-y-px active:translate-y-0 active:scale-[0.98]">
                        ✍️ 写文章
                    </a>
                    <a href="{{ route('profile.edit') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-border bg-surface px-5 py-2.5 text-sm font-medium text-text transition-all duration-200 hover:bg-white hover:border-border-strong hover:-translate-y-px">
                        ⚙️ 个人资料
                    </a>
                    <a href="{{ route('home') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-border bg-surface px-5 py-2.5 text-sm font-medium text-text transition-all duration-200 hover:bg-white hover:border-border-strong hover:-translate-y-px">
                        🏠 查看博客
                    </a>
                </div>
            </div>

            {{-- 站点设置：Hero 背景图 --}}
            <div class="rounded-2xl border border-border bg-surface-2 p-5 sm:p-6" x-data="heroImageManager({{ Illuminate\Support\Js::from($heroImage) }})">
                <div class="flex items-center gap-4">
                    {{-- 缩略预览 --}}
                    <div class="relative w-28 h-16 rounded-lg overflow-hidden border border-border shrink-0 bg-surface-2">
                        <template x-if="currentUrl">
                            <img :src="currentUrl" alt="Hero 背景图" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!currentUrl">
                            <div class="w-full h-full flex items-center justify-center text-text-muted text-[10px] leading-tight text-center px-1">
                                自动取文章图
                            </div>
                        </template>
                    </div>

                    {{-- 信息 + 操作（紧凑一行） --}}
                    <div class="flex-1 min-w-0 flex items-center gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-text truncate">首页 Hero 背景图</p>
                            <p class="text-xs text-text-secondary mt-0.5 hidden sm:block">不设置时自动使用最新文章封面</p>
                        </div>

                        <div class="flex items-center gap-2 shrink-0 ml-auto">
                            <input type="file" accept="image/*" class="hidden" x-ref="heroFile"
                                   @change="onFileChange($event)">
                            <button type="button"
                                    class="px-3 py-1.5 rounded-lg bg-primary text-xs font-medium text-white hover:bg-primary-hover transition disabled:opacity-50 whitespace-nowrap"
                                    :disabled="uploading"
                                    @click="$refs.heroFile.click()">
                                <span x-show="!uploading">上传</span>
                                <span x-show="uploading" x-cloak>上传中…</span>
                            </button>
                            <button type="button"
                                    class="px-3 py-1.5 rounded-lg border border-border text-xs font-medium text-text hover:bg-surface transition whitespace-nowrap"
                                    x-show="currentUrl"
                                    @click="remove()">
                                移除
                            </button>
                        </div>
                    </div>
                </div>
                {{-- 提示消息 --}}
                <template x-if="error">
                    <p class="text-xs text-red-600 mt-2" x-text="error"></p>
                </template>
                <template x-if="success">
                    <p class="text-xs text-green-600 mt-2" x-text="success"></p>
                </template>
            </div>

            {{-- 站点设置：About 页面内容（Markdown 编辑器） --}}
            <div class="rounded-2xl border border-border bg-surface-2 p-6" x-data="aboutMarkdownEditor({{ Illuminate\Support\Js::from($aboutMarkdown) }})">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-text">About 页面内容</p>
                        <p class="text-xs text-text-secondary mt-0.5">使用 Markdown 编写「关于」页面，支持实时预览</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="md-viewtoggle" role="group" aria-label="视图模式">
                            <button type="button" :class="{ 'active': viewMode === 'edit' }" @click="viewMode = 'edit'">编辑</button>
                            <button type="button" :class="{ 'active': viewMode === 'split' }" @click="setViewMode('split')">分屏</button>
                            <button type="button" :class="{ 'active': viewMode === 'preview' }" @click="setViewMode('preview')">预览</button>
                        </div>
                        <a href="{{ route('about') }}" target="_blank" class="text-xs text-primary hover:text-primary-hover transition flex items-center gap-1 shrink-0">
                            预览页面
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>
                </div>

                {{-- 工具栏：不顶边，普通按钮组样式 --}}
                <div class="md-toolbar md-toolbar-contained mb-3" role="toolbar" aria-label="Markdown 工具栏">
                    <div class="md-toolbar-group">
                        <button type="button" class="md-btn md-text" title="粗体" @click="wrapSelection('**', '**', '粗体文字')"><b>B</b></button>
                        <button type="button" class="md-btn md-text" title="斜体" @click="wrapSelection('*', '*', '斜体文字')"><i>I</i></button>
                        <button type="button" class="md-btn md-text" title="删除线" @click="wrapSelection('~~', '~~', '删除文字')"><s>S</s></button>
                        <button type="button" class="md-btn md-text" title="行内代码" @click="wrapSelection('`', '`', '代码')">&lt;/&gt;</button>
                    </div>
                    <span class="md-sep"></span>
                    <div class="md-toolbar-group">
                        <button type="button" class="md-btn md-text" title="H2" @click="toggleHeading(2)">H2</button>
                        <button type="button" class="md-btn md-text" title="H3" @click="toggleHeading(3)">H3</button>
                    </div>
                    <span class="md-sep"></span>
                    <div class="md-toolbar-group">
                        <button type="button" class="md-btn" title="无序列表" @click="toggleLinePrefix('- ')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6h12M9 12h12M9 18h12"/><circle cx="4" cy="6" r="1.4" fill="currentColor" stroke="none"/><circle cx="4" cy="12" r="1.4" fill="currentColor" stroke="none"/><circle cx="4" cy="18" r="1.4" fill="currentColor" stroke="none"/></svg>
                        </button>
                        <button type="button" class="md-btn" title="有序列表" @click="toggleOrderedList()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 6h11M10 12h11M10 18h11"/><text x="2" y="8.5" font-size="7" fill="currentColor" stroke="none">1</text><text x="2" y="14.5" font-size="7" fill="currentColor" stroke="none">2</text><text x="2" y="20.5" font-size="7" fill="currentColor" stroke="none">3</text></svg>
                        </button>
                        <button type="button" class="md-btn" title="引用" @click="toggleLinePrefix('> ')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21c3 0 7-1 7-8V5H3v8h4M14 21c3 0 7-1 7-8V5h-7v8h4"/></svg>
                        </button>
                    </div>
                    <span class="md-sep"></span>
                    <div class="md-toolbar-group">
                        <button type="button" class="md-btn" title="插入链接" @click="insertLink()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7 0l2-2a5 5 0 0 0-7-7l-1 1"/><path d="M14 11a5 5 0 0 0-7 0l-2 2a5 5 0 0 0 7 7l1-1"/></svg>
                        </button>
                        <button type="button" class="md-btn" title="插入图片" @click="insertImage()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="M21 16l-5-5-6 6"/></svg>
                        </button>
                        <button type="button" class="md-btn" title="分割线" @click="insertHr()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12h16"/></svg>
                        </button>
                        <button type="button" class="md-btn" title="代码块" @click="insertCodeBlock()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9.5 9.5l-3 3 3 3M14.5 9.5l3 3-3 3"/></svg>
                        </button>
                    </div>
                </div>

                {{-- 编辑区 + 预览 --}}
                <div class="editor-body" :class="viewMode">
                    <textarea x-ref="mdTextarea"
                        class="editor-textarea min-h-[400px]"
                        placeholder="用 Markdown 编写你的 About 页面内容…"
                        x-model="markdown"
                        @input="onInput()"></textarea>
                    <div class="md-preview article-content" x-show="viewMode !== 'edit'" x-cloak x-html="previewHtml"></div>
                </div>

                {{-- 底部状态栏 --}}
                <div class="flex items-center justify-between mt-3 pt-3 border-t border-border">
                    <span class="text-[11px] text-text-muted" x-text="'约 ' + charCount + ' 字'"></span>
                    <div class="flex items-center gap-3">
                        <template x-if="msg">
                            <p class="text-xs" :class="msgType === 'error' ? 'text-red-600' : 'text-green-600'" x-text="msg"></p>
                        </template>
                        <button type="button" @click="save()" :disabled="saving || !dirty"
                            class="px-5 py-2 rounded-lg text-sm font-medium transition disabled:opacity-40"
                            :class="dirty ? 'bg-primary text-white hover:bg-primary-hover' : 'bg-surface text-text-muted'">
                            <span x-show="!saving">保存修改</span>
                            <span x-show="saving" x-cloak>保存中…</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Recent Notes --}}
            <div class="rounded-2xl border border-border bg-surface-2 p-6 sm:p-8" x-data="coverManager()" x-cloak>
                <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-4">最近文章</p>

                @if ($notes->isNotEmpty())
                    <div class="space-y-3">
                        @foreach ($notes as $note)
                            <div class="flex items-center justify-between p-3 rounded-xl bg-surface border border-border hover:border-border-strong transition">
                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                    {{-- 封面缩略图（有封面显示 img，无封面显示占位，互斥） --}}
                                    <img src="{{ $note->cover_image_url ?? '' }}"
                                         alt="封面"
                                         data-cover-thumb="{{ $note->id }}"
                                         class="w-12 h-12 rounded-lg object-cover shrink-0 border border-border {{ $note->cover_image_url ? '' : 'hidden' }}">
                                    <div data-cover-placeholder="{{ $note->id }}"
                                         class="w-12 h-12 rounded-lg bg-surface-2 border border-border flex items-center justify-center shrink-0 text-text-muted text-xs {{ $note->cover_image_url ? 'hidden' : '' }}">
                                        无
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('notes.show', $note) }}"
                                                class="text-sm font-medium text-text hover:text-primary transition truncate">
                                                {{ \Illuminate\Support\Str::limit($note->title, 40) }}
                                            </a>
                                            @if ($note->isDraft())
                                                <span class="shrink-0 rounded-full bg-gold-light px-2 py-0.5 text-xs font-medium text-gold-hover">草稿</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-text-secondary mt-0.5">
                                            {{ $note->created_at->format('Y-m-d') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 ml-4">
                                    <button type="button"
                                        data-note-id="{{ $note->id }}"
                                        data-note-title="{{ $note->title }}"
                                        data-cover-url="{{ $note->cover_image_url ?? '' }}"
                                        class="text-sm font-medium text-primary hover:text-primary-hover transition"
                                        @click="openModal($event.currentTarget)">换封面</button>
                                    <a href="{{ route('notes.edit', $note) }}"
                                        class="text-sm font-medium text-primary hover:text-primary-hover transition">编辑</a>
                                    <form action="{{ route('notes.destroy', $note) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700 transition"
                                            onclick="return confirm('确定要删除这篇文章吗？')">删除</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-text-secondary text-center py-6">
                        还没有文章，<a href="{{ route('notes.create') }}" class="text-primary hover:text-primary-hover">去写一篇吧</a>
                    </p>
                @endif

                {{-- 更换封面弹窗 --}}
                <div x-show="modalOpen" x-cloak
                     class="fixed inset-0 z-50 flex items-center justify-center p-4"
                     x-transition.opacity>
                    <div class="absolute inset-0 bg-black/50" @click="closeModal()"></div>
                    <div class="relative bg-surface-2 rounded-2xl shadow-xl w-full max-w-md p-6" @click.stop>
                        <h3 class="font-bold text-lg text-text mb-1">更换封面</h3>
                        <p class="text-sm text-text-secondary mb-4 truncate" x-text="currentTitle"></p>

                        {{-- 预览 --}}
                        <div class="mb-4">
                            <template x-if="preview">
                                <img :src="preview" class="w-full h-40 object-cover rounded-xl border border-border">
                            </template>
                            <template x-if="!preview">
                                <div class="w-full h-40 rounded-xl bg-surface-2 border border-dashed border-border flex items-center justify-center text-text-muted text-sm">
                                    暂无封面
                                </div>
                            </template>
                        </div>

                        {{-- 选择文件 --}}
                        <input type="file" accept="image/*" class="hidden" x-ref="coverFile"
                               @change="onFileChange($event)">
                        <button type="button"
                                class="w-full py-2.5 rounded-lg border border-border bg-surface text-sm font-medium text-text hover:bg-surface-2 transition"
                                @click="$refs.coverFile.click()">
                            选择图片
                        </button>

                        <template x-if="errors.length">
                            <p class="text-xs text-red-600 mt-2" x-text="errors[0]"></p>
                        </template>

                        <div class="flex items-center gap-3 mt-5">
                            <button type="button"
                                    class="text-xs text-red-600 hover:text-red-700 transition"
                                    x-show="preview" @click="removeCover()">移除封面</button>
                            <div class="flex-1"></div>
                            <button type="button"
                                    class="px-4 py-2 rounded-lg text-sm text-text-secondary hover:bg-surface transition"
                                    @click="closeModal()">取消</button>
                            <button type="button"
                                    class="px-4 py-2 rounded-lg bg-primary text-sm font-medium text-white hover:bg-primary-hover transition disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="uploading || (!file && !removeFlag)"
                                    @click="save()">
                                <span x-show="uploading" x-cloak>保存中…</span>
                                <span x-show="!uploading">保存</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Flash --}}
            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
                    class="fixed bottom-6 right-6 rounded-xl bg-primary px-6 py-3 text-sm font-medium text-white shadow-md">
                    {{ session('success') }}
                </div>
            @endif
        </div>
    </div>

    <script>
        window.coverManager = function () {
            return {
                modalOpen: false,
                currentNoteId: null,
                currentTitle: '',
                preview: null,
                file: null,
                removeFlag: false,
                uploading: false,
                errors: [],

                openModal(el) {
                    this.currentNoteId = parseInt(el.dataset.noteId);
                    this.currentTitle = el.dataset.noteTitle || '';
                    this.preview = el.dataset.coverUrl || null;
                    this.file = null;
                    this.removeFlag = false;
                    this.errors = [];
                    this.modalOpen = true;
                },

                closeModal() {
                    this.modalOpen = false;
                },

                onFileChange(e) {
                    const f = e.target.files && e.target.files[0];
                    if (!f) return;
                    window.openImageCropper(f).then(croppedBlob => {
                        if (this.preview && this.preview.startsWith('blob:')) {
                            URL.revokeObjectURL(this.preview);
                        }
                        this.preview = URL.createObjectURL(croppedBlob);
                        this.file = croppedBlob;
                        this.removeFlag = false;
                        this.errors = [];
                    }).catch(() => {
                        e.target.value = '';
                    });
                },

                removeCover() {
                    if (this.preview && this.preview.startsWith('blob:')) {
                        URL.revokeObjectURL(this.preview);
                    }
                    this.preview = null;
                    this.file = null;
                    this.removeFlag = true;
                },

                async save() {
                    if (!this.file && !this.removeFlag) return;
                    this.uploading = true;
                    this.errors = [];
                    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    const fd = new FormData();
                    if (this.file) fd.append('cover_image', this.file);
                    if (this.removeFlag) fd.append('remove_cover', '1');
                    try {
                        const res = await fetch(`/notes/${this.currentNoteId}/cover`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: fd,
                        });
                        if (!res.ok) {
                            const data = await res.json().catch(() => ({}));
                            this.errors = data.errors ? Object.values(data.errors).flat() : ['上传失败，请重试'];
                            this.uploading = false;
                            return;
                        }
                        const data = await res.json();
                        this.updateThumb(this.currentNoteId, data.cover_url);
                        this.closeModal();
                    } catch (e) {
                        this.errors = ['上传失败，请重试'];
                    } finally {
                        this.uploading = false;
                    }
                },

                updateThumb(id, coverUrl) {
                    const img = document.querySelector(`[data-cover-thumb="${id}"]`);
                    const ph = document.querySelector(`[data-cover-placeholder="${id}"]`);
                    if (coverUrl) {
                        if (img) {
                            img.src = coverUrl;
                            img.classList.remove('hidden');
                        }
                        if (ph) ph.classList.add('hidden');
                    } else {
                        if (img) img.classList.add('hidden');
                        if (ph) ph.classList.remove('hidden');
                    }
                },
            };
        };

        window.heroImageManager = function (initialUrl) {
            return {
                currentUrl: initialUrl || null,
                file: null,
                uploading: false,
                error: null,
                success: null,

                onFileChange(e) {
                    const f = e.target.files && e.target.files[0];
                    if (!f) return;
                    window.openImageCropper(f).then(croppedBlob => {
                        if (!croppedBlob) {
                            this.error = '裁剪失败，未获取到图片';
                            e.target.value = '';
                            return;
                        }
                        if (this.currentUrl && this.currentUrl.startsWith('blob:')) {
                            URL.revokeObjectURL(this.currentUrl);
                        }
                        this.currentUrl = URL.createObjectURL(croppedBlob);
                        this.file = croppedBlob;
                        this.error = null;
                        this.success = null;
                        this.save();
                    }).catch((err) => {
                        if (err && err.message && err.message.includes('取消')) return;
                        this.error = (err && err.message) ? err.message : '图片处理失败';
                        e.target.value = '';
                    });
                },

                async remove() {
                    this.uploading = true;
                    this.error = null;
                    this.success = null;
                    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    const fd = new FormData();
                    fd.append('remove', '1');
                    try {
                        const res = await fetch('/settings/hero-image', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: fd,
                        });
                        if (!res.ok) {
                            const data = await res.json().catch(() => ({}));
                            this.error = data.message || '移除失败，请重试';
                            return;
                        }
                        this.currentUrl = null;
                        this.success = '已移除';
                        setTimeout(() => { this.success = null; }, 3000);
                    } catch (e) {
                        this.error = '移除失败，请重试';
                    } finally {
                        this.uploading = false;
                    }
                },

                async save() {
                    if (!this.file) return;
                    this.uploading = true;
                    this.error = null;
                    this.success = null;
                    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    const fd = new FormData();
                    fd.append('image', this.file);
                    try {
                        const res = await fetch('/settings/hero-image', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: fd,
                        });
                        if (!res.ok) {
                            const data = await res.json().catch(() => ({}));
                            const msg = data.message || ('errors' in data ? Object.values(data.errors).flat()[0] : '上传失败');
                            this.error = msg;
                            return;
                        }
                        const data = await res.json();
                        // 切换 blob 预览为真实 URL
                        if (this.currentUrl && this.currentUrl.startsWith('blob:')) {
                            URL.revokeObjectURL(this.currentUrl);
                        }
                        this.currentUrl = data.url;
                        this.file = null;
                        this.success = data.message || '更新成功';
                        setTimeout(() => { this.success = null; }, 3000);
                    } catch (e) {
                        this.error = '上传失败，请重试';
                    } finally {
                        this.uploading = false;
                    }
                },
            };
        };

        // 上传按钮自动触发保存（与 x-data 内联）
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[x-data*="heroImageManager"]').forEach(el => {
                // Alpine 会处理，无需额外绑定
            });
        });

        window.aboutMarkdownEditor = function (initialMarkdown) {
            return {
                markdown: initialMarkdown || '',
                viewMode: 'edit',
                previewHtml: '',
                saving: false,
                dirty: false,
                msg: null,
                msgType: '',
                _previewTimer: null,

                get charCount() {
                    return (this.markdown || '').replace(/\s/g, '').length;
                },

                get ta() {
                    return this.$refs.mdTextarea;
                },

                onInput() {
                    this.dirty = true;
                    if (this.viewMode !== 'edit') {
                        clearTimeout(this._previewTimer);
                        this._previewTimer = setTimeout(() => this.renderPreview(), 250);
                    }
                },

                setViewMode(mode) {
                    this.viewMode = mode;
                    if (mode !== 'edit') this.renderPreview();
                },

                renderPreview() {
                    if (typeof window.marked === 'undefined') {
                        this.previewHtml = '<pre style="padding:1rem;background:#f5f5f5;border-radius:8px;overflow:auto;font-size:13px;">' + this.escapeHtml(this.markdown || '') + '</pre>';
                        return;
                    }
                    const normalized = (this.markdown || '')
                        .replace(/!\s+\[([^\]]+)\]\s*\(/g, '![$1](')
                        .replace(/!\[([^\]]+)\]\s+\(/g, '![$1](');
                    let html = window.marked.parse(normalized, { gfm: true, breaks: true });
                    if (window.DOMPurify) html = window.DOMPurify.sanitize(html);
                    this.previewHtml = html;
                },

                escapeHtml(s) {
                    return (s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                },

                // ---- 工具栏操作 ----

                setTextareaValue(newVal, selStart, selEnd) {
                    const ta = this.ta;
                    if (!ta) return;
                    ta.value = newVal;
                    this.markdown = newVal;
                    ta.focus();
                    if (typeof selStart === 'number') {
                        ta.setSelectionRange(selStart, selEnd == null ? selStart : selEnd);
                    }
                    this.onInput();
                },

                wrapSelection(before, after, placeholder) {
                    const ta = this.ta;
                    if (!ta) return;
                    const val = ta.value;
                    const start = ta.selectionStart, end = ta.selectionEnd;
                    const selected = val.slice(start, end) || placeholder || '';
                    const newVal = val.slice(0, start) + before + selected + after + val.slice(end);
                    const cursorStart = start + before.length;
                    const cursorEnd = cursorStart + selected.length;
                    this.setTextareaValue(newVal, cursorStart, cursorEnd);
                },

                toggleLinePrefix(prefix) {
                    const ta = this.ta;
                    if (!ta) return;
                    const val = ta.value;
                    const start = ta.selectionStart, end = ta.selectionEnd;
                    const lineStart = val.lastIndexOf('\n', start - 1) + 1;
                    let lineEnd = val.indexOf('\n', end);
                    if (lineEnd === -1) lineEnd = val.length;
                    const block = val.slice(lineStart, lineEnd);
                    const lines = block.split('\n');
                    const allHave = lines.length > 0 && lines.every(l => l.startsWith(prefix) || l.trim() === '');
                    const newLines = allHave
                        ? lines.map(l => l.startsWith(prefix) ? l.slice(prefix.length) : l)
                        : lines.map(l => l.startsWith(prefix) ? l : prefix + l);
                    const newBlock = newLines.join('\n');
                    const newVal = val.slice(0, lineStart) + newBlock + val.slice(lineEnd);
                    this.setTextareaValue(newVal, lineStart, lineStart + newBlock.length);
                },

                toggleOrderedList() {
                    const ta = this.ta;
                    if (!ta) return;
                    const val = ta.value;
                    const start = ta.selectionStart, end = ta.selectionEnd;
                    const lineStart = val.lastIndexOf('\n', start - 1) + 1;
                    let lineEnd = val.indexOf('\n', end);
                    if (lineEnd === -1) lineEnd = val.length;
                    const block = val.slice(lineStart, lineEnd);
                    const lines = block.split('\n');
                    const re = /^\d+\.\s/;
                    const allNum = lines.length > 0 && lines.every(l => re.test(l) || l.trim() === '');
                    const newLines = allNum
                        ? lines.map(l => re.test(l) ? l.replace(re, '') : l)
                        : lines.map((l, i) => re.test(l) ? l : (i + 1) + '. ' + l);
                    const newBlock = newLines.join('\n');
                    const newVal = val.slice(0, lineStart) + newBlock + val.slice(lineEnd);
                    this.setTextareaValue(newVal, lineStart, lineStart + newBlock.length);
                },

                toggleHeading(level) {
                    const ta = this.ta;
                    if (!ta) return;
                    const val = ta.value;
                    const start = ta.selectionStart, end = ta.selectionEnd;
                    const lineStart = val.lastIndexOf('\n', start - 1) + 1;
                    let lineEnd = val.indexOf('\n', end);
                    if (lineEnd === -1) lineEnd = val.length;
                    const block = val.slice(lineStart, lineEnd);
                    const hashes = '#'.repeat(level);
                    const m = block.match(/^#{1,6}\s+/);
                    let newBlock;
                    if (m && m[0].trim() === hashes) {
                        newBlock = block.slice(m[0].length);
                    } else if (m) {
                        newBlock = hashes + ' ' + block.slice(m[0].length);
                    } else {
                        newBlock = hashes + ' ' + block;
                    }
                    const newVal = val.slice(0, lineStart) + newBlock + val.slice(lineEnd);
                    this.setTextareaValue(newVal, lineStart, lineStart + newBlock.length);
                },

                insertLink() {
                    const ta = this.ta;
                    if (!ta) return;
                    const start = ta.selectionStart, end = ta.selectionEnd;
                    const selected = ta.value.slice(start, end) || '链接文字';
                    const url = window.prompt('请输入链接地址：', 'https://');
                    if (url === null) return;
                    const md = '[' + selected + '](' + url + ')';
                    const newVal = ta.value.slice(0, start) + md + ta.value.slice(end);
                    this.setTextareaValue(newVal, start + md.length, start + md.length);
                },

                insertImage() {
                    const ta = this.ta;
                    if (!ta) return;
                    const url = window.prompt('请输入图片地址：', 'https://');
                    if (url === null) return;
                    const md = '![](' + url + ')';
                    const start = ta.selectionStart;
                    const newVal = ta.value.slice(0, start) + md + ta.value.slice(start);
                    this.setTextareaValue(newVal, start + md.length, start + md.length);
                },

                insertHr() {
                    const ta = this.ta;
                    if (!ta) return;
                    const val = ta.value;
                    const start = ta.selectionStart;
                    const before = val.slice(0, start), after = val.slice(start);
                    const pre = before && !before.endsWith('\n') ? '\n\n' : (before && !before.endsWith('\n\n') ? '\n' : '');
                    const post = after && !after.startsWith('\n') ? '\n\n' : '';
                    const text = pre + '---' + post;
                    const newVal = before + text + after;
                    this.setTextareaValue(newVal, before.length + text.length, before.length + text.length);
                },

                insertCodeBlock() {
                    const ta = this.ta;
                    if (!ta) return;
                    const val = ta.value;
                    const start = ta.selectionStart, end = ta.selectionEnd;
                    const selected = val.slice(start, end) || '在此输入代码';
                    const fence = '\n```\n' + selected + '\n```\n';
                    const newVal = val.slice(0, start) + fence + val.slice(end);
                    const innerStart = start + 5; // skip \n```\n
                    this.setTextareaValue(newVal, innerStart, innerStart + selected.length);
                },

                // ---- 保存 ----

                async save() {
                    if (!this.dirty) return;
                    this.saving = true;
                    this.msg = null;
                    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    try {
                        const res = await fetch('/settings/about-content', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ markdown: this.markdown }),
                        });
                        if (!res.ok) {
                            const d = await res.json().catch(() => ({}));
                            this.msg = Object.values(d.errors || {}).flat().join('；') || '保存失败';
                            this.msgType = 'error';
                        } else {
                            this.msg = '已保存';
                            this.msgType = 'success';
                            this.dirty = false;
                            setTimeout(() => { this.msg = null; }, 3000);
                        }
                    } catch (e) {
                        this.msg = '网络错误，请重试';
                        this.msgType = 'error';
                    } finally {
                        this.saving = false;
                    }
                },
            };
        };
    </script>

    @push('scripts')
        @vite(['resources/js/editor.js'])
    @endpush
</x-app-layout>
