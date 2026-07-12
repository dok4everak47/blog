<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-text">
            {{ __('删除账户') }}
        </h2>

        <p class="mt-1 text-sm text-text-secondary">
            {{ __('账户删除后，所有数据和资源将被永久删除。删除前请备份你需要保留的信息。') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('删除账户') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-text">
                {{ __('确定要删除账户吗？') }}
            </h2>

            <p class="mt-1 text-sm text-text-secondary">
                {{ __('账户删除后，所有数据将被永久删除。请输入密码以确认。') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('密码') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="{{ __('密码') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('取消') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('删除账户') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
