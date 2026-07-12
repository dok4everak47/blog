<x-guest-layout>
    <div class="mb-7">
        <h1 class="text-[26px] font-bold text-text tracking-tight">{{ __('设置新密码') }}</h1>
        <p class="mt-1.5 text-sm text-text-secondary/70">{{ __('请输入你的新密码') }}</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-input-label for="email" :value="__('邮箱')" />
            <x-text-input id="email" class="block mt-2" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div>
            <x-input-label for="password" :value="__('新密码')" />
            <x-text-input id="password" class="block mt-2" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('确认密码')" />
            <x-text-input id="password_confirmation" class="block mt-2"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full">
                {{ __('重置密码') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
