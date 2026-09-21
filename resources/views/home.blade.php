<x-layouts.app>
    {{-- 1. Hero --}}
    <section class="relative overflow-hidden pt-12 pb-20 lg:pt-20 lg:pb-28 border-b border-border/40">
        <div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(#00668a 1px, transparent 1px); background-size: 24px 24px;"></div>

        <div class="max-w-[1360px] mx-auto px-6 lg:px-16">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                {{-- Left: headline & actions --}}
                <div class="lg:col-span-7 flex flex-col space-y-6">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-surface-strong border border-border/40 w-fit">
                        <span class="w-1.5 h-1.5 rounded-full bg-accent animate-pulse"></span>
                        <span class="font-mono text-code-sm text-primary tracking-tight uppercase">Executive &amp; Engineering Mastery</span>
                    </div>

                    <h1 class="text-display-hero-mobile lg:text-display-hero text-text tracking-tight leading-[1.02]">
                        Knowledge into capability.
                    </h1>

                    <p class="text-body-lead text-text-muted max-w-xl">
                        Courses, workshops, webinars, and mentoring designed to turn meaningful learning into practical skills.
                    </p>

                    {{-- Prominent search --}}
                    <form method="GET" action="{{ route('products.index') }}" class="pt-2 max-w-xl" role="search" aria-label="Search products">
                        <div class="bg-surface border border-border/40 rounded-card p-2 shadow-sm focus-within:border-accent transition-all">
                            <div class="flex items-center gap-3 px-2">
                                <span class="material-symbols-outlined text-outline" aria-hidden="true">search</span>
                                <label for="hero-search" class="sr-only">Search courses, webinars, workshops</label>
                                <input type="search" id="hero-search" name="search" placeholder="Search courses, webinars, workshops..." class="w-full bg-transparent border-none text-body-md text-text placeholder:text-outline focus:ring-0 p-1">
                                <button type="submit" class="bg-inverse hover:bg-primary text-inverse-text font-medium text-label-md px-4 py-2.5 rounded-md transition-colors">
                                    Search
                                </button>
                            </div>
                        </div>

                        @if ($categories->isNotEmpty())
                            <div class="flex flex-wrap items-center gap-2 mt-3 pt-1">
                                <span class="text-label-sm text-outline uppercase tracking-wider">Trending:</span>
                                @foreach ($categories->take(4) as $category)
                                    <a href="{{ route('categories.show', $category) }}" class="px-2.5 py-1 rounded-full bg-surface-subtle hover:bg-surface-muted border border-border/40 text-label-sm text-text-muted transition-colors">{{ $category->name }}</a>
                                @endforeach
                            </div>
                        @endif
                    </form>

                    <div class="flex items-center gap-5 pt-3">
                        <x-ui.button href="#explore" size="lg" class="flex items-center gap-2">
                            <span>Explore learning</span>
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                        </x-ui.button>
                        <a href="{{ route('products.index') }}" class="text-text hover:text-primary font-semibold text-label-md transition-colors flex items-center gap-1">
                            Browse all products &rarr;
                        </a>
                    </div>

                    <div class="pt-6 border-t border-border/40 flex items-center gap-8 text-text-muted">
                        <div>
                            <div class="text-headline-sm text-text font-bold">98.4%</div>
                            <div class="text-label-sm text-outline">Completion Rigor</div>
                        </div>
                        <div class="h-8 w-px bg-border/40"></div>
                        <div>
                            <div class="text-headline-sm text-text font-bold">1:1</div>
                            <div class="text-label-sm text-outline">Practitioner Guidance</div>
                        </div>
                        <div class="h-8 w-px bg-border/40"></div>
                        <div>
                            <div class="text-headline-sm text-text font-bold">Top 1%</div>
                            <div class="text-label-sm text-outline">Domain Specialists</div>
                        </div>
                    </div>
                </div>

                {{-- Right: abstract knowledge-graph composition --}}
                <div class="lg:col-span-5 relative">
                    <div class="relative mx-auto w-full max-w-lg aspect-square bg-surface border border-border/40 rounded-2xl p-6 shadow-sm overflow-hidden flex flex-col justify-between">
                        <div class="absolute inset-0 bg-[linear-gradient(to_right,#eaedff_1px,transparent_1px),linear-gradient(to_bottom,#eaedff_1px,transparent_1px)] bg-[size:2rem_2rem] opacity-70"></div>

                        <div class="relative z-10 flex items-center justify-between border-b border-border/40 pb-4">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-primary"></span>
                                <span class="font-mono text-code-sm font-semibold text-text tracking-tight">KNOWLEDGE_VECTOR_GRAPH</span>
                            </div>
                            <span class="font-mono text-[11px] text-outline">STATUS: SYNCHRONIZED</span>
                        </div>

                        <div class="relative z-10 my-auto py-8">
                            <div class="relative h-56 flex items-center justify-center">
                                <div class="absolute w-48 h-48 rounded-full border border-dashed border-border/70"></div>
                                <div class="absolute w-32 h-32 rounded-full border border-accent/60 bg-accent/5"></div>

                                <svg class="absolute inset-0 w-full h-full pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 320 220">
                                    <path d="M60 40 L160 110 L260 50" stroke="#bdc8d1" stroke-dasharray="3 3" stroke-width="1.5" />
                                    <path d="M160 110 L80 180" stroke="#38bdf8" stroke-width="2" />
                                    <path d="M160 110 L240 170" stroke="#00668a" stroke-width="2" />
                                </svg>

                                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-inverse text-inverse-text rounded-card p-3 text-center shadow-md border border-accent/40 z-20">
                                    <div class="font-mono text-[11px] text-accent uppercase font-bold tracking-wider">Core Episteme</div>
                                    <div class="text-headline-sm font-semibold text-white">Direct Capability</div>
                                </div>

                                <div class="absolute top-2 left-4 bg-surface border border-border/40 p-2 rounded-md shadow-sm">
                                    <span class="font-mono text-[11px] text-primary font-semibold">01. Theoretical Model</span>
                                </div>
                                <div class="absolute top-4 right-4 bg-surface border border-border/40 p-2 rounded-md shadow-sm">
                                    <span class="font-mono text-[11px] text-lavender font-semibold">02. Execution Feedback</span>
                                </div>
                                <div class="absolute bottom-4 left-6 bg-surface border border-border/40 p-2 rounded-md shadow-sm">
                                    <span class="font-mono text-[11px] text-primary font-semibold">03. Mentored Synthesis</span>
                                </div>
                                <div class="absolute bottom-4 right-6 bg-surface border border-border/40 p-2 rounded-md shadow-sm">
                                    <span class="font-mono text-[11px] text-text font-semibold">04. Production Delivery</span>
                                </div>
                            </div>
                        </div>

                        <div class="relative z-10 pt-3 border-t border-border/40 flex items-center justify-between text-code-sm font-mono text-outline">
                            <span class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-success"></span>
                                Active Peer Cohort #04
                            </span>
                            <span>48 Verified Capstones</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. Featured Learning --}}
    <section id="explore" class="py-20 max-w-[1360px] mx-auto px-6 lg:px-16 border-b border-border/40">
        <x-ui.section-heading eyebrow="Curated Catalog">
            <x-slot:lead>Curated learning experiences for people who want to build real capability.</x-slot:lead>
            <x-slot:trailing>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('products.index') }}" class="bg-accent text-text font-semibold px-3.5 py-1.5 rounded-md border border-accent text-label-md transition-all">All</a>
                    <a href="{{ route('products.index', ['type' => 'course']) }}" class="bg-surface hover:bg-surface-subtle text-text-muted hover:text-text px-3.5 py-1.5 rounded-md border border-border/40 text-label-md transition-all">Courses</a>
                    <a href="{{ route('products.index', ['type' => 'workshop']) }}" class="bg-surface hover:bg-surface-subtle text-text-muted hover:text-text px-3.5 py-1.5 rounded-md border border-border/40 text-label-md transition-all">Workshops</a>
                    <a href="{{ route('products.index', ['type' => 'mentoring']) }}" class="bg-surface hover:bg-surface-subtle text-text-muted hover:text-text px-3.5 py-1.5 rounded-md border border-border/40 text-label-md transition-all">Mentoring</a>
                    <a href="{{ route('products.index', ['type' => 'webinar']) }}" class="bg-surface hover:bg-surface-subtle text-text-muted hover:text-text px-3.5 py-1.5 rounded-md border border-border/40 text-label-md transition-all">Webinars</a>
                </div>
            </x-slot:trailing>
            Explore what's worth learning.
        </x-ui.section-heading>

        @if ($featuredProducts->isEmpty())
            <p class="text-body-md text-text-muted">No featured products yet — check the <a href="{{ route('products.index') }}" class="text-primary font-semibold hover:underline">full catalog</a> instead.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ($featuredProducts as $product)
                    <x-product.card :product="$product" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- 3. Explore by Discipline --}}
    <section id="categories" class="py-20 bg-surface-subtle/40 border-b border-border/40">
        <div class="max-w-[1360px] mx-auto px-6 lg:px-16">
            <x-ui.section-heading eyebrow="Taxonomy">
                Explore by discipline
            </x-ui.section-heading>

            @php
                $categoryPalette = [
                    ['bg' => 'bg-primary-soft', 'text' => 'text-primary'],
                    ['bg' => 'bg-surface-muted', 'text' => 'text-primary'],
                    ['bg' => 'bg-surface-strong', 'text' => 'text-text'],
                    ['bg' => 'bg-lavender-soft', 'text' => 'text-lavender'],
                    ['bg' => 'bg-surface-highest', 'text' => 'text-primary'],
                ];
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($categories as $category)
                    @php $style = $categoryPalette[$loop->index % count($categoryPalette)]; @endphp
                    <a href="{{ route('categories.show', $category) }}" class="bg-surface border border-border/40 p-6 rounded-card hover:border-accent hover:-translate-y-0.5 transition-all group">
                        <div class="w-10 h-10 rounded-md {{ $style['bg'] }} flex items-center justify-center {{ $style['text'] }} mb-4">
                            <span class="material-symbols-outlined" aria-hidden="true">{{ $category->icon ?? 'category' }}</span>
                        </div>
                        <h3 class="text-headline-sm text-text group-hover:text-primary transition-colors">{{ $category->name }}</h3>
                        @if ($category->description)
                            <p class="text-body-sm text-text-muted mt-1">{{ $category->description }}</p>
                        @endif
                        <span class="inline-block mt-4 text-code-sm font-mono text-outline">{{ $category->products_count }} {{ Str::plural('product', $category->products_count) }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 4. Pedagogical Formats --}}
    <section class="py-20 max-w-[1360px] mx-auto px-6 lg:px-16 border-b border-border/40">
        <div class="max-w-2xl mb-12">
            <span class="font-mono text-code-sm text-primary uppercase tracking-wider font-semibold">Pedagogical Modes</span>
            <h2 class="text-headline-xl-mobile md:text-headline-xl text-text tracking-tight mt-1">
                Designed for deliberate cognitive transfer.
            </h2>
            <p class="text-body-lead text-text-muted mt-2">
                Different problems demand different formats. Choose how you acquire leverage.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $formats = [
                    ['n' => '01', 'title' => 'Self-Paced', 'desc' => 'Learn whenever you want with rigorous asynchronous modules.', 'tag' => 'UNLIMITED ACCESS', 'highlight' => false],
                    ['n' => '02', 'title' => 'Live Workshop', 'desc' => 'Learn together through immersive, practical sprint sessions.', 'tag' => 'COHORT-BOUND', 'highlight' => true],
                    ['n' => '03', 'title' => '1-on-1 Mentoring', 'desc' => 'Get direct career and technical guidance from seasoned practitioners.', 'tag' => 'HIGH ACCELERATION', 'highlight' => false],
                    ['n' => '04', 'title' => 'Focused Webinar', 'desc' => 'Explore cutting-edge developments with leading domain researchers.', 'tag' => 'SINGLE-SESSION SPRINT', 'highlight' => false],
                ];
            @endphp

            @foreach ($formats as $format)
                <div class="bg-surface border border-border/40 {{ $format['highlight'] ? 'border-t-2 border-t-accent' : '' }} rounded-2xl p-7 relative overflow-hidden flex flex-col justify-between">
                    <span class="font-mono text-4xl {{ $format['highlight'] ? 'text-accent' : 'text-border/50' }} font-bold block mb-8">{{ $format['n'] }}</span>
                    <div>
                        <h3 class="text-headline-md text-text mb-2">{{ $format['title'] }}</h3>
                        <p class="text-body-md text-text-muted leading-relaxed">{{ $format['desc'] }}</p>
                    </div>
                    <div class="pt-6 mt-6 border-t border-border/40 flex items-center justify-between text-code-sm font-mono text-primary">
                        <span>{{ $format['tag'] }}</span>
                        <span class="material-symbols-outlined text-sm">north_east</span>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- 5. Faculty & Instructors --}}
    <section id="faculty" class="py-20 max-w-[1360px] mx-auto px-6 lg:px-16 border-b border-border/40">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12">
            <div>
                <span class="font-mono text-code-sm text-primary uppercase tracking-wider font-semibold">Faculty</span>
                <h2 class="text-headline-xl-mobile md:text-headline-xl text-text tracking-tight mt-1">
                    Learn from people who practice what they teach.
                </h2>
            </div>
            <p class="text-body-md text-text-muted max-w-md mt-4 md:mt-0">
                Our mentors lead real teams, ship scaled infrastructure, and write foundational open source code.
            </p>
        </div>

        @if ($instructors->isEmpty())
            <p class="text-body-md text-text-muted">No published instructors yet.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach ($instructors as $instructor)
                    <x-instructor.card
                        :avatar="$instructor->avatar"
                        :name="$instructor->name"
                        :role="$instructor->headline"
                        :bio="$instructor->bio"
                        :footer-label="$instructor->products_count.' published '.\Illuminate\Support\Str::plural('product', $instructor->products_count)"
                    />
                @endforeach
            </div>
        @endif
    </section>

    {{-- 6. Outcome & Capability Framework --}}
    <section class="py-24 bg-inverse text-inverse-text relative overflow-hidden">
        <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full bg-primary/20 blur-3xl pointer-events-none"></div>

        <div class="max-w-[1360px] mx-auto px-6 lg:px-16 relative z-10">
            <div class="max-w-3xl mb-16">
                <span class="font-mono text-code-sm text-accent tracking-wider uppercase font-semibold">The Episteme Framework</span>
                <h2 class="text-headline-xl-mobile lg:text-headline-xl text-inverse-text tracking-tight mt-2">
                    Don't just collect knowledge.<br>Build capability.
                </h2>
                <p class="text-body-lead text-border mt-3 max-w-xl">
                    Passivity is the death of real learning. We structure every curriculum into four connected steps that demand output.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 relative">
                <div class="hidden md:block absolute top-12 left-8 right-8 h-px bg-outline/40 -z-0"></div>

                @php
                    $steps = [
                        ['n' => '01', 'title' => 'Understand', 'desc' => 'Deconstruct first principles. Absorb foundational heuristics before touching abstractions.', 'tag' => 'CORE MODELS', 'glow' => false],
                        ['n' => '02', 'title' => 'Practice', 'desc' => 'Deliberate repetition in sandboxed production environments that simulate reality.', 'tag' => 'SYNTHETIC RUNTIMES', 'glow' => true],
                        ['n' => '03', 'title' => 'Create', 'desc' => 'Synthesize a standalone capstone artifact subjected to rigorous domain-expert review.', 'tag' => 'VERIFIED ARTIFACT', 'glow' => false],
                        ['n' => '04', 'title' => 'Grow', 'desc' => 'Translate your proven capability into higher organizational scope and market value.', 'tag' => 'CAREER LEVERAGE', 'glow' => false],
                    ];
                @endphp

                @foreach ($steps as $step)
                    <div class="relative z-10 flex flex-col">
                        <div class="w-12 h-12 rounded-full bg-inverse border {{ $step['glow'] ? 'border-accent shadow-[0_0_15px_rgba(56,189,248,0.3)]' : 'border-outline/60 shadow-sm' }} flex items-center justify-center text-accent font-mono font-bold text-headline-sm mb-6">
                            {{ $step['n'] }}
                        </div>
                        <h3 class="text-headline-md text-inverse-text mb-2">{{ $step['title'] }}</h3>
                        <p class="text-body-sm text-border">{{ $step['desc'] }}</p>
                        <span class="mt-4 font-mono text-[11px] text-accent">{{ $step['tag'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 7. Upcoming Live Events --}}
    <section id="events" class="py-20 max-w-[1360px] mx-auto px-6 lg:px-16 border-b border-border/40">
        <div class="mb-12">
            <span class="font-mono text-code-sm text-primary uppercase tracking-wider font-semibold">Calendar</span>
            <h2 class="text-headline-xl-mobile md:text-headline-xl text-text tracking-tight mt-1">
                Upcoming live events
            </h2>
        </div>

        @if ($upcomingEvents->isEmpty())
            <p class="text-body-md text-text-muted">No upcoming events scheduled right now — check the <a href="{{ route('products.index', ['type' => 'webinar']) }}" class="text-primary font-semibold hover:underline">webinars &amp; workshops</a> catalog instead.</p>
        @else
            <div class="divide-y divide-border/40 border-y border-border/40">
                @foreach ($upcomingEvents as $event)
                    @php $eventDate = \Illuminate\Support\Carbon::parse($event->meta['event_date']); @endphp
                    <div class="py-6 flex flex-col md:flex-row md:items-center justify-between gap-4 group hover:bg-surface-subtle/30 px-3 -mx-3 rounded-md transition-colors">
                        <div class="flex items-start md:items-center gap-6">
                            <div class="flex flex-col text-center w-16 flex-shrink-0 bg-surface p-2 rounded-md border border-border/40">
                                <span class="font-mono text-headline-sm font-bold text-text">{{ $eventDate->format('d') }}</span>
                                <span class="text-[11px] font-semibold text-outline tracking-wider uppercase">{{ $eventDate->format('M') }}</span>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="bg-surface-strong text-primary text-label-sm px-2 py-0.5 rounded">{{ $event->type_label }}</span>
                                    @if (! empty($event->meta['event_time']))
                                        <span class="text-code-sm font-mono text-outline">{{ $event->meta['event_time'] }}</span>
                                    @endif
                                </div>
                                <h3 class="text-headline-sm text-text group-hover:text-primary transition-colors">
                                    <a href="{{ route('products.show', $event) }}" class="focus:outline-none focus-visible:ring-2 focus-visible:ring-accent rounded-sm">{{ $event->title }}</a>
                                </h3>
                            </div>
                        </div>
                        <div class="flex items-center gap-6 self-end md:self-center">
                            @if ($event->instructor)
                                <span class="text-code-sm font-mono text-outline hidden lg:inline">Host: {{ $event->instructor->name }}</span>
                            @endif
                            <a href="{{ route('products.show', $event) }}" class="text-label-md font-semibold text-primary hover:text-primary-hover flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                                View event &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- 8. Testimonials --}}
    <section class="py-20 max-w-[1360px] mx-auto px-6 lg:px-16 border-b border-border/40">
        <div class="max-w-2xl mb-12">
            <span class="font-mono text-code-sm text-primary uppercase tracking-wider font-semibold">Practitioner Outcomes</span>
            <h2 class="text-headline-xl-mobile md:text-headline-xl text-text tracking-tight mt-1">
                Peer-reviewed career velocity.
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-surface border border-border/40 rounded-2xl p-8 flex flex-col justify-between">
                <blockquote class="text-body-lead text-text leading-relaxed">
                    &ldquo;Most platforms give you a video player and a multiple-choice quiz. Episteme forced me to defend my partition recovery strategy directly against an ex-principal engineer. It replaced two years of trial-and-error in three weeks.&rdquo;
                </blockquote>
                <div class="flex items-center gap-4 mt-8 pt-6 border-t border-border/40">
                    <div class="w-12 h-12 rounded-full overflow-hidden border border-border/40 bg-surface-muted">
                        <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDM2krPj14CRAVmnpp-25zjhnY3nGc3ddXG_FWGaC91LyI69YSJ898mfXCcokonf4yS8O6HHfD36zTeqB8JRTt5dijlWRFFMrNQ6F0qI_81PVdQN4Jik-GmgVsRGfAKN-6CqTwL0KCA0EakBz3JwCMa3hGuOMptrV25WxapNynjmu89bWmBqVGW_A9pabcyXZPClxZJ317Sf7qcAn-KSDFoVLxiGC5tR82XKuoNX-VdhWjW01aiADxBvA" alt="Rian Wicaksono" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <div class="text-headline-sm text-text font-semibold">Rian Wicaksono</div>
                        <div class="text-body-sm text-outline">Lead Backend Architect @ FinTech Horizon</div>
                    </div>
                </div>
            </div>

            <div class="bg-surface border border-border/40 rounded-2xl p-8 flex flex-col justify-between">
                <blockquote class="text-body-lead text-text leading-relaxed">
                    &ldquo;The 1-on-1 mentorship changed how I write internal engineering RFCs. Learning the exact communication mechanics of staff-level practitioners secured my promotion to Engineering Director within four months.&rdquo;
                </blockquote>
                <div class="flex items-center gap-4 mt-8 pt-6 border-t border-border/40">
                    <div class="w-12 h-12 rounded-full overflow-hidden border border-border/40 bg-surface-muted">
                        <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuB3LSHMRS6i-QF0Wg7aqAa7gDTJnkottqOtpiaHmdnTwc_cBq4CjVJPIQV4oZFBbotbzo2wTaA1G1RfYZUpP0JpdY0C4x2ZpAPDqwiCBOBEked0kY1Pkx8ylrO59LHbjE-t9N41OftxfPw8MhZAfpJVtVibS_40BhoMqAkHmBFvwjxq1HUVtj-KFbKUnyfN2LwVy10eaGg4y6lZlEkenOldHx_LO2csVny6pWZRUIlJHrNBiV_kFe_d2g" alt="Amara Kholiq" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <div class="text-headline-sm text-text font-semibold">Amara Kholiq</div>
                        <div class="text-body-sm text-outline">Director of Engineering @ ScaledMedia</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 9. Final CTA --}}
    <section class="py-24 max-w-[1360px] mx-auto px-6 lg:px-16">
        <div class="relative bg-surface border border-border/40 rounded-3xl p-10 lg:p-20 overflow-hidden shadow-sm">
            <div class="absolute inset-0 pointer-events-none opacity-40">
                <svg class="w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 1200 400">
                    <path d="M0,100 C300,180 600,20 1200,80 L1200,400 L0,400 Z" fill="#f2f3ff" />
                    <path d="M0,200 C400,280 800,120 1200,220" stroke="#38bdf8" stroke-dasharray="6 6" stroke-width="1.5" />
                </svg>
            </div>

            <div class="relative z-10 max-w-2xl">
                <span class="font-mono text-code-sm text-primary uppercase tracking-wider font-semibold">Take the step</span>
                <h2 class="text-display-hero-mobile lg:text-headline-xl text-text tracking-tight mt-2 mb-4 leading-tight">
                    Your next capability starts here.
                </h2>
                <p class="text-body-lead text-text-muted mb-8 max-w-xl">
                    Find a learning experience designed for where you want to go next.
                </p>

                <div class="flex flex-wrap items-center gap-4">
                    <x-ui.button href="#catalog" size="lg" class="flex items-center gap-2">
                        <span>Explore Episteme</span>
                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </x-ui.button>
                    <x-ui.button href="#consultation" variant="outline" size="lg">
                        Request Cohort Syllabus
                    </x-ui.button>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
