<x-layouts.app title="Create an account — Episteme" description="Create your Episteme account to start exploring learning experiences.">
    <x-auth.card title="Create your account" subtitle="Join Episteme to save your progress and get ready to learn.">
        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            <x-ui.text-input label="Name" name="name" type="text" required autofocus autocomplete="name" />

            <x-ui.text-input label="Email" name="email" type="email" required autocomplete="username" />

            <x-ui.text-input label="Password" name="password" type="password" required autocomplete="new-password" />

            <x-ui.text-input label="Confirm password" name="password_confirmation" type="password" required autocomplete="new-password" />

            <x-ui.button type="submit" class="w-full">Create account</x-ui.button>

            <p class="text-label-md text-text-muted text-center">
                Already have an account?
                <a href="{{ route('login') }}" class="text-primary font-semibold hover:underline">Log in</a>
            </p>
        </form>
    </x-auth.card>
</x-layouts.app>
