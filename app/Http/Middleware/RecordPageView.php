<?php

namespace App\Http\Middleware;

use App\Models\Note;
use App\Models\PageView;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordPageView
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethod('GET') && $route = $request->route()) {
            $note = $route->parameter('note');
            if ($note instanceof Note && $note->isPublished()) {
                PageView::create([
                    'note_id' => $note->id,
                    'ip' => $request->ip(),
                    'user_agent' => mb_substr($request->userAgent() ?? '', 0, 500),
                    'referer' => mb_substr($request->header('referer', ''), 0, 500),
                    'referer_domain' => parse_url($request->header('referer', ''), PHP_URL_HOST) ?: null,
                ]);
            }
        }

        return $response;
    }
}
