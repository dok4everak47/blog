<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyAccountRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profile,
    ) {}

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $this->profile->update($request);

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function destroy(DestroyAccountRequest $request): RedirectResponse
    {
        $this->profile->destroy($request->user());

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
