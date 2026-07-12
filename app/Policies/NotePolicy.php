<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;

class NotePolicy
{
    /**
     * 任何人都能看到公开列表（前台由控制器/作用域控制草稿过滤）
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * 文章可见性规则：
     * - 已发布文章 → 所有人可见（含未登录访客）
     * - 草稿 → 仅作者本人可见
     */
    public function view(?User $user, Note $note): bool
    {
        // 已发布文章对所有角色开放
        if ($note->isPublished()) {
            return true;
        }

        // 草稿仅作者本人可看（未登录直接拒绝）
        if ($user === null) {
            return false;
        }

        return $user->id === $note->user_id;
    }

    /**
     * 任意登录用户都可以写文章
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * 仅作者本人可编辑（根除 IDOR）
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
