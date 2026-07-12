<x-guest-layout>
    <div class="mb-7">
        <h1 class="text-[26px] font-bold text-text tracking-tight">{{ __('安全确认') }}</h1>
        <p class="mt-1.5 text-sm text-text-secondary/70">{{ __('请输入密码以继续') }}</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="password" :value="__('密码')" />
            <x-text-input id="password" class="block mt-2"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full">
                {{ __('确认') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
