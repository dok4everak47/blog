# 数据看板：文章浏览统计

## 需求

后台 Dashboard 增加浏览统计数据：总浏览量、文章排行、趋势图、来源分析。

## 实现步骤

### 1. 数据库迁移 — `page_views` 表

```php
Schema::create('page_views', function (Blueprint $table) {
    $table->id();
    $table->foreignId('note_id')->constrained()->cascadeOnDelete();
    $table->string('ip', 45)->nullable();           // 访客 IP
    $table->text('user_agent')->nullable();          // 浏览器 UA
    $table->string('referer')->nullable();            // 来源 URL
    $table->string('referer_domain', 100)->nullable(); // 来源域名（方便分组统计）
    $table->timestamp('created_at')->useCurrent();    // 浏览时间
});
```

文件：`database/migrations/2026_07_29_000001_create_page_views_table.php`

### 2. Model: `App\Models\PageView`

```php
class PageView extends Model
{
    public $timestamps = false; // 只用 created_at
    protected $fillable = ['note_id', 'ip', 'user_agent', 'referer', 'referer_domain'];

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }
}
```

### 3. 给 Note 模型加关联

```php
public function pageViews(): HasMany
{
    return $this->hasMany(PageView::class);
}
```

### 4. 浏览统计中间件

在 `bootstrap/app.php` 注册一个中间件（追加到 `web` 组），文章详情页每次访问时记录一条浏览数据：

```php
// 注意：这个中间件是临时方案，跳过。用下面的 Middleware 方式
```

创建 `app/Http/Middleware/RecordPageView.php`：

```php
class RecordPageView
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // 只记录文章详情页的 GET 请求
        if ($request->isMethod('GET') && $route = $request->route()) {
            $note = $route->parameter('note');
            if ($note instanceof Note && $note->isPublished()) {
                PageView::create([
                    'note_id' => $note->id,
                    'ip' => $request->ip(),
                    'user_agent' => mb_substr($request->userAgent() ?? '', 0, 500),
                    'referer' => mb_substr($request->header('referer', ''), 0, 500),
                    'referer_domain' => $refererDomain = parse_url($request->header('referer', ''), PHP_URL_HOST) ?: null,
                ]);
            }
        }

        return $response;
    }
}
```

然后在 `bootstrap/app.php` 注册：

```php
$middleware->appendToGroup('web', \App\Http\Middleware\RecordPageView::class);
```

### 5. Controller: `App\Http\Controllers\Dashboard\StatsController`

```php
class StatsController extends Controller
{
    public function index(Request $request): View
    {
        $days = (int) $request->get('days', 30);

        return view('dashboard.stats', [
            // 总浏览量
            'totalViews' => PageView::count(),

            // 今日浏览
            'todayViews' => PageView::whereDate('created_at', today())->count(),

            // 浏览趋势（每日）
            'dailyViews' => PageView::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays($days))
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date'),

            // 热门文章 TOP 10
            'topNotes' => PageView::selectRaw('note_id, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays($days))
                ->groupBy('note_id')
                ->orderByDesc('count')
                ->take(10)
                ->get()
                ->map(fn ($pv) => [
                    'note' => Note::find($pv->note_id),
                    'views' => $pv->count,
                ]),

            // 来源域名 TOP
            'topReferrers' => PageView::selectRaw('COALESCE(referer_domain, \'(直接访问)\') as domain, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays($days))
                ->groupBy('domain')
                ->orderByDesc('count')
                ->take(10)
                ->get()
                ->pluck('count', 'domain'),
        ]);
    }
}
```

### 6. 路由

在 `routes/web.php` 的 `auth` + `admin` 组内加：

```php
Route::get('/dashboard/stats', [StatsController::class, 'index'])
    ->name('dashboard.stats');
```

### 7. 视图

创建 `resources/views/dashboard/stats.blade.php`：

- 顶部概览卡片：总浏览量、今日浏览、平均每日
- 趋势图：用 Alpine.js + 纯 CSS 柱状图（或 Chart.js 的 CDN 版本）
- 热门文章表格：排名、标题、浏览量
- 来源分析：来源域名 + 占比

**柱状图示例（Alpine.js + CSS，无需外部依赖）：**

```blade
<div x-data="{
    data: {{ json_encode($dailyViews) }},
    max: Math.max(...Object.values({{ json_encode($dailyViews) }}), 1)
}">
    <template x-for="(count, date) in data">
        <div class="flex items-center gap-2">
            <span class="w-20 text-xs" x-text="date"></span>
            <div class="flex-1 bg-gray-100 rounded h-6">
                <div class="bg-blue-500 h-6 rounded"
                     :style="'width: ' + (count / max * 100) + '%'"
                     x-text="count">
                </div>
            </div>
        </div>
    </template>
</div>
```

### 8. 更新 `PROJECT_CONTEXT.md`

数据模型补上 `PageView`。

### 9. 测试

```php
// tests/Feature/PageViewTest.php
// 1. 访问公开文章 → 记录一条 page_view
// 2. 访问草稿 → 不记录
// 3. 多次访问同一文章 → 记录多条
// 4. 管理员访问后台 → 不记录
```

## 注意事项

- `page_views` 表会快速增长（每次页面刷新都记录）。建议一个月归档一次旧数据（保留原始的 `created_at` 即可）
- 统计数据是原始日志，不区分唯一访客（没有用 Cookie/指纹去重）。如果需要 UV，可以加 `visitor_id` 字段（匿名化哈希），但复杂度会大很多
- 中间件只记录已发布文章的 GET 请求，草稿和管理后台不记录
- `referer_domain` 提前提取好，避免每次统计时 parse URL
- `totalViews` 和 `todayViews` 适合用模型缓存（Cache::remember）减少数据库压力
