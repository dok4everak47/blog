<x-guest-layout>
    <div class="mb-7">
        <h1 class="text-[26px] font-bold text-text tracking-tight">{{ __('登录') }}</h1>
        <p class="mt-1.5 text-sm text-text-secondary/70">{{ __('欢迎回来') }}</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('邮箱')" />
            <x-text-input id="email" class="block mt-2" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('密码')" />
            <x-text-input id="password" class="block mt-2"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-border text-primary focus:ring-primary/20 w-4 h-4" name="remember">
                <span class="ms-2 text-sm text-text-secondary">{{ __('记住我') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-text-secondary hover:text-primary transition" href="{{ route('password.request') }}">
                    {{ __('忘记密码？') }}
                </a>
            @endif
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full">
                {{ __('登录') }}
            </x-primary-button>

            <p class="mt-4 text-center text-sm text-text-secondary">
                {{ __('还没有账号？') }}
                <a href="{{ route('register') }}" class="font-medium text-primary hover:text-primary-hover transition">
                    {{ __('注册') }}
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
