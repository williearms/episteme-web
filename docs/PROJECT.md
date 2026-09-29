# Episteme

Episteme is an education marketplace and learning platform: discover, purchase, and (eventually)
learn from courses, workshops, webinars, mentoring, and other educational products.

Long-term user journey: **Discover → Explore → Understand → Purchase → Learn**.

This repository currently covers **Phase 0 (foundation)**, **Phase 1 (design system + UI)**,
**Phase 2 (public website + discovery)**, **Phase 3 (product & catalog domain)**, **Phase 4
(authentication & user area)**, and **Phase 5 (commerce, checkout & payment)**. See
[`PROGRESS.md`](./PROGRESS.md) for status, architectural decisions, and scope boundaries.

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

## Product domain (Phase 3)

The base `products` table holds everything common to every product type. Type-specific metadata
lives in **one-to-one subtype tables** instead of a generic JSON blob (Phase 2's `meta` column) or a
sprawl of nullable columns:

```text
products
    ├── courses          (courses.product_id → products.id, unique)
    ├── webinars         (webinars.product_id → products.id, unique)
    ├── workshops        (workshops.product_id → products.id, unique)
    ├── bootcamps        (bootcamps.product_id → products.id, unique)
    ├── mentorings       (mentorings.product_id → products.id, unique)
    └── digital_products (digital_products.product_id → products.id, unique)
```

| Model | Table | Key fields |
|---|---|---|
| `App\Models\Category` | `categories` | `name`, `slug` (unique, route key), `description`, `icon`, `status` (`active`/`inactive`) |
| `App\Models\Instructor` | `instructors` | `name`, `slug` (unique), `headline`, `bio`, `avatar` (nullable — the instructor card falls back to initials) |
| `App\Models\Product` | `products` | `category_id`, `instructor_id` (nullable), `title`, `slug` (unique, route key), `short_description`, `description`, `outcomes`/`requirements` (JSON arrays), `audience` (text), `product_type` (`App\Enums\ProductType`), `level`, `price` (integer, `0` = free), `currency` (`IDR`), `thumbnail`, `status` (`App\Enums\ProductStatus`: draft/published/archived), `published_at`, `featured` |
| `App\Models\Course` | `courses` | `duration_minutes`, `total_modules`, `total_lessons`, `delivery_type` (`self_paced`/`cohort_based`) |
| `App\Models\Webinar` | `webinars` | `event_date`, `start_time`, `end_time`, `timezone`, `platform` (name only — **never** meeting URLs/credentials, this table is publicly readable) |
| `App\Models\Workshop` | `workshops` | `event_date`, `duration_minutes`, `mode` (`online`/`offline`/`hybrid`), `location` |
| `App\Models\Bootcamp` | `bootcamps` | `duration`, `schedule`, `cohort`, `delivery_mode` |
| `App\Models\Mentoring` | `mentorings` | `session_duration_minutes`, `number_of_sessions`, `session_type` (`1_on_1`/`group`), `delivery_mode` |
| `App\Models\DigitalProduct` | `digital_products` | `file_type`, `file_size`, `access_type` |

`Product` exposes `hasOne` relations for all six subtypes plus a `type_details` accessor that
resolves to whichever one matches `product_type` — Blade and controllers use `$product->type_details`
or the named relation (`$product->course`, `$product->webinar`, …) rather than branching on a raw
string. `Product::LEVELS` remains a plain const map (no enum — it's used in exactly one place and
didn't earn the abstraction); `ProductType`/`ProductStatus` are backed enums because they're
referenced everywhere (filters, badges, seeders, route binding, tests) and a typo in a raw string
would fail silently.

**Why subtype tables instead of Phase 2's `meta` JSON column:** the JSON blob mixed genuinely
type-specific fields (event dates, session counts) with generic marketing copy (outcomes,
requirements) in one untyped bag, with no FK integrity and no way to add a NOT NULL/indexed
column for one type without affecting the others. Splitting it: (1) lets each subtype enforce its
own shape via real columns, (2) makes `products.id → subtype.product_id` a real FK with
`cascadeOnDelete` (a subtype row cannot outlive its product), (3) keeps `products` itself lean —
only `outcomes`/`requirements`/`audience` stayed there since those apply across most types, not one.

**Visibility:** `Product::scopePublished()` (`status = published`) is the only way a product is ever
shown publicly — this now correctly also excludes `archived`, not just `draft`.
`Product::resolveRouteBinding()` applies this scope automatically, so `/products/{slug}` 404s for a
draft, archived, or nonexistent slug with no extra code in the controller.

**Search & filtering:** unchanged from Phase 2 — `Product::scopeFilter()` / `scopeSort()` still back
both `ProductController::index` and `CategoryController::show`. Added scopes `ofType()`,
`forCategory()`, `byInstructor()` (small, named wrappers used internally by `scopeFilter`, not
speculative additions) and `withTypeDetails()` (eager-loads all six subtype relations in 6
constrained queries total — not per-row — for pages that need to display them).

**Routes:** unchanged from Phase 2 (`home`, `products.index`, `products.show`, `categories.show`) —
Phase 3 only had to make the underlying queries and views compatible with the new schema, not add
or rename any URL.

**Seed data:** `ProductSeeder` now creates a matching subtype row for every product. 20 products
total: 18 published (spanning all 6 types, 3 levels, free/paid), 1 `draft`, and 1 `archived` — the
extra archived example specifically exercises the "archived is hidden too, not just draft" rule in
both manual QA and `ProductDomainTest`. Run via `php artisan migrate:fresh --seed`.

## Commerce domain (Phase 5)

A per-user `Cart` → `Checkout` → `Order` → `Payment` flow sits on top of the product domain above,
without touching enrollment/course-access (Phase 6):

```text
users
    ├── carts (1:1, lazy-created)
    │       └── cart_items (product_id, quantity, unit_price — a display cache, never trusted at checkout)
    └── orders (1:many)
            ├── order_items — a full SNAPSHOT of product_title/product_type/unit_price at purchase time
            └── payments (1:many) — every payment ATTEMPT for the order, not just the latest
```

| Model | Table | Key fields |
|---|---|---|
| `App\Models\Cart` | `carts` | `user_id` (unique — one cart per user) |
| `App\Models\CartItem` | `cart_items` | `cart_id`, `product_id`, `quantity`, `unit_price`; unique `[cart_id, product_id]` |
| `App\Models\Order` | `orders` | `user_id`, `order_number` (unique, e.g. `EP-20260928-000123`), `status` (`App\Enums\OrderStatus`), `subtotal`/`discount`/`total`, `currency` |
| `App\Models\OrderItem` | `order_items` | `order_id`, `product_id` (nullable, `nullOnDelete`), `product_title`, `product_type`, `quantity`, `unit_price`, `subtotal` — all snapshotted |
| `App\Models\Payment` | `payments` | `order_id`, `provider`, `provider_payment_id` (unique per attempt), `status` (`App\Enums\PaymentStatus`), `amount`, `paid_at`, `raw_response` |

**Price snapshot principle:** a price is only ever trusted at the moment it's read from
`products.price`, and only the server reads it — `cart_items.unit_price` is a display cache,
recomputed from the live product at checkout, never read back as the order price. See
`docs/PROGRESS.md` → "Phase 5" for the full write-up, including the payment provider abstraction
(`App\Payments\PaymentGateway`, implemented by `MidtransGateway`), the order/payment status
lifecycle, webhook signature/amount/idempotency handling, and local payment testing instructions.

**Routes:** `/cart`, `/checkout`, `/orders/{order}`, `/account/orders` (all `auth`-protected),
`/payments/webhook/midtrans` (CSRF-exempt, server-to-server only).

## Known limitations (Phase 2 & 3)

- No commerce as of Phase 2/3: the product detail CTA was rendered `disabled` with a caption
  explaining enrollment wasn't available yet. **Resolved in Phase 5** — the CTA now adds the product
  to the authenticated user's cart; see "Commerce domain (Phase 5)" above.
- No authentication as of Phase 2/3: "Log In" / "Get Started" in the navbar were inert Phase 1
  placeholders. **Resolved in Phase 4.**
- Pagination uses Laravel's default Tailwind pagination view (`$products->links()`), which is
  functional and accessible but styled with Tailwind's default palette rather than Episteme's
  custom tokens — acceptable for now, worth reskinning later if it reads as visually inconsistent.
- No related-products / "more like this" section on the detail page — not requested and skipped to
  avoid scope creep.
- `sort=popular` was intentionally not implemented — there's no view/purchase-count data to sort by,
  and fabricating one would violate "use only data that actually exists."
- `Category.status` and `Instructor.slug` exist per the Phase 3 spec's field list but have no admin
  UI to change them yet (that's Phase 7) and no public instructor-profile route consumes the slug
  yet — both are there so a later phase doesn't need another migration.
- No Course modules/lessons/quizzes, no webinar meeting access, no mentoring booking/availability —
  the subtype tables are **product description**, not delivery systems; those belong to the Learning
  phase (course content) and Commerce phase (booking, access control) per the Phase 3 brief.

## Scope boundaries

Phase 0–5 explicitly **exclude**: coupons/discount codes, refunds/chargebacks, a general
inventory/capacity engine, enrollment, course progress, webinar/mentoring booking, certificates,
protected digital-file delivery, review backend, user roles beyond guest/authenticated (no
Student/Instructor/Admin), account deletion, and Filament CRUD for any of the new commerce
resources. These belong to later phases and are not implemented here, even where the schema or UI
might hint at them. Authentication and a basic personal account area (`/account`, `/account/profile`,
`/account/settings`) were added in Phase 4 — see `docs/PROGRESS.md` → "Phase 4" for the full
architecture and the reasoning behind not using a starter-kit package. Payments/checkout/orders were
added in Phase 5 — see "Commerce domain (Phase 5)" above and `docs/PROGRESS.md` → "Phase 5" for the
full architecture, security decisions, and known limitations.
