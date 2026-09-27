<header class="sticky top-0 z-50 bg-surface/90 backdrop-blur-md border-b border-border/40">
    <div class="flex justify-between items-center w-full px-6 lg:px-16 max-w-[1360px] mx-auto h-20">
        <div class="flex items-center gap-3">
            <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                <span class="h-9 w-9 rounded-md bg-accent flex items-center justify-center shadow-sm group-hover:opacity-90 transition-opacity">
                    <span class="material-symbols-outlined text-white text-[20px]">auto_stories</span>
                </span>
                <span class="text-headline-md text-text font-bold tracking-tight">Episteme</span>
            </a>
        </div>

        <nav class="hidden md:flex items-center space-x-8">
            <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'text-primary font-semibold border-b-2 border-accent pb-1' : 'text-text-muted font-medium hover:text-primary' }} transition-colors duration-150 text-label-md">Explore</a>
            <a href="{{ url('/#categories') }}" class="text-text-muted font-medium hover:text-primary transition-colors duration-150 text-label-md">Categories</a>
            <a href="{{ url('/#faculty') }}" class="text-text-muted font-medium hover:text-primary transition-colors duration-150 text-label-md">Instructors</a>
            <a href="{{ url('/#events') }}" class="text-text-muted font-medium hover:text-primary transition-colors duration-150 text-label-md">Live Events</a>
        </nav>

        <div class="flex items-center gap-3.5">
            <form method="GET" action="{{ route('products.index') }}" class="hidden lg:block" role="search" aria-label="Search products">
                <label for="nav-search" class="sr-only">Search catalog</label>
                <x-ui.input id="nav-search" name="search" value="{{ request('search') }}" placeholder="Search catalog..." />
            </form>
            @auth
                <a
                    href="{{ route('account.dashboard') }}"
                    class="flex items-center gap-2 text-label-md font-medium text-text hover:text-primary px-3 py-2 transition-colors duration-150"
                >
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">account_circle</span>
                    <span class="hidden sm:inline">{{ Str::before(auth()->user()->name, ' ') }}</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="text-label-md font-medium text-text hover:text-primary px-3 py-2 transition-colors duration-150">Log In</a>
                <x-ui.button href="{{ route('register') }}" size="sm">Get Started</x-ui.button>
            @endauth
        </div>
    </div>
</header>
