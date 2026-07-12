import Cropper from 'cropperjs';

/**
 * 打开图片裁剪弹窗，返回裁剪后的 Blob。
 *
 * @param {File} file       - 原始图片文件
 * @param {Object} [opts]   - 可选配置
 * @param {number} [opts.aspectRatio=NaN] - 裁剪宽高比，NaN 表示自由裁剪
 * @param {number} [opts.maxWidth=1200]   - 输出最大宽度
 * @returns {Promise<Blob>} 如果取消则 reject
 */
export function openImageCropper(file, opts = {}) {
    const aspectRatio = opts.aspectRatio || NaN;
    const maxWidth = opts.maxWidth || 1200;

    return new Promise((resolve, reject) => {
        // 构建弹窗 DOM
        const overlay = document.createElement('div');
        overlay.innerHTML = `
            <div class="cropper-overlay">
                <div class="cropper-modal">
                    <div class="cropper-header">
                        <span class="cropper-title">裁剪封面图</span>
                        <button type="button" class="cropper-close" id="cropper-cancel">&times;</button>
                    </div>
                    <div class="cropper-body">
                        <div class="cropper-container-wrap">
                            <img id="cropper-image" src="" alt="裁剪预览">
                        </div>
                    </div>
                    <div class="cropper-footer">
                        <div class="cropper-aspects">
                            <button type="button" data-ratio="0" class="cropper-ratio-btn cropper-ratio-active">自由</button>
                            <button type="button" data-ratio="1" class="cropper-ratio-btn">1:1</button>
                            <button type="button" data-ratio="1.7778" class="cropper-ratio-btn">16:9</button>
                            <button type="button" data-ratio="1.3333" class="cropper-ratio-btn">4:3</button>
                        </div>
                        <div class="cropper-actions">
                            <button type="button" class="cropper-btn-cancel" id="cropper-btn-cancel">取消</button>
                            <button type="button" class="cropper-btn-confirm" id="cropper-btn-confirm">确认裁剪</button>
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
        const ratioBtns = overlay.querySelectorAll('.cropper-ratio-btn');

        let cropper = null;
        let cleaned = false;

        const cleanup = () => {
            if (cleaned) return;
            cleaned = true;
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            URL.revokeObjectURL(imgEl.src);
            overlay.remove();
        };

        // 加载图片
        imgEl.src = URL.createObjectURL(file);
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

        // 宽高比按钮
        ratioBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                ratioBtns.forEach(b => b.classList.remove('cropper-ratio-active'));
                btn.classList.add('cropper-ratio-active');
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
            }, file.type, 0.9);
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
