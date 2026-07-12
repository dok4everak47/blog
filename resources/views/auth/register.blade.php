<x-guest-layout>
    {{-- 标题区：更大字号，更浅副标题 --}}
    <div class="mb-7">
        <h1 class="text-[26px] font-bold text-text tracking-tight">{{ __('注册') }}</h1>
        <p class="mt-1.5 text-sm text-text-secondary/70">{{ __('创建一个新账号') }}</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('用户名')" />
            <x-text-input id="name" class="block mt-2" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('邮箱')" />
            <x-text-input id="email" class="block mt-2" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('密码')" />
            <x-text-input id="password" class="block mt-2"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('确认密码')" />
            <x-text-input id="password_confirmation" class="block mt-2"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
        </div>

        {{-- 底部操作区：登录链接 + 独立注册按钮 --}}
        <div class="pt-2">
            <x-primary-button class="w-full">
                {{ __('注册') }}
            </x-primary-button>

            <p class="mt-4 text-center text-sm text-text-secondary">
                {{ __('已有账号？') }}
                <a href="{{ route('login') }}" class="font-medium text-primary hover:text-primary-hover transition">
                    {{ __('登录') }}
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
