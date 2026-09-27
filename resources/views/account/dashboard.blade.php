<x-layouts.app title="My Account — Episteme" description="Your Episteme account dashboard.">
    <section class="max-w-[1360px] mx-auto px-6 lg:px-16 py-12">
        <div class="mb-8">
            <span class="font-mono text-code-sm text-primary uppercase tracking-wider font-semibold">Account</span>
            <h1 class="text-headline-xl-mobile md:text-headline-xl text-text tracking-tight mt-1">
                Welcome, {{ $user->name }}
            </h1>
        </div>

        @include('account._nav')

        @if (request('verified'))
            <x-ui.alert variant="success">Your email address is already verified.</x-ui.alert>
        @endif
        @session('status')
            <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
        @endsession

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-surface border border-border/40 rounded-card p-6">
                <h2 class="text-label-sm font-semibold text-outline uppercase tracking-wider mb-3">Profile</h2>
                <p class="text-headline-sm text-text">{{ $user->name }}</p>
                <p class="text-body-sm text-text-muted mt-1">{{ $user->email }}</p>
                <a href="{{ route('account.profile') }}" class="inline-block mt-4 text-label-md font-semibold text-primary hover:underline">Edit profile &rarr;</a>
            </div>

            <div class="bg-surface border border-border/40 rounded-card p-6">
                <h2 class="text-label-sm font-semibold text-outline uppercase tracking-wider mb-3">Account status</h2>
                @if ($user->hasVerifiedEmail())
                    <p class="flex items-center gap-1.5 text-body-sm text-success font-medium">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">check_circle</span>
                        Email verified
                    </p>
                @else
                    <p class="flex items-center gap-1.5 text-body-sm text-error font-medium">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">error</span>
                        Email not verified
                    </p>
                    <a href="{{ route('verification.notice') }}" class="inline-block mt-2 text-label-md font-semibold text-primary hover:underline">Verify now &rarr;</a>
                @endif
                <p class="text-body-sm text-text-muted mt-3">Member since {{ $user->created_at->format('d F Y') }}</p>
            </div>

            <div class="bg-surface border border-dashed border-border/60 rounded-card p-6 opacity-70">
                <h2 class="text-label-sm font-semibold text-outline uppercase tracking-wider mb-3">My Purchases &amp; Learning</h2>
                <p class="text-body-sm text-text-muted">Coming in a future update — purchases and enrolled courses will appear here once available.</p>
            </div>
        </div>
    </section>
</x-layouts.app>
