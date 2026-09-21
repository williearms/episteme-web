# Episteme

Episteme is an education marketplace and learning platform: discover, purchase, and (eventually)
learn from courses, workshops, webinars, mentoring, and other educational products.

Long-term user journey: **Discover → Explore → Understand → Purchase → Learn**.

This repository currently covers **Phase 0 (foundation)**, **Phase 1 (design system + UI)**, and
**Phase 2 (public website + discovery)**. See [`PROGRESS.md`](./PROGRESS.md) for status and scope
boundaries.

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

## Product domain (Phase 2)

Three tables back the public discovery experience:

| Model | Table | Key fields |
|---|---|---|
| `App\Models\Category` | `categories` | `name`, `slug` (unique, route key), `description`, `icon` (Material Symbols name) |
| `App\Models\Instructor` | `instructors` | `name`, `headline`, `bio`, `avatar` (nullable — the instructor card falls back to initials) |
| `App\Models\Product` | `products` | `category_id`, `instructor_id` (nullable), `title`, `slug` (unique, route key), `short_description`, `description`, `product_type`, `level`, `duration`, `price` (integer Rupiah, `0` = free), `thumbnail` (nullable — falls back to a placeholder), `status` (`draft`/`published`), `featured`, `meta` (JSON) |

`Product::TYPES` and `Product::LEVELS` are the canonical value→label maps (course/webinar/workshop/
bootcamp/mentoring/digital-product; beginner/intermediate/advanced/all-levels) — referenced directly
from the filter partial and used by the `type_label`/`level_label` accessors.

**`meta` (JSON, nullable)** holds everything that's specific to a product type or a detail-page
section, deliberately kept as one flexible column instead of a dozen mostly-null typed columns:
`outcomes[]` ("what you'll learn"), `requirements[]`, `audience` ("who this is for"), `modules`/
`lessons` (curriculum preview counts), `event_date`/`event_time`/`location`/`speaker` (webinar/
workshop), `format`/`session_duration`/`availability` (mentoring), `file_type`/`access_type`
(digital product). The detail page (`products/show.blade.php`) only renders a section when the
relevant key is present — nothing is forced onto a product type that doesn't have that data.

**Visibility:** `Product::scopePublished()` (`status = 'published'`) is the only way a product is
ever shown publicly. `Product::resolveRouteBinding()` overrides route-model binding to apply this
scope automatically, so `/products/{slug}` 404s for a draft or nonexistent slug with no extra code
in the controller.

**Search & filtering:** `Product::scopeFilter(array $filters)` applies `search` (LIKE across title,
short_description, description, instructor name, category name), `category` (slug), `type`,
`level`, and `price` (`free`/`paid`) — used by both `ProductController::index` and
`CategoryController::show` so the query logic exists in exactly one place. `Product::scopeSort()`
handles `featured` (default), `newest`, `price-low`, `price-high`. All filters are plain query-string
GET parameters (`?search=&category=&type=&level=&price=&sort=`); there's no client-side state.

**Routes:**

| Name | URI | Controller |
|---|---|---|
| `home` | `/` | `HomeController` |
| `products.index` | `/products` | `ProductController@index` |
| `products.show` | `/products/{product}` | `ProductController@show` |
| `categories.show` | `/categories/{category}` | `CategoryController@show` |

**Seed data:** `CategorySeeder` (7 categories), `InstructorSeeder` (7 Indonesian instructors),
`ProductSeeder` (19 products — 18 published across all 6 types/3 levels/free+paid, 1 deliberately
`draft` to exercise the "unpublished never shows publicly" behavior in both manual QA and tests).
Run via `php artisan migrate:fresh --seed` or `php artisan db:seed`.

## Known limitations (Phase 2)

- No commerce: the product detail CTA is rendered `disabled` with a caption explaining enrollment
  isn't available yet — it deliberately does not link anywhere or submit anything.
- No authentication: "Log In" / "Get Started" in the navbar remain the same inert Phase 1 placeholders.
- Pagination uses Laravel's default Tailwind pagination view (`$products->links()`), which is
  functional and accessible but styled with Tailwind's default palette rather than Episteme's
  custom tokens — acceptable for Phase 2, worth reskinning later if it reads as visually inconsistent.
- No related-products / "more like this" section on the detail page — not requested by Phase 2 and
  skipped to avoid scope creep.
- `sort=popular` was intentionally not implemented — there's no view/purchase-count data to sort by,
  and fabricating one would violate "use only data that actually exists."

## Scope boundaries

Phase 0–2 explicitly **exclude**: authentication, registration, login, payments/checkout, orders,
enrollment, course progress, webinar/mentoring booking, certificates, review backend, user
dashboards, and any marketplace transaction logic. These belong to later phases and are not
implemented here, even where the schema or UI might hint at them.
