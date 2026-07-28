<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\PageView;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatsController extends Controller
{
    public function index(Request $request): View
    {
        $days = (int) $request->get('days', 30);

        return view('dashboard.stats', [
            'totalViews' => PageView::count(),
            'todayViews' => PageView::whereDate('created_at', today())->count(),
            'dailyViews' => PageView::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays($days))
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date'),
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
