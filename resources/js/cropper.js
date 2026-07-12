import Cropper from 'cropperjs';

/**
 * 打开图片裁剪弹窗，返回裁剪后的 Blob。
 *
 * @param {File|Blob|String} source - 原始图片：File/Blob 或图片 URL
 * @param {Object} [opts]   - 可选配置
 * @param {number} [opts.aspectRatio=NaN] - 裁剪宽高比，NaN 表示自由裁剪
 * @param {number} [opts.maxWidth=1200]   - 输出最大宽度
 * @returns {Promise<Blob>} 如果取消则 reject
 */
export function openImageCropper(source, opts = {}) {
    const aspectRatio = opts.aspectRatio || NaN;
    const maxWidth = opts.maxWidth || 1200;

    return new Promise((resolve, reject) => {
        // 构建弹窗 DOM
        const overlay = document.createElement('div');
        overlay.innerHTML = `
            <div class="img-cropper-overlay">
                <div class="img-cropper-modal">
                    <div class="img-cropper-header">
                        <span class="img-cropper-title">裁剪封面图</span>
                        <button type="button" class="img-cropper-close" id="cropper-cancel">&times;</button>
                    </div>
                    <div class="img-cropper-body">
                        <div class="img-cropper-container-wrap">
                            <img id="cropper-image" src="" alt="裁剪预览">
                        </div>
                    </div>
                    <div class="img-cropper-footer">
                        <div class="img-cropper-aspects">
                            <button type="button" data-ratio="0" class="img-cropper-ratio-btn img-cropper-ratio-active">自由</button>
                            <button type="button" data-ratio="1" class="img-cropper-ratio-btn">1:1</button>
                            <button type="button" data-ratio="1.7778" class="img-cropper-ratio-btn">16:9</button>
                            <button type="button" data-ratio="1.3333" class="img-cropper-ratio-btn">4:3</button>
                        </div>
                        <div class="img-cropper-actions">
                            <button type="button" class="img-cropper-btn-cancel" id="cropper-btn-cancel">取消</button>
                            <button type="button" class="img-cropper-btn-confirm" id="cropper-btn-confirm">确认裁剪</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);

        const imgEl = overlay.querySelector('#cropper-image');
        const cancelBtn = overlay.querySelector('#cropper-cancel');
        const cancelBtn2 = overlay.querySelector('#cropper-btn-cancel');
        const confirmBtn = overlay.querySelector('#cropper-btn-confirm');
        const ratioBtns = overlay.querySelectorAll('.img-cropper-ratio-btn');

        let cropper = null;
        let cleaned = false;
        let objectUrl = null;

        const cleanup = () => {
            if (cleaned) return;
            cleaned = true;
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }
            overlay.remove();
        };

        // 加载图片：支持 File/Blob 和远程 URL
        if (source instanceof Blob) {
            objectUrl = URL.createObjectURL(source);
            imgEl.src = objectUrl;
        } else if (typeof source === 'string' && source.trim()) {
            imgEl.src = source;
            imgEl.crossOrigin = 'anonymous';
        } else {
            reject(new Error('不支持的图片源'));
            cleanup();
            return;
        }

        imgEl.onload = () => {
            cropper = new Cropper(imgEl, {
                aspectRatio,
                viewMode: 1,
                autoCropArea: 1,
                responsive: true,
                background: false,
                modal: true,
                guides: true,
                center: true,
                highlight: true,
                rotatable: false,
                scalable: true,
                zoomable: true,
                movable: true,
            });
        };

        imgEl.onerror = () => {
            reject(new Error('图片加载失败'));
            cleanup();
        };

        // 宽高比按钮
        ratioBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                ratioBtns.forEach(b => b.classList.remove('img-cropper-ratio-active'));
                btn.classList.add('img-cropper-ratio-active');
                const r = parseFloat(btn.dataset.ratio);
                if (cropper) {
                    cropper.setAspectRatio(r || NaN);
                }
            });
        });

        // 确认裁剪
        confirmBtn.addEventListener('click', () => {
            if (!cropper) return;
            const canvas = cropper.getCroppedCanvas({ maxWidth });
            if (!canvas) {
                reject(new Error('裁剪失败'));
                cleanup();
                return;
            }
            canvas.toBlob(blob => {
                if (blob) {
                    resolve(blob);
                } else {
                    reject(new Error('图片处理失败'));
                }
                cleanup();
            }, 'image/jpeg', 0.92);
        });

        // 取消
        const cancel = () => { reject(new Error('用户取消')); cleanup(); };
        cancelBtn.addEventListener('click', cancel);
        cancelBtn2.addEventListener('click', cancel);
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) cancel();
        });
    });
}
