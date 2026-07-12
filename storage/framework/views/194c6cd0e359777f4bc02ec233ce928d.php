<?php $__env->startSection('title', '全部文章 · My Blog'); ?>

<?php $__env->startSection('seo'); ?>
<meta name="description" content="浏览全部文章 — <?php echo e(config('app.name', 'My Blog')); ?>">
<meta property="og:type" content="website">
<meta property="og:title" content="全部文章">
<meta property="og:url" content="<?php echo e(route('notes.index')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
  <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
    
    <div class="flex items-center justify-between mb-8">
      <div>
        <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-2">All Articles</p>
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">全部文章</h1>
      </div>
      <span class="text-sm text-text-secondary">共 <?php echo e($notes->total()); ?> 篇</span>
    </div>

    
    <?php $__empty_1 = true; $__currentLoopData = $notes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <article class="group rounded-2xl border border-border bg-surface overflow-hidden hover:border-border-strong hover:shadow-md transition-all duration-300 mb-6">
        <div onclick="window.location.href='<?php echo e(route('notes.show', $note)); ?>'" class="grid grid-cols-1 sm:grid-cols-5 cursor-pointer" role="link" tabindex="0">
          
          <div class="p-6 sm:p-8 sm:col-span-3 flex flex-col justify-center">
            
            <div class="flex items-center gap-1.5 text-xs text-text-muted mb-3">
              <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 2v3M16 2v3M3.5 9h17M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z"/>
              </svg>
              <span>发布于 <?php echo e($note->created_at->format('Y-m-d')); ?></span>
            </div>

            
            <h3 class="text-xl sm:text-2xl font-bold text-text group-hover:text-primary transition leading-snug mb-3">
              <?php echo e($note->title); ?>

            </h3>

            
            <div class="flex flex-wrap items-center gap-1.5 text-xs text-text-secondary mb-3">
              <?php if($note->category): ?>
                <a href="<?php echo e(route('categories.show', $note->category)); ?>"
                   class="inline-flex items-center gap-1 hover:text-primary transition"
                   onclick="event.stopPropagation()">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M19 11H5m14 0a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-6a2 2 0 0 1 2-2m14 0V9a2 2 0 0 0-2-2M5 11V9a2 2 0 0 1 2-2m0 0V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2M7 7h10"/>
                  </svg>
                  <?php echo e($note->category->name); ?>

                </a>
              <?php endif; ?>

              <?php if($note->tags->isNotEmpty()): ?>
                <?php if($note->category): ?>
                  <span class="text-border-strong">·</span>
                <?php endif; ?>
                <?php $__currentLoopData = $note->tags->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <a href="<?php echo e(route('tags.show', $tag)); ?>"
                     class="inline-flex items-center gap-1 hover:text-primary transition"
                     onclick="event.stopPropagation()">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 0 1 0 2.828l-7 7a2 2 0 0 1-2.828 0l-7-7A2 2 0 0 1 3 12V7a4 4 0 0 1 4-4z"/>
                    </svg>
                    <?php echo e($tag->name); ?>

                  </a>
                  <?php if(!$loop->last): ?>
                    <span class="text-border-strong">·</span>
                  <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              <?php endif; ?>
            </div>

            
            <p class="text-sm text-text-secondary line-clamp-2 leading-relaxed">
              <?php echo e(strip_tags(Str::markdown($note->content ?? ''))); ?>

            </p>
          </div>

          
          <?php
            // 优先用缩略图，其次封面图，最后从正文提取第一张 Markdown 图片
            $displayImage = $note->thumbnail_url ?: $note->cover_image_url;
            if (!$displayImage && $note->content) {
              if (preg_match('/!\[.*?\]\(([^)]+)\)/', $note->content, $m)) {
                $displayImage = $m[1];
              }
            }
          ?>

          <div class="min-h-[200px] sm:min-h-auto sm:col-span-2 relative overflow-hidden bg-surface-2">
            <?php if($displayImage): ?>
              <img src="<?php echo e($displayImage); ?>" alt="<?php echo e($note->title); ?>"
                   loading="lazy"
                   class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            <?php else: ?>
              
              <div class="absolute inset-0 bg-gradient-to-br from-sage-light/60 to-sage/20 flex items-center justify-center">
                <div class="text-center">
                  <div class="w-20 h-20 rounded-2xl bg-white/60 backdrop-blur-sm flex items-center justify-center mx-auto mb-3 shadow-sm">
                    <span class="text-3xl font-bold text-sage">
                      <?php echo e(mb_substr($note->title, 0, 1)); ?>

                    </span>
                  </div>
                  <span class="text-xs text-sage/70 font-medium">点击阅读</span>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </article>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <div class="rounded-2xl border border-dashed border-border p-16 text-center bg-surface-2">
        <p class="text-text-secondary mb-2">还没有文章</p>
        <?php if(auth()->guard()->check()): ?>
          <a href="<?php echo e(route('notes.create')); ?>" class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-hover transition">
            写第一篇文章
          </a>
        <?php else: ?>
          <a href="<?php echo e(route('register')); ?>" class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-hover transition">
            注册账号开始写作
          </a>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    
    <?php if($notes->hasPages()): ?>
      <nav class="mt-10 flex items-center justify-center gap-2" aria-label="分页">
        <?php if($notes->onFirstPage()): ?>
          <span class="px-3 py-2 text-sm text-text-muted rounded-lg border border-border bg-surface-2 cursor-not-allowed">← 上一页</span>
        <?php else: ?>
          <a href="<?php echo e($notes->previousPageUrl()); ?>" class="px-3 py-2 text-sm text-text-secondary rounded-lg border border-border bg-surface-2 hover:text-primary hover:border-primary transition">← 上一页</a>
        <?php endif; ?>

        <span class="px-4 py-2 text-sm text-text bg-primary text-white rounded-lg">
          <?php echo e($notes->currentPage()); ?> / <?php echo e($notes->lastPage()); ?>

        </span>

        <?php if($notes->hasMorePages()): ?>
          <a href="<?php echo e($notes->nextPageUrl()); ?>" class="px-3 py-2 text-sm text-text-secondary rounded-lg border border-border bg-surface-2 hover:text-primary hover:border-primary transition">下一页 →</a>
        <?php else: ?>
          <span class="px-3 py-2 text-sm text-text-muted rounded-lg border border-border bg-surface-2 cursor-not-allowed">下一页 →</span>
        <?php endif; ?>
      </nav>
    <?php endif; ?>
  </main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.blog', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Volumes/T7/Project/blog/resources/views/notes/index.blade.php ENDPATH**/ ?>