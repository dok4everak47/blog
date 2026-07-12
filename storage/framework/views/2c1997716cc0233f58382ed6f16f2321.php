<?php $__env->startSection('title', ($note->title ?? '') . ' · My Blog'); ?>

<?php
$seoDescription = $note->excerpt
    ?? \App\Models\Note::generateExcerpt($note->content, 160);
$ogImage = $note->cover_image_url
    ?? (preg_match('/!\[.*?\]\(([^)]+)\)/', $note->content ?? '', $m) ? $m[1] : null);
?>

<?php $__env->startSection('seo'); ?>
<meta name="description" content="<?php echo e($seoDescription); ?>">
<meta property="og:type" content="article">
<meta property="og:title" content="<?php echo e($note->title); ?>">
<meta property="og:description" content="<?php echo e($seoDescription); ?>">
<meta property="og:url" content="<?php echo e(route('notes.show', $note)); ?>">
<?php if($ogImage): ?>
<meta property="og:image" content="<?php echo e(url($ogImage)); ?>">
<?php endif; ?>
<meta property="og:site_name" content="<?php echo e(config('app.name', 'My Blog')); ?>">
<meta property="article:published_time" content="<?php echo e($note->created_at->toIso8601String()); ?>">
<?php if($note->updated_at->gt($note->created_at)): ?>
<meta property="article:modified_time" content="<?php echo e($note->updated_at->toIso8601String()); ?>">
<?php endif; ?>
<meta name="twitter:card" content="<?php echo e($ogImage ? 'summary_large_image' : 'summary'); ?>">
<meta name="twitter:title" content="<?php echo e($note->title); ?>">
<meta name="twitter:description" content="<?php echo e($seoDescription); ?>">
<?php if($ogImage): ?>
<meta name="twitter:image" content="<?php echo e(url($ogImage)); ?>">
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
  <main class="max-w-2xl mx-auto px-4 sm:px-6 py-16 sm:py-20">
    <article>
      <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-4">文章</p>

      <h1 class="text-3xl sm:text-4xl font-bold tracking-tight mb-5 leading-tight"><?php echo e($note->title); ?></h1>

      <?php if($note->cover_image_url): ?>
        <div class="mb-8 rounded-2xl overflow-hidden border border-border">
          <img src="<?php echo e($note->cover_image_url); ?>" alt="<?php echo e($note->title); ?>"
               class="w-full h-60 sm:h-80 object-cover">
        </div>
      <?php endif; ?>

      <div class="flex flex-wrap items-center gap-3 mb-8 text-sm text-text-secondary">
        <span class="inline-flex items-center gap-1">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6l4 2m6-2A10 10 0 1 1 4.5 18.5"/>
          </svg>
          <?php echo e($note->created_at->format('Y-m-d')); ?>

        </span>
        <?php if($note->category): ?>
          <span class="text-border-strong">·</span>
          <span class="text-primary"><?php echo e($note->category->name); ?></span>
        <?php endif; ?>
      </div>

      <div class="border-t border-border pt-8">
        <div class="article-content text-text leading-relaxed text-[15px]">
          <?php echo Str::markdown($note->content, [
              'html_input' => 'strip',
              'allow_unsafe_links' => false,
              'max_nesting_level' => 20,
          ]); ?>

        </div>
      </div>
    </article>

    <?php if($note->tags->isNotEmpty()): ?>
      <div class="mt-8 flex flex-wrap gap-2">
        <?php $__currentLoopData = $note->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <a href="<?php echo e(route('tags.show', $tag)); ?>"
             class="rounded-full bg-surface-2 border border-border px-3 py-1 text-xs text-text-secondary hover:border-primary hover:text-primary transition">
            <?php echo e($tag->name); ?>

          </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
    <?php endif; ?>

    
    <section class="mt-10 pt-10 border-t border-border" id="comments">
      <?php
        $comments = $note->loadCount('comments')->comments;
      ?>
      <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-5">
        评论（<?php echo e($note->comments_count); ?>）
      </p>

      
      <?php if($comments->isEmpty()): ?>
        <p class="text-text-muted text-sm py-6">暂无评论，来发表第一条吧~</p>
      <?php else: ?>
        <div class="space-y-5 mb-8">
          <?php $__currentLoopData = $comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex gap-3" id="comment-<?php echo e($comment->id); ?>">
              <div class="w-8 h-8 rounded-full bg-primary flex-shrink-0 flex items-center justify-center text-white text-xs font-bold">
                <?php echo e(strtoupper(substr($comment->user->name, 0, 1))); ?>

              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                  <span class="text-sm font-medium text-text"><?php echo e($comment->user->name); ?></span>
                  <span class="text-xs text-text-muted"><?php echo e($comment->created_at->diffForHumans()); ?></span>
                  <?php if(auth()->guard()->check()): ?> && $comment->user_id === auth()->id()
                    <form action="<?php echo e(route('comments.destroy', $comment)); ?>" method="POST" class="ml-auto">
                      <?php echo csrf_field(); ?>
                      <?php echo method_field('DELETE'); ?>
                      <button type="submit" class="text-xs text-text-muted hover:text-red-500 transition">删除</button>
                    </form>
                  <?php endif; ?>
                </div>
                <p class="text-sm text-text-secondary leading-relaxed"><?php echo e($comment->content); ?></p>

                
                <?php if($comment->replies->isNotEmpty()): ?>
                  <div class="mt-3 ml-4 space-y-3 pl-3 border-l-2 border-border">
                    <?php $__currentLoopData = $comment->replies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                      <div class="flex gap-2">
                        <div class="w-6 h-6 rounded-full bg-sage flex-shrink-0 flex items-center justify-center text-white text-[10px] font-bold">
                          <?php echo e(strtoupper(substr($reply->user->name, 0, 1))); ?>

                        </div>
                        <div>
                          <div class="flex items-center gap-2 mb-0.5">
                            <span class="text-xs font-medium text-text"><?php echo e($reply->user->name); ?></span>
                            <span class="text-[10px] text-text-muted"><?php echo e($reply->created_at->diffForHumans()); ?></span>
                          </div>
                          <p class="text-xs text-text-secondary leading-relaxed"><?php echo e($reply->content); ?></p>
                        </div>
                      </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </div>
                <?php endif; ?>

                
                <?php if(auth()->guard()->check()): ?>
                  <button onclick="document.getElementById('reply-form-<?php echo e($comment->id); ?>').classList.toggle('hidden')"
                          class="mt-1 text-xs text-text-muted hover:text-primary transition">回复</button>
                  <form id="reply-form-<?php echo e($comment->id); ?>" class="hidden mt-2"
                        action="<?php echo e(route('comments.store', $note)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="parent_id" value="<?php echo e($comment->id); ?>">
                    <div class="flex gap-2">
                      <input name="content" required maxlength="2000" placeholder="写下你的回复…"
                             class="flex-1 rounded-lg border border-border bg-surface px-3 py-1.5 text-sm text-text outline-none focus:border-primary">
                      <button type="submit" class="px-3 py-1.5 rounded-lg bg-primary text-white text-xs font-medium hover:bg-primary-hover transition">回复</button>
                    </div>
                  </form>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
      <?php endif; ?>

      
      <?php if(auth()->guard()->check()): ?>
        <form action="<?php echo e(route('comments.store', $note)); ?>" method="POST" class="mt-4">
          <?php echo csrf_field(); ?>
          <textarea name="content" required maxlength="2000" rows="3" placeholder="发表评论…" 
                    class="w-full rounded-xl border border-border bg-surface px-4 py-3 text-sm text-text outline-none focus:border-primary resize-y"></textarea>
          <div class="mt-2 flex justify-end">
            <button type="submit" class="px-5 py-2 rounded-lg bg-primary text-white text-sm font-medium hover:bg-primary-hover transition">发布评论</button>
          </div>
        </form>
      <?php else: ?>
        <p class="mt-4 text-sm text-text-secondary">
          <a href="<?php echo e(route('login')); ?>" class="text-primary hover:underline">登录</a> 后参与评论
        </p>
      <?php endif; ?>
    </section>

    
    <?php if($previous || $next): ?>
      <nav class="mt-10 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <?php if($previous): ?>
          <a href="<?php echo e(route('notes.show', $previous)); ?>"
             class="group rounded-xl border border-border bg-surface-2 p-4 hover:border-primary transition">
            <p class="text-xs text-text-muted mb-1">← 上一篇</p>
            <p class="text-sm font-medium text-text group-hover:text-primary transition line-clamp-1"><?php echo e($previous->title); ?></p>
          </a>
        <?php else: ?>
          <span></span>
        <?php endif; ?>
        <?php if($next): ?>
          <a href="<?php echo e(route('notes.show', $next)); ?>"
             class="group rounded-xl border border-border bg-surface-2 p-4 hover:border-primary transition text-right">
            <p class="text-xs text-text-muted mb-1">下一篇 →</p>
            <p class="text-sm font-medium text-text group-hover:text-primary transition line-clamp-1"><?php echo e($next->title); ?></p>
          </a>
        <?php endif; ?>
      </nav>
    <?php endif; ?>

    
    <?php if($related->isNotEmpty()): ?>
      <section class="mt-12 pt-10 border-t border-border">
        <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-5">Related</p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <?php $__currentLoopData = $related; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('notes.show', $rel)); ?>"
               class="group rounded-xl border border-border bg-surface-2 p-5 hover:border-border-strong hover:shadow-sm transition-all duration-300">
              <?php if($rel->category): ?>
                <p class="text-xs text-primary mb-2"><?php echo e($rel->category->name); ?></p>
              <?php endif; ?>
              <p class="text-sm font-bold text-text group-hover:text-primary transition leading-snug line-clamp-2 mb-2"><?php echo e($rel->title); ?></p>
              <p class="text-xs text-text-muted"><?php echo e($rel->created_at->format('Y-m-d')); ?></p>
            </a>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
      </section>
    <?php endif; ?>

    <div class="mt-10 flex flex-wrap items-center gap-4 text-sm pt-8 border-t border-border">
      <a href="<?php echo e(route('home')); ?>" class="text-text-secondary hover:text-primary transition">← 回首页</a>
      <?php if(auth()->guard()->check()): ?>
        <a href="<?php echo e(route('notes.edit', $note)); ?>" class="text-primary hover:text-primary-hover transition">编辑</a>
        <form action="<?php echo e(route('notes.destroy', $note)); ?>" method="POST" class="inline">
          <?php echo csrf_field(); ?>
          <?php echo method_field('DELETE'); ?>
          <button type="submit" class="text-red-600 hover:text-red-700 transition"
            onclick="return confirm('确定要删除这篇文章吗？')">删除</button>
        </form>
      <?php endif; ?>
    </div>
  </main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.blog', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Volumes/T7/Project/blog/resources/views/notes/show.blade.php ENDPATH**/ ?>