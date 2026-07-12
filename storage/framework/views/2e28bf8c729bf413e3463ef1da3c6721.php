
<script>
    window.articleEditor = function () {
        return {
            // 数据
            allTags: [],
            selectedTags: [],
            noteId: null,
            title: '',
            content: '',
            categoryId: '',
            slug: '',
            slugEdited: false,

            // 新建分类
            newCategoryOpen: false,
            newCategoryName: '',
            catSaving: false,

            // 封面图
            coverPreview: null,
            coverChanged: false,
            coverRemoved: false,
            _autosaving: false,

            // 插入图片弹窗
            imageModalOpen: false,
            imageUrl: '',
            imageAlt: '',
            imageUploading: false,

            // 编辑器：视图模式 + 预览 + 工具栏高亮
            viewMode: 'edit',          // edit | split | preview
            previewHtml: '',
            active: {
                bold: false, italic: false, strike: false, code: false,
                h1: false, h2: false, h3: false,
                ul: false, ol: false, task: false, quote: false,
            },

            // 状态
            saving: false,
            dirty: false,
            savedAt: null,
            savedLabel: '',
            toast: false,
            toastMsg: '',
            errors: [],

            _autosaveTimer: null,
            _toastTimer: null,
            _previewTimer: null,

            init() {
                const root = this.$root;
                this.allTags = JSON.parse(root.dataset.tags || '[]');
                this.selectedTags = JSON.parse(root.dataset.initialSelected || '[]');
                this.noteId = root.dataset.noteId || null;
                this.title = this.$refs.title.value;
                this.content = this.$refs.content.value;
                this.categoryId = this.$refs.category.value;
                this.slug = this.$refs.slug.value;
                this.slugEdited = !!this.slug;
                this.savedLabel = this.noteId ? '已保存' : '';
                this.coverPreview = root.dataset.initialCover || null;
                // 服务端校验错误（如标题为空被重定向回）
                try {
                    this.errors = JSON.parse(root.dataset.serverErrors || '[]');
                } catch (e) { this.errors = []; }
                this.autoResize();
                this.updateActive();

                // Ctrl / Cmd + S 保存
                this._keyHandler = (e) => {
                    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 's') {
                        e.preventDefault();
                        this.autosaveSave(true);
                    }
                };
                window.addEventListener('keydown', this._keyHandler);

                // 离开页面提示
                this._unload = (e) => {
                    if (this.dirty) {
                        e.preventDefault();
                        e.returnValue = '';
                    }
                };
                window.addEventListener('beforeunload', this._unload);
            },

            autoResize() {
                const el = this.$refs.content;
                if (!el) return;
                el.style.height = 'auto';
                el.style.height = Math.max(600, el.scrollHeight) + 'px';
            },

            onChange() {
                // 同步最新正文（修复自动保存读取旧内容的隐患）
                this.content = this.$refs.content.value;
                this.errors = [];
                this.dirty = true;
                this.savedLabel = '未保存';
                if (!this.slugEdited) {
                    this.slug = this.slugify(this.title);
                }
                this.autoResize();
                this.maybeRenderPreview();
                clearTimeout(this._autosaveTimer);
                this._autosaveTimer = setTimeout(() => this.autosaveSave(false), 1500);
            },

            onCategoryChange() {
                const val = this.$refs.category.value;
                if (val === '__NEW__') {
                    this.newCategoryOpen = true;
                    this.newCategoryName = '';
                    this.$nextTick(() => this.$refs.newCategoryInput && this.$refs.newCategoryInput.focus());
                    return;
                }
                this.categoryId = val;
                this.dirty = true;
                clearTimeout(this._autosaveTimer);
                this._autosaveTimer = setTimeout(() => this.autosaveSave(false), 1500);
            },

            async createCategory() {
                const name = (this.newCategoryName || '').trim();
                if (!name) {
                    this.$refs.newCategoryInput && this.$refs.newCategoryInput.focus();
                    return;
                }
                this.catSaving = true;
                const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
                try {
                    const res = await fetch(this.$root.dataset.createCategoryUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ name }),
                    });
                    const data = await res.json();
                    if (!res.ok || !data.id) {
                        this.errors = data.errors ? Object.values(data.errors).flat() : ['创建分类失败，请重试'];
                        this.catSaving = false;
                        return;
                    }
                    // 动态插入新选项并选中
                    const opt = document.createElement('option');
                    opt.value = data.id;
                    opt.textContent = data.name;
                    const sel = this.$refs.category;
                    sel.insertBefore(opt, sel.querySelector('option[value="__NEW__"]'));
                    sel.value = data.id;
                    this.categoryId = data.id;
                    this.newCategoryOpen = false;
                    this.newCategoryName = '';
                    this.dirty = true;
                } catch (e) {
                    this.errors = ['创建分类失败，请重试'];
                } finally {
                    this.catSaving = false;
                }
            },

            cancelNewCategory() {
                this.newCategoryOpen = false;
                this.newCategoryName = '';
                this.$refs.category.value = this.categoryId || '';
            },

            onSlugInput() {
                this.slugEdited = true;
                this.dirty = true;
            },

            // ---------- 封面图 ----------
            onCoverChange() {
                const file = this.$refs.coverInput.files && this.$refs.coverInput.files[0];
                if (!file) return;
                if (this.coverPreview && this.coverPreview.startsWith('blob:')) {
                    URL.revokeObjectURL(this.coverPreview);
                }
                this.coverPreview = URL.createObjectURL(file);
                this.coverChanged = true;
                this.coverRemoved = false;
                this.dirty = true;
                this.savedLabel = '未保存';
                clearTimeout(this._autosaveTimer);
                this._autosaveTimer = setTimeout(() => this.autosaveSave(false), 1500);
            },

            removeCover() {
                this.$refs.coverInput.value = '';
                if (this.coverPreview && this.coverPreview.startsWith('blob:')) {
                    URL.revokeObjectURL(this.coverPreview);
                }
                this.coverPreview = null;
                this.coverRemoved = true;
                this.coverChanged = false;
                this.dirty = true;
                this.savedLabel = '未保存';
                clearTimeout(this._autosaveTimer);
                this._autosaveTimer = setTimeout(() => this.autosaveSave(false), 1500);
            },

            // ---------- Markdown 工具栏核心 ----------
            get ta() {
                return this.$refs.content;
            },

            // 写入新内容并恢复光标选区，触发 onChange 与高亮更新
            setValue(newVal, selStart, selEnd) {
                const ta = this.ta;
                if (!ta) return;
                ta.value = newVal;
                this.content = newVal;
                ta.focus();
                if (typeof selStart === 'number') {
                    ta.setSelectionRange(selStart, selEnd == null ? selStart : selEnd);
                }
                this.onChange();
                this.updateActive();
            },

            // 行内包裹：选中内容则包裹，未选中则插入占位并选中占位
            wrap(before, after, placeholder) {
                const ta = this.ta, val = ta.value;
                const start = ta.selectionStart, end = ta.selectionEnd;
                const selected = val.slice(start, end) || placeholder || '';
                const newVal = val.slice(0, start) + before + selected + after + val.slice(end);
                const cursorStart = start + before.length;
                const cursorEnd = cursorStart + selected.length;
                this.setValue(newVal, cursorStart, cursorEnd);
            },

            // 行首前缀切换（列表 / 引用）
            toggleLine(marker) {
                const ta = this.ta, val = ta.value;
                const start = ta.selectionStart, end = ta.selectionEnd;
                const lineStart = val.lastIndexOf('\n', start - 1) + 1;
                let lineEnd = val.indexOf('\n', end);
                if (lineEnd === -1) lineEnd = val.length;
                const block = val.slice(lineStart, lineEnd);
                const lines = block.split('\n');
                const allHave = lines.length > 0 && lines.every(l => l.startsWith(marker) || l.trim() === '');
                const someHave = lines.some(l => l.startsWith(marker));
                let newLines;
                if (allHave && someHave) {
                    newLines = lines.map(l => l.startsWith(marker) ? l.slice(marker.length) : l);
                } else {
                    newLines = lines.map(l => l.startsWith(marker) ? l : marker + l);
                }
                const newBlock = newLines.join('\n');
                const newVal = val.slice(0, lineStart) + newBlock + val.slice(lineEnd);
                this.setValue(newVal, lineStart, lineStart + newBlock.length);
            },

            // 有序列表切换（自动编号）
            toggleOrderedList() {
                const ta = this.ta, val = ta.value;
                const start = ta.selectionStart, end = ta.selectionEnd;
                const lineStart = val.lastIndexOf('\n', start - 1) + 1;
                let lineEnd = val.indexOf('\n', end);
                if (lineEnd === -1) lineEnd = val.length;
                const block = val.slice(lineStart, lineEnd);
                const lines = block.split('\n');
                const re = /^\d+\.\s/;
                const allNum = lines.length > 0 && lines.every(l => re.test(l) || l.trim() === '');
                let newLines;
                if (allNum) {
                    newLines = lines.map(l => re.test(l) ? l.replace(re, '') : l);
                } else {
                    newLines = lines.map((l, i) => re.test(l) ? l : (i + 1) + '. ' + l);
                }
                const newBlock = newLines.join('\n');
                const newVal = val.slice(0, lineStart) + newBlock + val.slice(lineEnd);
                this.setValue(newVal, lineStart, lineStart + newBlock.length);
            },

            // 标题（H1/H2/H3）：同级切换关闭，异级替换，普通行添加
            applyHeader(level) {
                const ta = this.ta, val = ta.value;
                const start = ta.selectionStart, end = ta.selectionEnd;
                const lineStart = val.lastIndexOf('\n', start - 1) + 1;
                let lineEnd = val.indexOf('\n', end);
                if (lineEnd === -1) lineEnd = val.length;
                const block = val.slice(lineStart, lineEnd);
                const lines = block.split('\n');
                const hashes = '#'.repeat(level);
                const newLines = lines.map(l => {
                    const m = l.match(/^#{1,6}\s+/);
                    if (m && m[0].trim() === hashes) return l.slice(m[0].length);
                    if (m) return hashes + ' ' + l.slice(m[0].length);
                    return hashes + ' ' + l;
                });
                const newBlock = newLines.join('\n');
                const newVal = val.slice(0, lineStart) + newBlock + val.slice(lineEnd);
                this.setValue(newVal, lineStart, lineStart + newBlock.length);
            },

            // 代码块：包裹选中内容或插入占位
            insertCodeBlock() {
                const ta = this.ta, val = ta.value;
                const start = ta.selectionStart, end = ta.selectionEnd;
                const selected = val.slice(start, end) || '在此输入代码';
                const fence = '```\n' + selected + '\n```';
                const before = val.slice(0, start), after = val.slice(end);
                const pre = before && !before.endsWith('\n') ? '\n' : '';
                const post = after && !after.startsWith('\n') ? '\n' : '';
                const text = pre + fence + post;
                const newVal = before + text + after;
                const innerStart = before.length + pre.length + 4; // 跳过 ```\n
                const innerEnd = innerStart + selected.length;
                this.setValue(newVal, innerStart, innerEnd);
            },

            insertLink() {
                const ta = this.ta, val = ta.value;
                const start = ta.selectionStart, end = ta.selectionEnd;
                const selected = val.slice(start, end) || '链接文字';
                const url = window.prompt('请输入链接地址：', 'https://');
                if (url === null) return;
                const md = '[' + selected + '](' + url + ')';
                const newVal = val.slice(0, start) + md + val.slice(end);
                this.setValue(newVal, start + md.length, start + md.length);
            },

            // 点击工具栏「插入图片」→ 打开弹窗（支持本地上传 / 链接）
            insertImage() {
                this.openImageModal();
            },

            openImageModal() {
                this.imageModalOpen = true;
                this.imageUrl = '';
                this.imageAlt = '';
                this.imageUploading = false;
                this.$nextTick(() => {
                    if (this.$refs.imageFileInput) this.$refs.imageFileInput.value = '';
                });
            },

            closeImageModal() {
                this.imageModalOpen = false;
            },

            // 上传本地图片到 /notes/upload-image，成功后回填 URL 并自动填入 alt
            async uploadInlineImage() {
                const file = this.$refs.imageFileInput.files && this.$refs.imageFileInput.files[0];
                if (!file) return;
                this.imageUploading = true;
                this.errors = [];
                const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
                const fd = new FormData();
                fd.append('image', file);
                try {
                    const res = await fetch(this.$root.dataset.uploadImageUrl, {
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
                        this.errors = data.errors ? Object.values(data.errors).flat() : ['图片上传失败，请重试'];
                        this.imageUploading = false;
                        return;
                    }
                    const data = await res.json();
                    this.imageUrl = data.url;
                    this.imageAlt = file.name.replace(/\.[^.]+$/, '');
                } catch (e) {
                    this.errors = ['图片上传失败，请重试'];
                } finally {
                    this.imageUploading = false;
                }
            },

            // 确认插入：将 ![alt](url) 写入正文
            confirmImageInsert() {
                const url = (this.imageUrl || '').trim();
                if (!url) return;
                const alt = (this.imageAlt || '').trim() || '图片';
                const md = '![' + alt + '](' + url + ')';
                const ta = this.ta, val = ta.value;
                const start = ta.selectionStart, end = ta.selectionEnd;
                const newVal = val.slice(0, start) + md + val.slice(end);
                this.setValue(newVal, start + md.length, start + md.length);
                this.closeImageModal();
            },

            insertTable() {
                const ta = this.ta, val = ta.value;
                const start = ta.selectionStart, end = ta.selectionEnd;
                const tpl = '| 列1 | 列2 | 列3 |\n| --- | --- | --- |\n| 内容 | 内容 | 内容 |\n| 内容 | 内容 | 内容 |';
                const before = val.slice(0, start), after = val.slice(end);
                const pre = before && !before.endsWith('\n') ? '\n' : '';
                const post = after && !after.startsWith('\n') ? '\n' : '';
                const text = pre + tpl + post;
                const newVal = before + text + after;
                const pos = before.length + pre.length + tpl.length;
                this.setValue(newVal, pos, pos);
            },

            insertHr() {
                const ta = this.ta, val = ta.value;
                const start = ta.selectionStart, end = ta.selectionEnd;
                const before = val.slice(0, start), after = val.slice(end);
                let pre = '';
                if (before && !before.endsWith('\n')) pre = '\n';
                if (before && !before.endsWith('\n\n') && pre === '\n') pre = '\n\n';
                let post = '';
                if (after && !after.startsWith('\n')) post = '\n';
                if (after && !after.startsWith('\n\n') && post === '\n') post = '\n\n';
                const text = pre + '---' + post;
                const newVal = before + text + after;
                const pos = before.length + text.length;
                this.setValue(newVal, pos, pos);
            },

            // 缩进 / 取消缩进（Tab / Shift+Tab）
            indent(dir) {
                const ta = this.ta, val = ta.value;
                const start = ta.selectionStart, end = ta.selectionEnd;
                const lineStart = val.lastIndexOf('\n', start - 1) + 1;
                let lineEnd = val.indexOf('\n', end);
                if (lineEnd === -1) lineEnd = val.length;
                const block = val.slice(lineStart, lineEnd);
                const lines = block.split('\n');
                let newLines;
                if (dir > 0) {
                    newLines = lines.map(l => '  ' + l);
                } else {
                    newLines = lines.map(l => l.startsWith('  ') ? l.slice(2) : (l.startsWith('\t') ? l.slice(1) : l));
                }
                const newBlock = newLines.join('\n');
                const newVal = val.slice(0, lineStart) + newBlock + val.slice(lineEnd);
                this.setValue(newVal, lineStart, lineStart + newBlock.length);
            },

            // 自动配对：括号 / 引号 / 反引号
            autoPair(open, close) {
                const ta = this.ta, val = ta.value;
                const start = ta.selectionStart, end = ta.selectionEnd;
                const sel = val.slice(start, end);
                if (sel) {
                    const text = open + sel + close;
                    const newVal = val.slice(0, start) + text + val.slice(end);
                    this.setValue(newVal, start + open.length, start + open.length + sel.length);
                } else {
                    const text = open + close;
                    const newVal = val.slice(0, start) + text + val.slice(end);
                    this.setValue(newVal, start + open.length, start + open.length);
                }
            },

            // 编辑器快捷键 + 自动配对
            onEditorKeydown(e) {
                const ta = this.ta;
                if (!ta) return;
                const mod = e.metaKey || e.ctrlKey;
                if (mod && !e.altKey) {
                    const k = e.key.toLowerCase();
                    if (k === 'b') { e.preventDefault(); this.applyCommand('bold'); return; }
                    if (k === 'i') { e.preventDefault(); this.applyCommand('italic'); return; }
                    if (k === 'k') { e.preventDefault(); this.insertLink(); return; }
                }
                if (e.key === 'Tab') { e.preventDefault(); this.indent(e.shiftKey ? -1 : 1); return; }

                const pairs = { '(': ')', '[': ']', '{': '}', '"': '"', "'": "'", '`': '`' };
                const opens = Object.keys(pairs);
                const closes = Object.values(pairs);

                // 输入右符号且右侧已存在相同符号 → 直接跳过
                if (closes.includes(e.key) && !mod && ta.selectionStart === ta.selectionEnd
                    && ta.value[ta.selectionStart] === e.key) {
                    e.preventDefault();
                    ta.setSelectionRange(ta.selectionStart + 1, ta.selectionStart + 1);
                    return;
                }
                // 输入左符号 → 自动补全配对
                if (opens.includes(e.key) && !mod) {
                    this.autoPair(e.key, pairs[e.key]);
                    e.preventDefault();
                    return;
                }
                // 退格删除成对符号
                if (e.key === 'Backspace' && ta.selectionStart === ta.selectionEnd) {
                    const pos = ta.selectionStart;
                    const prev = ta.value[pos - 1], next = ta.value[pos];
                    if (prev && next && pairs[prev] === next) {
                        e.preventDefault();
                        const newVal = ta.value.slice(0, pos - 1) + ta.value.slice(pos + 1);
                        this.setValue(newVal, pos - 1, pos - 1);
                        return;
                    }
                }
            },

            // 命令分发
            applyCommand(cmd) {
                switch (cmd) {
                    case 'bold':      return this.wrap('**', '**', '粗体文字');
                    case 'italic':    return this.wrap('*', '*', '斜体文字');
                    case 'strike':    return this.wrap('~~', '~~', '删除文字');
                    case 'code':      return this.wrap('`', '`', '代码');
                    case 'h1':        return this.applyHeader(1);
                    case 'h2':        return this.applyHeader(2);
                    case 'h3':        return this.applyHeader(3);
                    case 'ul':        return this.toggleLine('- ');
                    case 'ol':        return this.toggleOrderedList();
                    case 'task':      return this.toggleLine('- [ ] ');
                    case 'quote':     return this.toggleLine('> ');
                    case 'codeblock': return this.insertCodeBlock();
                    case 'link':      return this.insertLink();
                    case 'image':     return this.insertImage();
                    case 'table':     return this.insertTable();
                    case 'hr':        return this.insertHr();
                }
            },

            // 更新工具栏高亮状态
            updateActive() {
                const ta = this.ta;
                if (!ta) return;
                const val = ta.value, s = ta.selectionStart, e = ta.selectionEnd;
                const before = val.slice(0, s), after = val.slice(e);
                const wrapActive = (o, c) => before.endsWith(o) && after.startsWith(c);
                const a = this.active;
                a.bold = wrapActive('**', '**');
                a.italic = wrapActive('*', '*');
                a.strike = wrapActive('~~', '~~');
                a.code = wrapActive('`', '`');
                const lineStart = val.lastIndexOf('\n', s - 1) + 1;
                const nl = val.indexOf('\n', e);
                const line = val.slice(lineStart, nl === -1 ? val.length : nl);
                const hm = line.match(/^(#{1,6})\s/);
                a.h1 = !!(hm && hm[1].length === 1);
                a.h2 = !!(hm && hm[1].length === 2);
                a.h3 = !!(hm && hm[1].length === 3);
                a.ul = line.startsWith('- ');
                a.ol = /^\d+\.\s/.test(line);
                a.task = line.startsWith('- [ ] ') || line.startsWith('- [x] ');
                a.quote = line.startsWith('> ');
            },

            // ---------- 预览 ----------
            setViewMode(mode) {
                this.viewMode = mode;
                if (mode !== 'edit') this.renderPreview();
            },

            maybeRenderPreview() {
                if (this.viewMode === 'edit') return;
                clearTimeout(this._previewTimer);
                this._previewTimer = setTimeout(() => this.renderPreview(), 250);
            },

            renderPreview() {
                const src = this.content || '';
                if (typeof window.marked === 'undefined') {
                    this.previewHtml = '<pre class="md-pre-fallback">' + this.escapeHtml(src) + '</pre>';
                    return;
                }
                let html = window.marked.parse(src, { gfm: true, breaks: true });
                if (window.DOMPurify) html = window.DOMPurify.sanitize(html);
                this.previewHtml = html;
            },

            escapeHtml(s) {
                return (s || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
            },

            // ---------- 自动保存 ----------
            buildFormData() {
                const fd = new FormData();
                fd.append('id', this.noteId || '');
                fd.append('title', this.title);
                fd.append('content', this.content);
                fd.append('category_id', this.categoryId || '');
                fd.append('slug', this.slug || '');
                this.selectedTags.forEach((t) => fd.append('tags[]', t));
                fd.append('status', 'draft');
                if (this.coverChanged && this.$refs.coverInput.files[0]) {
                    fd.append('cover_image', this.$refs.coverInput.files[0]);
                }
                if (this.coverRemoved) {
                    fd.append('remove_cover', '1');
                }
                return fd;
            },

            toggleTag(id) {
                const i = this.selectedTags.indexOf(id);
                if (i === -1) {
                    this.selectedTags.push(id);
                } else {
                    this.selectedTags.splice(i, 1);
                }
                this.dirty = true;
            },

            selectAllTags() {
                this.selectedTags = this.allTags.map(t => t.id);
                this.dirty = true;
            },

            clearTags() {
                this.selectedTags = [];
                this.dirty = true;
            },

            slugify(text) {
                return (text || '')
                    .toString()
                    .toLowerCase()
                    .trim()
                    .replace(/[\s\W-]+/g, '-')
                    .replace(/^-+|-+$/g, '')
                    .slice(0, 80) || '';
            },

            get stats() {
                const len = (this.content || '').replace(/\s/g, '').length;
                return {
                    words: len,
                    mins: Math.max(1, Math.round(len / 400)),
                };
            },

            get titleLen() {
                return (this.title || '').length;
            },

            async autosaveSave(showToast) {
                if (this._autosaving) return;
                this._autosaving = true;
                const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
                const fd = this.buildFormData();

                try {
                    const res = await fetch(this.$root.dataset.autosaveUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: fd,
                    });
                    if (!res.ok) return;
                    const data = await res.json();
                    if (data.id) this.noteId = data.id;
                    this.savedAt = data.saved_at;
                    // 回写服务器封面地址，避免重复上传
                    if (data.cover_url) {
                        this.coverPreview = data.cover_url;
                        this.coverChanged = false;
                        this.$refs.coverInput.value = '';
                    }
                    this.dirty = false;
                    this.savedLabel = '已保存 · ' + this.formatTime(data.saved_at);
                    if (showToast) this.showToast('已保存草稿');
                } catch (e) {
                    // 自动保存失败不影响写作，静默忽略
                } finally {
                    this._autosaving = false;
                }
            },

            saveDraft() {
                this.autosaveSave(true);
            },

            formatTime(ts) {
                try {
                    return new Date(ts * 1000).toLocaleTimeString('zh-CN', {
                        hour: '2-digit',
                        minute: '2-digit',
                    });
                } catch (e) {
                    return '';
                }
            },

            showToast(msg) {
                this.toastMsg = msg;
                this.toast = true;
                clearTimeout(this._toastTimer);
                this._toastTimer = setTimeout(() => (this.toast = false), 2000);
            },

            addHidden(form, name, value) {
                let inp = form.querySelector('input[name="' + name + '"]');
                if (!inp) {
                    inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.name = name;
                    form.appendChild(inp);
                }
                inp.value = value;
            },

            syncTagInputs() {
                const c = this.$refs.tagInputs;
                c.innerHTML = '';
                this.selectedTags.forEach((id) => {
                    const inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.name = 'tags[]';
                    inp.value = id;
                    c.appendChild(inp);
                });
            },

            beforeSubmit(e) {
                if (this.saving) {
                    e.preventDefault();
                    return;
                }
                // 客户端校验：标题必填
                this.errors = [];
                if (!this.title || !this.title.trim()) {
                    this.errors = ['标题不能为空'];
                    e.preventDefault();
                    this.scrollToErrors();
                    return;
                }
                this.saving = true;
                const form = this.$refs.form;
                this.syncTagInputs();
                this.addHidden(form, 'status', 'published');
                if (this.noteId) {
                    this.addHidden(form, '_method', 'PUT');
                    form.action = this.$root.dataset.updateUrl.replace('__ID__', this.noteId);
                }
                return true;
            },

            scrollToErrors() {
                this.$nextTick(() => {
                    if (this.$refs.errorBanner) {
                        this.$refs.errorBanner.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });
            },
        };
    };
</script>
<?php /**PATH /Volumes/T7/Project/blog/resources/views/components/article-editor-script.blade.php ENDPATH**/ ?>