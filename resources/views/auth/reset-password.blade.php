<x-layouts.app title="Reset password — Episteme" description="Set a new password for your Episteme account.">
    <x-auth.card title="Set a new password">
        <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <x-ui.text-input label="Email" name="email" type="email" :value="$email" required autofocus autocomplete="username" />

            <x-ui.text-input label="New password" name="password" type="password" required autocomplete="new-password" />

            <x-ui.text-input label="Confirm new password" name="password_confirmation" type="password" required autocomplete="new-password" />

            <x-ui.button type="submit" class="w-full">Reset password</x-ui.button>
        </form>
    </x-auth.card>
</x-layouts.app>
