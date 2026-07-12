<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Note;
use App\Http\Requests\StoreCommentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * 发布评论（或回复）
     */
    public function store(StoreCommentRequest $request, Note $note): RedirectResponse
    {
        // 草稿不允许评论
        if ($note->isDraft()) {
            abort(404);
        }

        $this->authorize('create', Comment::class);

        $comment = new Comment($request->only('content', 'parent_id'));
        $comment->note_id = $note->id;
        $comment->user_id = Auth::id();
        $comment->save();

        return back()->with('success', '评论已发布');
    }

    /**
     * 删除评论（仅作者可删）
     */
    public function destroy(Comment $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);
        $comment->delete();
        return back()->with('success', '评论已删除');
    }
}
