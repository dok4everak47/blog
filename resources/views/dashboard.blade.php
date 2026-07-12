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
            <div class="rounded-2xl border border-border bg-white p-6 sm:p-8">
                <h1 class="text-2xl font-bold text-text">
                    你好，{{ Auth::user()->name }} 👋
                </h1>
                <p class="mt-2 text-text-secondary">
                    欢迎回到 Dashboard，从这里管理你的博客内容。
                </p>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="rounded-2xl border border-border bg-white p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-text-secondary">文章总数</p>
                            <p class="text-3xl font-bold text-text mt-1">{{ $notesCount }}</p>
                        </div>
                        <span class="text-3xl">📝</span>
                    </div>
                </div>
                <div class="rounded-2xl border border-border bg-white p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-text-secondary">分类数</p>
                            <p class="text-3xl font-bold text-text mt-1">{{ $categoriesCount }}</p>
                        </div>
                        <span class="text-3xl">📁</span>
                    </div>
                </div>
                <div class="rounded-2xl border border-border bg-white p-6">
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
            <div class="rounded-2xl border border-border bg-white p-6 sm:p-8">
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
            <div class="rounded-2xl border border-border bg-white p-6 sm:p-8" x-data="heroImageManager({{ Illuminate\Support\Js::from($heroImage) }})">
                <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-4">站点设置</p>
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    {{-- 当前 Hero 背景图预览 --}}
                    <div class="relative w-full sm:w-48 h-28 rounded-xl overflow-hidden border border-border shrink-0 bg-surface-2">
                        <template x-if="currentUrl">
                            <img :src="currentUrl" alt="Hero 背景图" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!currentUrl">
                            <div class="w-full h-full flex items-center justify-center text-text-muted text-xs">
                                未设置（自动取最新文章）
                            </div>
                        </template>
                    </div>

                    {{-- 操作区 --}}
                    <div class="flex-1 space-y-3 w-full">
                        <p class="text-sm text-text-secondary">
                            首页全屏 Hero 区域的背景图。不设置时自动使用最新文章的封面图。
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <input type="file" accept="image/*" class="hidden" x-ref="heroFile"
                                   @change="onFileChange($event)">
                            <button type="button"
                                    class="px-4 py-2 rounded-lg bg-primary text-sm font-medium text-white hover:bg-primary-hover transition disabled:opacity-50"
                                    :disabled="uploading"
                                    @click="$refs.heroFile.click()">
                                <span x-show="!uploading">上传背景图</span>
                                <span x-show="uploading" x-cloak>上传中…</span>
                            </button>
                            <button type="button"
                                    class="px-4 py-2 rounded-lg border border-border text-sm font-medium text-text hover:bg-surface transition"
                                    x-show="currentUrl"
                                    @click="remove()">
                                移除
                            </button>
                        </div>
                        <template x-if="error">
                            <p class="text-xs text-red-600" x-text="error"></p>
                        </template>
                        <template x-if="success">
                            <p class="text-xs text-green-600" x-text="success"></p>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Recent Notes --}}
            <div class="rounded-2xl border border-border bg-white p-6 sm:p-8" x-data="coverManager()" x-cloak>
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
                    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6" @click.stop>
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
                                class="w-full py-2.5 rounded-lg border border-border bg-surface text-sm font-medium text-text hover:bg-white transition"
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
                    try {
                        const res = await fetch('/settings/hero-image', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({ remove: true }),
                        });
                        if (!res.ok) throw new Error();
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
    </script>
</x-app-layout>
