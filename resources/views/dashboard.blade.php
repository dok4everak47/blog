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

            {{-- 站点设置：About 页面内容（富文本编辑） --}}
            <div class="rounded-2xl border border-border bg-surface-2 p-5 sm:p-6" x-data="aboutEditor({{ Illuminate\Support\Js::from($aboutHtml) }})">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-text">About 页面内容</p>
                        <p class="text-xs text-text-secondary mt-0.5">使用富文本编辑器编写「关于」页面内容</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('about') }}" target="_blank" class="text-xs text-primary hover:text-primary-hover transition flex items-center gap-1">
                            预览页面
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>
                </div>

                {{-- TipTap 工具栏 --}}
                <div class="flex flex-wrap items-center gap-1 p-2 rounded-lg bg-surface border border-border mb-2">
                    <button type="button" @click="editor.chain().focus().toggleBold().run()"
                            :class="{ 'bg-primary-light text-primary': editor.isActive('bold') }"
                            class="w-8 h-8 flex items-center justify-center rounded text-sm font-bold text-text-secondary hover:bg-surface-2 transition" title="粗体 (Ctrl+B)">
                        B
                    </button>
                    <button type="button" @click="editor.chain().focus().toggleItalic().run()"
                            :class="{ 'bg-primary-light text-primary': editor.isActive('italic') }"
                            class="w-8 h-8 flex items-center justify-center rounded text-sm italic text-text-secondary hover:bg-surface-2 transition" title="斜体 (Ctrl+I)">
                        I
                    </button>
                    <button type="button" @click="editor.chain().focus().toggleStrike().run()"
                            :class="{ 'bg-primary-light text-primary': editor.isActive('strike') }"
                            class="w-8 h-8 flex items-center justify-center rounded text-sm line-through text-text-secondary hover:bg-surface-2 transition" title="删除线">
                        S
                    </button>

                    <div class="w-px h-5 bg-border mx-1"></div>

                    <button type="button" @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                            :class="{ 'bg-primary-light text-primary': editor.isActive('heading', { level: 2 }) }"
                            class="h-8 px-2 flex items-center justify-center rounded text-xs font-bold text-text-secondary hover:bg-surface-2 transition" title="标题 H2">
                        H2
                    </button>
                    <button type="button" @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
                            :class="{ 'bg-primary-light text-primary': editor.isActive('heading', { level: 3 }) }"
                            class="h-8 px-2 flex items-center justify-center rounded text-xs font-bold text-text-secondary hover:bg-surface-2 transition" title="标题 H3">
                        H3
                    </button>

                    <div class="w-px h-5 bg-border mx-1"></div>

                    <button type="button" @click="editor.chain().focus().toggleBulletList().run()"
                            :class="{ 'bg-primary-light text-primary': editor.isActive('bulletList') }"
                            class="w-8 h-8 flex items-center justify-center rounded text-text-secondary hover:bg-surface-2 transition" title="无序列表">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <button type="button" @click="editor.chain().focus().toggleOrderedList().run()"
                            :class="{ 'bg-primary-light text-primary': editor.isActive('orderedList') }"
                            class="w-8 h-8 flex items-center justify-center rounded text-text-secondary hover:bg-surface-2 transition" title="有序列表">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                    </button>
                    <button type="button" @click="editor.chain().focus().toggleBlockquote().run()"
                            :class="{ 'bg-primary-light text-primary': editor.isActive('blockquote') }"
                            class="w-8 h-8 flex items-center justify-center rounded text-sm text-text-secondary hover:bg-surface-2 transition" title="引用">
                        ❝
                    </button>

                    <div class="w-px h-5 bg-border mx-1"></div>

                    <button type="button" @click="editor.chain().focus().setHorizontalRule().run()"
                            class="w-8 h-8 flex items-center justify-center rounded text-text-secondary hover:bg-surface-2 transition" title="分割线">
                        —
                    </button>
                    <button type="button" @click="insertLink()"
                            :class="{ 'bg-primary-light text-primary': editor.isActive('link') }"
                            class="w-8 h-8 flex items-center justify-center rounded text-text-secondary hover:bg-surface-2 transition" title="插入链接">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    </button>
                </div>

                {{-- 编辑区域 --}}
                <div id="about-editor" class="min-h-[300px] max-h-[600px] overflow-y-auto rounded-lg border border-border bg-white dark:bg-gray-900 focus-within:border-primary transition-colors prose prose-sm max-w-none">
                </div>

                {{-- 字数统计 + 状态 --}}
                <div class="flex items-center justify-between mt-3 pt-3 border-t border-border">
                    <span class="text-[11px] text-text-muted" x-text="'约 ' + charCount + ' 字'"></span>
                    <div class="flex items-center gap-3">
                        <template x-if="msg">
                            <p class="text-xs" :class="msgType === 'error' ? 'text-red-600' : 'text-green-600'" x-text="msg"></p>
                        </template>
                        <button type="button" @click="save()" :disabled="saving || !isDirty"
                                class="px-5 py-2 rounded-lg text-sm font-medium transition disabled:opacity-40"
                                :class="isDirty ? 'bg-primary text-white hover:bg-primary-hover' : 'bg-surface text-text-muted'">
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
                                        class="text-xs text-primary hover:text-primary-hover transition"
                                        @click="openModal($event.currentTarget)">换封面</button>
                                    <a href="{{ route('notes.edit', $note) }}"
                                        class="text-xs text-primary hover:text-primary-hover transition">编辑</a>
                                    <form action="{{ route('notes.destroy', $note) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-600 hover:text-red-700 transition"
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
                    if (this.preview && this.preview.startsWith('blob:')) {
                        URL.revokeObjectURL(this.preview);
                    }
                    this.preview = URL.createObjectURL(f);
                    this.file = f;
                    this.removeFlag = false;
                    this.errors = [];
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
                    // 预览
                    if (this.currentUrl && this.currentUrl.startsWith('blob:')) {
                        URL.revokeObjectURL(this.currentUrl);
                    }
                    this.currentUrl = URL.createObjectURL(f);
                    this.file = f;
                    this.error = null;
                    this.success = null;
                    // 自动上传
                    this.save();
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

        window.aboutEditor = function (initialHtml) {
            return {
                editor: null,
                saving: false,
                msg: null,
                msgType: '',
                isDirty: false,
                charCount: 0,

                init() {
                    // 使用 Vite 预构建的 TipTap 模块
                    if (typeof window.initAboutEditor === 'function') {
                        this.editor = window.initAboutEditor({
                            elementId: 'about-editor',
                            content: initialHtml || '',
                            onUpdate: function() {
                                this.isDirty = true;
                                this.updateCharCount();
                            }.bind(this),
                        });
                        this.updateCharCount();
                    } else {
                        console.error('[aboutEditor] TipTap 模块未加载');
                    }
                },

                updateCharCount() {
                    if (this.editor) {
                        var text = this.editor.getText();
                        this.charCount = text.replace(/\s/g, '').length;
                    }
                },

                insertLink() {
                    if (!this.editor) return;
                    var url = prompt('请输入链接地址：', 'https://');
                    if (url) {
                        this.editor.chain().focus().setLink({ href: url }).run();
                    }
                },

                async save() {
                    if (!this.editor || !this.isDirty) return;
                    this.saving = true;
                    this.msg = null;
                    var html = this.editor.getHTML();
                    var token = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    try {
                        const res = await fetch('/settings/about-content', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': token, 'Content-Type': 'application/json' },
                            body: JSON.stringify({ html: html }),
                        });
                        if (!res.ok) {
                            const d = await res.json().catch(() => ({}));
                            this.msg = Object.values(d.errors || {}).flat().join('；') || '保存失败';
                            this.msgType = 'error';
                        } else {
                            this.msg = '已保存';
                            this.msgType = 'success';
                            this.isDirty = false;
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
</x-app-layout>
