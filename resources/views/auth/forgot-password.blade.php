<x-guest-layout>
    <div class="mb-7">
        <h1 class="text-[26px] font-bold text-text tracking-tight">{{ __('重置密码') }}</h1>
        <p class="mt-1.5 text-sm text-text-secondary/70">{{ __('输入邮箱，我们将发送重置链接') }}</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('邮箱')" />
            <x-text-input id="email" class="block mt-2" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full">
                {{ __('发送重置链接') }}
            </x-primary-button>

            <p class="mt-4 text-center text-sm text-text-secondary">
                <a href="{{ route('login') }}" class="font-medium text-primary hover:text-primary-hover transition">
                    ← {{ __('返回登录') }}
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
