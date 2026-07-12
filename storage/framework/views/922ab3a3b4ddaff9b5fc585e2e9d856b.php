
<div x-show="imageModalOpen" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

  
  <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeImageModal()"></div>

  
  <div class="relative w-full max-w-md rounded-2xl border border-border bg-surface p-6 shadow-xl"
       @click.stop>
    <div class="flex items-center justify-between mb-5">
      <h3 class="text-lg font-semibold text-text">插入图片</h3>
      <button type="button" class="text-text-muted hover:text-text transition" @click="closeImageModal()" aria-label="关闭">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18"/>
        </svg>
      </button>
    </div>

    
    <label class="block text-sm font-medium text-text mb-2">上传本地图片</label>
    <div class="flex items-center gap-3">
      <input x-ref="imageFileInput" type="file" accept="image/*" class="hidden" @change="uploadInlineImage()">
      <button type="button" class="btn-ghost flex-1 justify-center" @click="$refs.imageFileInput.click()" :disabled="imageUploading">
        <span x-show="imageUploading" class="spinner spinner-sm" x-cloak></span>
        <span x-text="imageUploading ? '上传中…' : '选择图片文件'"></span>
      </button>
      <span x-show="imageUploading" x-cloak class="text-xs text-text-muted whitespace-nowrap">上传中…</span>
    </div>
    <p class="text-xs text-text-muted mt-1.5">支持 JPG / PNG / WebP / GIF，单张 ≤ 5MB</p>

    <div class="md-sep my-5" aria-hidden="true"></div>

    
    <label class="block text-sm font-medium text-text mb-2">图片链接</label>
    <input type="text" x-model="imageUrl" class="field-control mb-3"
           placeholder="https://… 或上传后自动填入">
    <label class="block text-sm font-medium text-text mb-2">图片描述（alt，可选）</label>
    <input type="text" x-model="imageAlt" class="field-control mb-6"
           placeholder="用于无障碍与 SEO">

    <div class="flex justify-end gap-3">
      <button type="button" class="btn-ghost" @click="closeImageModal()">取消</button>
      <button type="button" class="btn-primary" @click="confirmImageInsert()" :disabled="!imageUrl.trim()">插入</button>
    </div>
  </div>
</div>
<?php /**PATH /Volumes/T7/Project/blog/resources/views/components/image-insert-modal.blade.php ENDPATH**/ ?>