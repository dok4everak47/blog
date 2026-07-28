<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Reaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReactionController extends Controller
{
    /**
     * 切换点赞状态（已赞则取消，未赞则点赞）
     */
    public function toggle(Request $request, Note $note): JsonResponse
    {
        if ($note->isDraft()) {
            abort(404);
        }

        $userId = Auth::id();

        $existing = Reaction::where('note_id', $note->id)
            ->where('user_id', $userId)
            ->where('type', 'like')
            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            Reaction::create([
                'note_id' => $note->id,
                'user_id' => $userId,
                'type' => 'like',
            ]);
            $liked = true;
        }

        return response()->json([
            'liked' => $liked,
            'count' => Reaction::countByNote($note->id),
        ]);
    }
}
