// TipTap 富文本编辑器 — About 页面专用
// 通过 Vite 打包，暴露 initAboutEditor() 给全局
import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';

window.initAboutEditor = function(options) {
    var element = document.getElementById(options.elementId);
    if (!element) {
        console.error('[AboutEditor] 找不到编辑器容器 #' + options.elementId);
        return null;
    }

    var editor = new Editor({
        element: element,
        extensions: [
            StarterKit.configure({
                heading: { levels: [2, 3] },
            }),
        ],
        content: options.content || '',
        onUpdate: function() {
            if (typeof options.onUpdate === 'function') {
                options.onUpdate(editor.getHTML());
            }
        },
    });

    return editor;
};
