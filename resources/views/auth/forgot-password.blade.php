<x-layouts.app title="Forgot password — Episteme" description="Reset your Episteme account password.">
    <x-auth.card title="Forgot your password?" subtitle="Enter your email and we'll send you a link to reset it.">
        @session('status')
            <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
        @endsession

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf

            <x-ui.text-input label="Email" name="email" type="email" required autofocus autocomplete="username" />

            <x-ui.button type="submit" class="w-full">Email password reset link</x-ui.button>

            <p class="text-label-md text-text-muted text-center">
                <a href="{{ route('login') }}" class="text-primary font-semibold hover:underline">Back to log in</a>
            </p>
        </form>
    </x-auth.card>
</x-layouts.app>
