import Alpine from 'alpinejs';
import Prism from 'prismjs';
import { openImageCropper } from './cropper.js';

// 暴露到全局，供 article-editor-script 等内联脚本调用
window.openImageCropper = openImageCropper;

// 常用语法高亮语言
import 'prismjs/components/prism-markup';
import 'prismjs/components/prism-css';
import 'prismjs/components/prism-clike';
import 'prismjs/components/prism-javascript';
import 'prismjs/components/prism-php';
import 'prismjs/components/prism-sql';
import 'prismjs/components/prism-bash';
import 'prismjs/components/prism-json';
import 'prismjs/components/prism-yaml';
import 'prismjs/components/prism-python';

window.Alpine = Alpine;

Alpine.start();

// 主题切换 + 代码高亮
document.addEventListener('DOMContentLoaded', function() {
    // Prism 代码语法高亮
    Prism.highlightAll();

    var toggle = document.getElementById('theme-toggle');
    if (!toggle) return;

    toggle.addEventListener('click', function() {
        var html = document.documentElement;
        var isDark = html.classList.toggle('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
    });

    // 监听系统主题变化（当用户选择「跟随系统」时）
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
        var stored = localStorage.getItem('theme');
        if (!stored) { // 仅在用户未手动设置时跟随系统
            document.documentElement.classList.toggle('dark', e.matches);
        }
    });
});
