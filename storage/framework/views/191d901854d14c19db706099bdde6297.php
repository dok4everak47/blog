<?php $__env->startSection('title', '搜索' . ($q ? '：' . $q : '') . ' · My Blog'); ?>

<?php $__env->startSection('content'); ?>
  <main class="max-w-4xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
    
    <div class="mb-10">
      <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-2">Search</p>
      <h1 class="text-2xl sm:text-3xl font-bold tracking-tight mb-6">搜索文章</h1>

      <form action="<?php echo e(route('search')); ?>" method="GET" class="relative">
        <input type="text" name="q" value="<?php echo e($q); ?>" autofocus
               placeholder="输入关键词搜索文章标题或正文…"
               class="w-full rounded-xl border border-border bg-surface-2 px-5 py-3.5 pr-12 text-sm text-text outline-none transition focus:border-primary focus:bg-surface-2 focus:ring-2 focus:ring-primary/10">
        <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 p-2 text-text-secondary hover:text-primary transition" aria-label="搜索">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </button>
      </form>
    </div>

    
    <?php if($q !== ''): ?>
      <p class="text-sm text-text-secondary mb-6">
        找到 <span class="font-medium text-text"><?php echo e($notes->total()); ?></span> 篇与「<span class="text-primary"><?php echo e($q); ?></span>」相关的文章
      </p>

      <?php $__empty_1 = true; $__currentLoopData = $notes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <article class="group rounded-2xl border border-border bg-surface-2 p-6 sm:p-8 hover:border-border-strong hover:shadow-sm transition-all duration-300 mb-5">
          <a href="<?php echo e(route('notes.show', $note)); ?>">
            <div class="flex items-center gap-2 text-xs text-text-muted mb-2">
              <span><?php echo e($note->created_at->format('Y-m-d')); ?></span>
              <?php if($note->category): ?>
                <span class="text-border-strong">·</span>
                <span class="text-primary"><?php echo e($note->category->name); ?></span>
              <?php endif; ?>
            </div>
            <h3 class="text-lg sm:text-xl font-bold text-text group-hover:text-primary transition leading-snug mb-2">
              <?php echo e($note->title); ?>

            </h3>
            <p class="text-sm text-text-secondary line-clamp-2 leading-relaxed">
              <?php echo e($note->excerpt ?: \App\Models\Note::generateExcerpt($note->content, 120)); ?>

            </p>
          </a>
        </article>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="rounded-2xl border border-dashed border-border p-16 text-center bg-surface-2">
          <p class="text-text-secondary mb-2">没有找到相关文章</p>
          <p class="text-xs text-text-muted">试试换个关键词？</p>
        </div>
      <?php endif; ?>

      
      <?php if($notes->hasPages()): ?>
        <nav class="mt-10 flex items-center justify-center gap-2" aria-label="分页">
          <?php if($notes->onFirstPage()): ?>
            <span class="px-3 py-2 text-sm text-text-muted rounded-lg border border-border bg-surface-2 cursor-not-allowed">← 上一页</span>
          <?php else: ?>
            <a href="<?php echo e($notes->previousPageUrl()); ?>" class="px-3 py-2 text-sm text-text-secondary rounded-lg border border-border bg-surface-2 hover:text-primary hover:border-primary transition">← 上一页</a>
          <?php endif; ?>
          <span class="px-4 py-2 text-sm text-text bg-primary text-white rounded-lg"><?php echo e($notes->currentPage()); ?> / <?php echo e($notes->lastPage()); ?></span>
          <?php if($notes->hasMorePages()): ?>
            <a href="<?php echo e($notes->nextPageUrl()); ?>" class="px-3 py-2 text-sm text-text-secondary rounded-lg border border-border bg-surface-2 hover:text-primary hover:border-primary transition">下一页 →</a>
          <?php else: ?>
            <span class="px-3 py-2 text-sm text-text-muted rounded-lg border border-border bg-surface-2 cursor-not-allowed">下一页 →</span>
          <?php endif; ?>
        </nav>
      <?php endif; ?>
    <?php else: ?>
      <div class="rounded-2xl border border-dashed border-border p-16 text-center bg-surface-2">
        <p class="text-text-secondary">输入关键词开始搜索</p>
      </div>
    <?php endif; ?>
  </main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.blog', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Volumes/T7/Project/blog/resources/views/search.blade.php ENDPATH**/ ?>