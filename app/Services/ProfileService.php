<?php

namespace App\Services;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ProfileService
{
    public function update(ProfileUpdateRequest $request): User
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $user;
    }

    public function destroy(User $user): void
    {
        Auth::logout();

        $user->delete();
    }
}
