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
            <a href="#explore" class="text-primary font-semibold border-b-2 border-accent pb-1 text-label-md transition-colors duration-150">Explore</a>
            <a href="#categories" class="text-text-muted font-medium hover:text-primary transition-colors duration-150 text-label-md">Categories</a>
            <a href="#faculty" class="text-text-muted font-medium hover:text-primary transition-colors duration-150 text-label-md">Instructors</a>
            <a href="#events" class="text-text-muted font-medium hover:text-primary transition-colors duration-150 text-label-md">Live Events</a>
        </nav>

        <div class="flex items-center gap-3.5">
            <div class="hidden lg:block">
                <x-ui.input placeholder="Search catalog..." />
            </div>
            <a href="#login" class="text-label-md font-medium text-text hover:text-primary px-3 py-2 transition-colors duration-150">Log In</a>
            <x-ui.button href="#get-started" size="sm">Get Started</x-ui.button>
        </div>
    </div>
</header>
