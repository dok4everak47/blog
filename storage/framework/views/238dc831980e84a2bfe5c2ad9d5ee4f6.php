<?php echo e('<?xml version="1.0" encoding="UTF-8" ?>'); ?>

<rss version="2.0"
     xmlns:atom="http://www.w3.org/2005/Atom"
     xmlns:content="http://purl.org/rss/1.0/modules/content/">
    <channel>
        <title><?php echo e(config('app.name', 'My Blog')); ?></title>
        <link><?php echo e(url('/')); ?></link>
        <description><?php echo e(config('app.name', 'My Blog')); ?> — 最新文章</description>
        <language>zh-CN</language>
        <copyright><?php echo e(now()->year); ?> <?php echo e(config('app.name', 'My Blog')); ?></copyright>
        <lastBuildDate><?php echo e($notes->first() ? $notes->first()->updated_at->toRssString() : now()->toRssString()); ?></lastBuildDate>
        <atom:link href="<?php echo e(url('/feed.xml')); ?>" rel="self" type="application/rss+xml"/>

        <?php $__currentLoopData = $notes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <item>
            <title><?php echo e($note->title); ?></title>
            <link><?php echo e(route('notes.show', $note)); ?></link>
            <guid isPermaLink="true"><?php echo e(route('notes.show', $note)); ?></guid>
            <description><?php echo e(\Illuminate\Support\Str::limit(strip_tags($note->content ?? ''), 300)); ?></description>
            <?php if($note->cover_image_url): ?>
            <enclosure url="<?php echo e(url($note->cover_image_url)); ?>" type="image/jpeg"/>
            <?php endif; ?>
            <pubDate><?php echo e($note->created_at->toRssString()); ?></pubDate>
            <?php if($note->category): ?>
            <category><?php echo e($note->category->name); ?></category>
            <?php endif; ?>
            <?php $__currentLoopData = $note->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <category><?php echo e($tag->name); ?></category>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </item>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </channel>
</rss>
<?php /**PATH /Volumes/T7/Project/blog/resources/views/feeds/rss.blade.php ENDPATH**/ ?>