// 文章编辑器依赖：marked（Markdown 解析）+ DOMPurify（XSS 净化）
// 本地打包，替代原来的 CDN 引入，避免供应链风险
import { marked } from 'marked';
import DOMPurify from 'dompurify';

window.marked = marked;
window.DOMPurify = DOMPurify;
