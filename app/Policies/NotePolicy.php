<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;

class NotePolicy
{
    /**
     * 所有登录用户都可以管理笔记
     * （当前项目只有一个管理员角色，后续可扩展为 RBAC）
     */

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Note $note): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Note $note): bool
    {
        return true;
    }

    public function delete(User $user, Note $note): bool
    {
        return true;
    }
}
