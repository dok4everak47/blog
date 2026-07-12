import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// 主题切换
document.addEventListener('DOMContentLoaded', function() {
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
