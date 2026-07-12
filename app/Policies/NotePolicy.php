<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;

class NotePolicy
{
    /**
     * 任何人都能看到公开列表（前台公开访问由控制器/作用域控制）
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * 已发布文章所有人可见；草稿的可见性已在控制器 show() 中按作者过滤，
     * 这里对公开文章放行即可。
     */
    public function view(User $user, Note $note): bool
    {
        return true;
    }

    /**
     * 任意登录用户都可以写文章
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * 仅作者本人可编辑（根除 IDOR：之前对所有登录用户返回 true）
     */
    public function update(User $user, Note $note): bool
    {
        return $user->id === $note->user_id;
    }

    /**
     * 仅作者本人可删除
     */
    public function delete(User $user, Note $note): bool
    {
        return $user->id === $note->user_id;
    }
}
