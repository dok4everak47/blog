<x-guest-layout>
    <div class="mb-7">
        <h1 class="text-[26px] font-bold text-text tracking-tight">{{ __('邮箱验证') }}</h1>
        <p class="mt-1.5 text-sm text-text-secondary/70">{{ __('验证你的邮箱地址以完成注册') }}</p>
    </div>

    <div class="mb-5 text-sm text-text-secondary leading-relaxed">
        {{ __('感谢注册！请点击我们发送到您邮箱的验证链接以完成验证。如果没有收到，可以重新发送。') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-5 rounded-lg bg-primary-light px-4 py-3 text-sm text-primary">
            {{ __('新的验证链接已发送到您的邮箱。') }}
        </div>
    @endif

    <div class="space-y-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button class="w-full">
                {{ __('重新发送验证邮件') }}
            </x-primary-button>
        </form>

        <div class="text-center">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-text-secondary hover:text-primary transition">
                    {{ __('退出登录') }}
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
