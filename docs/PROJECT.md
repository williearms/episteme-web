# Episteme

Episteme is an education marketplace and learning platform: discover, purchase, and (eventually)
learn from courses, workshops, webinars, mentoring, and other educational products.

Long-term user journey: **Discover → Explore → Understand → Purchase → Learn**.

This repository currently covers **Phase 0 (project foundation)** and **Phase 1 (design system +
UI foundation)** only. See [`PROGRESS.md`](./PROGRESS.md) for status and scope boundaries.

## Stack

- Laravel 13 (PHP 8.5, via Docker)
- Blade + Tailwind CSS (server-rendered, no SPA framework)
- MySQL 8.4
- Filament (admin panel, installed but not yet used for business resources)
- Docker (Laravel Sail) for local development
- Vite for asset bundling

## Local development

Requires Docker Desktop with WSL2. No local PHP/Composer/Node installation is required — everything
runs inside containers.

**The project lives in the WSL2 filesystem**, not on a Windows drive
(`\\wsl.localhost\Ubuntu\home\willi\projects\episteme`) — Docker Desktop's bind-mount performance for
Windows-drive paths is ~40x slower for the many small file reads Laravel/Filament/Livewire do per
request (see `docs/PROGRESS.md` for the full investigation). Run all commands from inside WSL2:

```bash
wsl -d Ubuntu
cd ~/projects/episteme

docker compose up -d
docker compose exec laravel.test composer install
docker compose exec laravel.test npm install
docker compose exec laravel.test npm run dev -- --host 0.0.0.0
```

App: http://localhost:8010 (8000 is used by an unrelated project on this machine)

Artisan / composer / npm commands can be run via:

```bash
docker compose exec laravel.test php artisan <command>
docker compose exec laravel.test composer <command>
docker compose exec laravel.test npm <command>
```

Note: `docker/php/opcache-dev.ini` (bind-mounted in `compose.yaml`) sets `opcache.validate_timestamps=0`
for dev performance. Restart the `laravel.test` container after PHP source changes if they don't seem to
take effect.

## Design reference

The visual design originates from a Google Stitch export at
[`docs/design/stitch/index.html`](./design/stitch/index.html). It is a **visual reference only**,
not production code — see [`docs/design/design-system.md`](./design/design-system.md) for how it
was translated into Tailwind tokens and Blade components.

## Scope boundaries

Phase 0 and Phase 1 explicitly **exclude**: payments/checkout, orders, enrollment, course
progress, webinar/mentoring booking, certificates, review backend, user dashboards, and any
marketplace transaction logic. These belong to later phases and are not implemented here, even
where the schema or UI might hint at them.
