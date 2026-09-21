# Episteme Development Progress

## Phase 0
- [x] Laravel foundation (Laravel 13, scaffolded via Composer in Docker)
- [x] Docker (Laravel Sail: `laravel.test` app container + `mysql` 8.4, app on host port 8010 — 8000 was taken by an unrelated WordPress container already running on this machine)
- [x] MySQL (migrations run successfully against it)
- [x] Tailwind (v4, CSS-first `@theme` tokens in `resources/css/app.css`)
- [x] Filament (installed, admin panel scaffolded at `/admin`, not yet themed/used)
- [x] Documentation (`docs/PROJECT.md`, `docs/PROGRESS.md`, `docs/design/design-system.md`)
- [x] Git baseline

## Phase 1
- [x] Stitch analysis (single-page homepage reference — see `docs/design/design-system.md`)
- [x] Design tokens (Tailwind v4 `@theme` in `resources/css/app.css`)
- [x] Typography (Hanken Grotesk + JetBrains Mono + Material Symbols, loaded via direct Google Fonts CDN
  `<link>` tags in the layout head — matching how the Stitch reference itself loads fonts)
- [x] Global layout (`resources/views/components/layouts/app.blade.php`)
- [x] Navbar (`components/layout/navbar.blade.php`)
- [x] Footer (`components/layout/footer.blade.php`)
- [x] Buttons (`components/ui/button.blade.php`)
- [x] Cards (product + instructor components; category/format/event/testimonial built inline in the homepage since each appears once)
- [x] Product card (`components/product/card.blade.php`)
- [x] Responsive system (verified at desktop 1440px and mobile 375px — nav collapses, hero stacks, tags wrap, stats stay in 3 columns)
- [x] Homepage prototype (`resources/views/home.blade.php`, all Stitch sections implemented)
- [x] Visual QA — done, see below

## Visual QA summary (2026-09-21)

Compared the rendered homepage against `docs/design/stitch/index.html` at desktop (1440px) and mobile
(375px). Layout, typography, spacing, component sizing, colors, and responsive stacking all match the
Stitch reference closely (hero, product cards, category grid all visually verified via screenshot; the
remaining sections — formats, faculty, dark framework band, events, testimonials, footer — verified via
full-text content diff, matching section-for-section). No discrepancies found worth fixing further for a
Phase 1 prototype.

## Resolved: environment performance issues (2026-09-18 → 2026-09-21)

Two separate issues blocked visual QA for a while; both are now fixed and documented so they don't need
re-diagnosing:

1. **OPcache disabled for the CLI SAPI.** The Sail 8.5 runtime uses `php artisan serve` (not PHP-FPM), which
   runs under the `cli` SAPI, and that SAPI ships with `opcache.enable_cli=Off` by default. Every request
   was recompiling the entire framework + Filament + Livewire from source. Fixed permanently via a bind-mounted
   `docker/php/opcache-dev.ini` (see `compose.yaml`) setting `opcache.enable_cli=1` and
   `opcache.validate_timestamps=0`. Note: with `validate_timestamps=0`, code changes require restarting the
   `laravel.test` container to be picked up.
2. **Windows bind-mount I/O overhead.** Even with OPcache fixed, requests were still taking 20–150+ seconds
   (and worsening) because the project lived on `D:\PROJECTS\EPISTEME`, a Windows-drive path bind-mounted
   into the container. Benchmarked directly: writing 200 small files took **2.3s** on the bind mount vs
   **0.06s** on the container's native filesystem (~40x). Laravel + Filament + Livewire do thousands of file
   stats/reads per request (autoloading, view/component discovery), so this overhead compounded into
   minutes. Windows Defender exclusions did **not** fix it — confirmed via benchmark that the bind-mount
   translation itself is the bottleneck, not AV scanning.

   **Fix:** the project now lives natively inside the WSL2 Ubuntu filesystem at
   `\\wsl.localhost\Ubuntu\home\willi\projects\episteme` (accessible from Windows via that UNC path), and
   `docker compose` is run from *inside* WSL2 (`wsl -d Ubuntu -- bash -lc 'cd ~/projects/episteme && docker compose ...'`)
   so Docker Desktop's WSL2 backend mounts it at native speed. Requests dropped to under 1s. The old
   `D:\PROJECTS\EPISTEME` copy still exists as a historical artifact but is no longer the working copy —
   **all future work should target the WSL2 path.**

To resume work: `wsl -d Ubuntu -- bash -lc 'cd ~/projects/episteme && docker compose up -d'`, then
(if Vite isn't already running) `docker compose exec -d laravel.test npm run dev -- --host 0.0.0.0`.
App: http://localhost:8010

## Notes / deferred items

- No `/catalog` page exists in the Stitch reference (single homepage export only), so none was
  built in Phase 1. Revisit when a catalog design reference is provided.
- Laravel's default skeleton now ships `AGENTS.md`/`CLAUDE.md` with Laravel Boost bootstrap
  instructions (composer require + `boost:install`). Boost is agent tooling, unrelated to the
  Episteme product scope; since this task explicitly targets a Docker-only workflow (no host PHP),
  Boost setup was left for a follow-up rather than blocking Phase 0/1 work.
- Out of scope for Phase 0/1 (explicitly deferred to later phases): payment gateway, checkout,
  orders, enrollment, course progress, webinar/mentoring booking, certificates, reviews backend,
  complex user dashboard, marketplace transaction logic, production deployment.
