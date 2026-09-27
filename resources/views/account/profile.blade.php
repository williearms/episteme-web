<x-layouts.app title="Profile — Episteme" description="Manage your Episteme profile information.">
    <section class="max-w-[1360px] mx-auto px-6 lg:px-16 py-12">
        <div class="mb-8">
            <span class="font-mono text-code-sm text-primary uppercase tracking-wider font-semibold">Account</span>
            <h1 class="text-headline-xl-mobile md:text-headline-xl text-text tracking-tight mt-1">Profile</h1>
        </div>

        @include('account._nav')

        @session('status')
            <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
        @endsession

        <div class="max-w-lg bg-surface border border-border/40 rounded-card p-6 sm:p-8">
            <form method="POST" action="{{ route('account.profile.update') }}" class="space-y-5">
                @csrf
                @method('PATCH')

                <x-ui.text-input label="Name" name="name" type="text" :value="$user->name" required autocomplete="name" />

                <div>
                    <x-ui.text-input label="Email" name="email" type="email" :value="$user->email" required autocomplete="username" />
                    @unless ($user->hasVerifiedEmail())
                        <p class="mt-2 text-label-sm text-text-muted">
                            Your email address is unverified.
                            <button type="button" form="resend-verification" class="text-primary font-semibold hover:underline">
                                Click here to re-send the verification email.
                            </button>
                        </p>
                    @endunless
                </div>

                <x-ui.button type="submit">Save changes</x-ui.button>
            </form>
        </div>

        @unless ($user->hasVerifiedEmail())
            <form id="resend-verification" method="POST" action="{{ route('verification.send') }}" class="hidden">
                @csrf
            </form>
        @endunless
    </section>
</x-layouts.app>
