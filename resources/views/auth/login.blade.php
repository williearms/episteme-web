<x-layouts.app title="Log In — Episteme" description="Log in to your Episteme account.">
    <x-auth.card title="Log in" subtitle="Welcome back — pick up where you left off.">
        @session('status')
            <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
        @endsession

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <x-ui.text-input label="Email" name="email" type="email" required autofocus autocomplete="username" />

            <x-ui.text-input label="Password" name="password" type="password" required autocomplete="current-password" />

            <label class="flex items-center gap-2 text-body-sm text-text-muted">
                <input type="checkbox" name="remember" class="rounded border-border/60 text-accent focus:ring-accent">
                Remember me
            </label>

            <x-ui.button type="submit" class="w-full">Log in</x-ui.button>

            <div class="flex items-center justify-between text-label-md">
                <a href="{{ route('password.request') }}" class="text-primary font-semibold hover:underline">Forgot your password?</a>
                <a href="{{ route('register') }}" class="text-text-muted hover:text-primary transition-colors">Create an account</a>
            </div>
        </form>
    </x-auth.card>
</x-layouts.app>
