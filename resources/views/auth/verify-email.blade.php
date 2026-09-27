<x-layouts.app title="Verify your email — Episteme" description="Verify your Episteme account email address.">
    <x-auth.card title="Verify your email" subtitle="Thanks for signing up! Please verify your email address by clicking the link we just emailed you.">
        @session('status')
            @if (session('status') === 'verification-link-sent')
                <x-ui.alert variant="success">A new verification link has been sent to the email address you provided during registration.</x-ui.alert>
            @else
                <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
            @endif
        @endsession

        <p class="text-body-sm text-text-muted mb-6">
            Didn&rsquo;t receive the email? We can send another one.
        </p>

        <div class="flex flex-wrap items-center gap-4">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <x-ui.button type="submit">Resend verification email</x-ui.button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-ui.button type="submit" variant="outline">Log out</x-ui.button>
            </form>
        </div>
    </x-auth.card>
</x-layouts.app>
