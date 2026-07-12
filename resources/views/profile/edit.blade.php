<x-app-layout>
    <x-slot name="header">
        <div class="max-w-6xl mx-auto">
            <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-1">Account</p>
            <h2 class="font-bold text-xl text-text leading-tight">
                {{ __('个人资料') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-10 sm:py-16 bg-bg">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 space-y-6">
            <div class="p-6 sm:p-8 bg-surface-2 border border-border rounded-2xl">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="p-6 sm:p-8 bg-surface-2 border border-border rounded-2xl">
                @include('profile.partials.update-password-form')
            </div>

            <div class="p-6 sm:p-8 bg-surface-2 border border-border rounded-2xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
