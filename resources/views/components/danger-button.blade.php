<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center rounded-lg border border-red-200 bg-red-50 px-5 py-2.5 text-sm font-medium text-red-600 transition-all duration-200 hover:bg-red-100 hover:text-red-700 hover:-translate-y-px focus:outline-none focus:ring-2 focus:ring-red-200 active:translate-y-0 active:scale-[0.98]']) }}>
    {{ $slot }}
</button>
