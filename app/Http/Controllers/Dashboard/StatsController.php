<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\PageView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class StatsController extends Controller
{
    public function index(Request $request): View
    {
        $days = (int) $request->get('days', 30);
        $cacheKey = 'dashboard.stats.' . $days;

        $stats = Cache::remember($cacheKey, 600, function () use ($days) {
            $topRows = PageView::selectRaw('note_id, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays($days))
                ->groupBy('note_id')
                ->orderByDesc('count')
                ->take(10)
                ->get();

            $notes = Note::whereIn('id', $topRows->pluck('note_id'))
                ->get()
                ->keyBy('id');

            return [
                'totalViews' => PageView::count(),
                'todayViews' => PageView::whereDate('created_at', today())->count(),
                'dailyViews' => PageView::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->where('created_at', '>=', now()->subDays($days))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->pluck('count', 'date'),
                'topNotes' => $topRows->map(fn ($pv) => [
                    'note' => $notes->get($pv->note_id),
                    'views' => $pv->count,
                ]),
                'topReferrers' => PageView::selectRaw('COALESCE(referer_domain, \'(直接访问)\') as domain, COUNT(*) as count')
                    ->where('created_at', '>=', now()->subDays($days))
                    ->groupBy('domain')
                    ->orderByDesc('count')
                    ->take(10)
                    ->get()
                    ->pluck('count', 'domain'),
            ];
        });

        return view('dashboard.stats', $stats);
    }
}
