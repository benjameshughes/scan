<div class="relative w-full dark:bg-zinc-800">
    <nav class="max-w-7xl mx-auto flex flex-1 justify-between items-center">
        <div class="justify-start">
            @auth
                <span class="text-zinc-900 dark:text-white">{{ (now()->hour < 12 ? 'Good Morning' : 'Good Evening') . ', ' . auth()->user()->name }}</span>
            @endauth
        </div>
        <div class="justify-end">
            <!-- Dark mode toggle -->

            <flux:button x-data x-on:click="$flux.dark = ! $flux.dark" icon="moon" variant="subtle"
                         aria-label="Toggle dark mode" class="mr-2"/>
            <a
                    href="{{ url('/dashboard') }}"
                    class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
            >
                Dashboard
            </a>
        </div>
    </nav>
</div>