<footer class="bg-surface-subtle border-t border-border/40">
    <div class="w-full px-6 lg:px-16 py-16 max-w-[1360px] mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-12 pb-16 border-b border-border/40">
            <div class="md:col-span-4">
                <div class="flex items-center gap-3 mb-4">
                    <span class="h-8 w-8 rounded-md bg-accent flex items-center justify-center">
                        <span class="material-symbols-outlined text-white text-[18px]" aria-hidden="true">auto_stories</span>
                    </span>
                    <span class="text-headline-md text-text font-bold">Episteme</span>
                </div>
                <p class="text-body-sm text-text-muted max-w-xs">
                    Knowledge into capability. Courses, workshops, webinars, and mentoring designed to turn learning into practical skills.
                </p>
            </div>

            {{-- Every link below goes somewhere real; footers full of "#" placeholders were removed. --}}
            <nav class="md:col-span-2" aria-label="Explore">
                <p class="text-label-md font-semibold text-text mb-4">Explore</p>
                <ul class="space-y-2.5 text-body-sm">
                    <li><a href="{{ route('products.index') }}" class="text-text-muted hover:text-primary transition-colors">Catalog</a></li>
                    <li><a href="{{ route('products.index', ['type' => 'course']) }}" class="text-text-muted hover:text-primary transition-colors">Courses</a></li>
                    <li><a href="{{ route('products.index', ['type' => 'webinar']) }}" class="text-text-muted hover:text-primary transition-colors">Webinars</a></li>
                    <li><a href="{{ route('products.index', ['type' => 'workshop']) }}" class="text-text-muted hover:text-primary transition-colors">Workshops</a></li>
                </ul>
            </nav>

            <nav class="md:col-span-2" aria-label="Learn">
                <p class="text-label-md font-semibold text-text mb-4">Learn</p>
                <ul class="space-y-2.5 text-body-sm">
                    <li><a href="{{ route('products.index', ['type' => 'mentoring']) }}" class="text-text-muted hover:text-primary transition-colors">Mentoring</a></li>
                    <li><a href="{{ route('products.index', ['type' => 'bootcamp']) }}" class="text-text-muted hover:text-primary transition-colors">Bootcamps</a></li>
                    <li><a href="{{ route('home') }}#categories" class="text-text-muted hover:text-primary transition-colors">Categories</a></li>
                    <li><a href="{{ route('home') }}#faculty" class="text-text-muted hover:text-primary transition-colors">Faculty</a></li>
                </ul>
            </nav>

            <nav class="md:col-span-2" aria-label="Account">
                <p class="text-label-md font-semibold text-text mb-4">Account</p>
                <ul class="space-y-2.5 text-body-sm">
                    @auth
                        <li><a href="{{ route('account.learning.index') }}" class="text-text-muted hover:text-primary transition-colors">My Learning</a></li>
                        <li><a href="{{ route('account.orders.index') }}" class="text-text-muted hover:text-primary transition-colors">My Orders</a></li>
                        <li><a href="{{ route('cart.index') }}" class="text-text-muted hover:text-primary transition-colors">Cart</a></li>
                    @else
                        <li><a href="{{ route('login') }}" class="text-text-muted hover:text-primary transition-colors">Log In</a></li>
                        <li><a href="{{ route('register') }}" class="text-text-muted hover:text-primary transition-colors">Create an account</a></li>
                    @endauth
                </ul>
            </nav>

            <div class="md:col-span-2">
                <p class="text-label-md font-semibold text-text mb-4">Start learning</p>
                <p class="text-body-sm text-text-muted mb-3">Browse the catalog and find your next course.</p>
                <x-ui.button href="{{ route('products.index') }}" size="sm">Explore catalog</x-ui.button>
            </div>
        </div>

        <div class="pt-8">
            <p class="text-body-sm text-text-muted">
                &copy; {{ date('Y') }} Episteme. All rights reserved.
            </p>
        </div>
    </div>
</footer>
