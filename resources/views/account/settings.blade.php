<x-layouts.app title="Settings — Episteme" description="Manage your Episteme account settings.">
    <section class="max-w-[1360px] mx-auto px-6 lg:px-16 py-12">
        <div class="mb-8">
            <span class="font-mono text-code-sm text-primary uppercase tracking-wider font-semibold">Account</span>
            <h1 class="text-headline-xl-mobile md:text-headline-xl text-text tracking-tight mt-1">Settings</h1>
        </div>

        @include('account._nav')

        @session('status')
            <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
        @endsession

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 max-w-4xl">
            <div class="bg-surface border border-border/40 rounded-card p-6 sm:p-8">
                <h2 class="text-headline-sm text-text mb-1">Account information</h2>
                <dl class="mt-4 space-y-3 text-body-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-text-muted">Name</dt>
                        <dd class="text-text font-medium">{{ $user->name }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-text-muted flex-shrink-0">Email</dt>
                        <dd class="text-text font-medium truncate">{{ $user->email }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-text-muted">Email status</dt>
                        <dd class="font-medium {{ $user->hasVerifiedEmail() ? 'text-success-strong' : 'text-error' }}">
                            {{ $user->hasVerifiedEmail() ? 'Verified' : 'Not verified' }}
                        </dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-text-muted">Member since</dt>
                        <dd class="text-text font-medium">{{ $user->created_at->format('d F Y') }}</dd>
                    </div>
                </dl>
                <a href="{{ route('account.profile') }}" class="inline-block mt-5 text-label-md font-semibold text-primary hover:underline">Edit profile &rarr;</a>
            </div>

            <div class="bg-surface border border-border/40 rounded-card p-6 sm:p-8">
                <h2 class="text-headline-sm text-text mb-1">Change password</h2>
                <p class="text-body-sm text-text-muted mb-5">Enter your current password to set a new one.</p>

                <form method="POST" action="{{ route('account.settings.password') }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <x-ui.text-input label="Current password" name="current_password" type="password" required autocomplete="current-password" />
                    <x-ui.text-input label="New password" name="password" type="password" required autocomplete="new-password" />
                    <x-ui.text-input label="Confirm new password" name="password_confirmation" type="password" required autocomplete="new-password" />

                    <x-ui.button type="submit">Update password</x-ui.button>
                </form>
            </div>
        </div>
    </section>
</x-layouts.app>
