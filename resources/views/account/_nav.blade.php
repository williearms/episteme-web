<nav class="flex flex-wrap items-center gap-2 mb-10 pb-6 border-b border-border/40" aria-label="Account navigation">
    <a
        href="{{ route('account.dashboard') }}"
        class="px-3.5 py-1.5 rounded-md text-label-md font-medium transition-colors {{ request()->routeIs('account.dashboard') ? 'bg-accent text-text' : 'text-text-muted hover:text-text hover:bg-surface-subtle' }}"
    >
        Dashboard
    </a>
    <a
        href="{{ route('account.profile') }}"
        class="px-3.5 py-1.5 rounded-md text-label-md font-medium transition-colors {{ request()->routeIs('account.profile') ? 'bg-accent text-text' : 'text-text-muted hover:text-text hover:bg-surface-subtle' }}"
    >
        Profile
    </a>
    <a
        href="{{ route('account.settings') }}"
        class="px-3.5 py-1.5 rounded-md text-label-md font-medium transition-colors {{ request()->routeIs('account.settings') ? 'bg-accent text-text' : 'text-text-muted hover:text-text hover:bg-surface-subtle' }}"
    >
        Settings
    </a>

    <form method="POST" action="{{ route('logout') }}" class="sm:ml-auto">
        @csrf
        <button type="submit" class="px-3.5 py-1.5 rounded-md text-label-md font-medium text-text-muted hover:text-error hover:bg-error/10 transition-colors">
            Logout
        </button>
    </form>
</nav>
