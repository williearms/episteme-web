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

## Phase 2 — Public Website & Discovery

- [x] Homepage connected to database
- [x] Product model
- [x] Category model
- [x] Instructor model
- [x] Product factories
- [x] Product seeders
- [x] Category seeders
- [x] Instructor seeders
- [x] Product listing
- [x] Search
- [x] Category filtering
- [x] Product type filtering
- [x] Level filtering
- [x] Sorting
- [x] Pagination
- [x] Category page
- [x] Product detail page
- [x] Product type presentation
- [x] Empty states
- [x] SEO metadata
- [x] Accessibility review
- [x] Functional tests
- [x] Visual QA
- [x] Documentation updated

See [`docs/PROJECT.md`](./PROJECT.md) ("Product domain (Phase 2)") for the model/route/filtering
design, and ("Known limitations (Phase 2)") for what was deliberately left out.

### Visual QA summary — Phase 2 (2026-09-21)

Checked homepage, catalog (`/products`), a product detail page, and a category page at desktop
(1440px) and mobile (375px), against `docs/design/stitch/index.html` and the Phase 1 design system.

1. **Product card titles** initially looked like default blue hyperlinks in a screenshot — verified
   via computed styles (`getComputedStyle`) that the color is actually `rgb(19, 27, 46)` (`--color-text`)
   with no underline; it was a screenshot-compression artifact, not a real bug. No fix needed.
2. **Filter bar and product grid** match the design system's card/border/radius/spacing tokens; no
   new colors or components were introduced outside the existing token set.
3. **Mobile**: filter fields stack to one column, product grid goes to one column, no horizontal
   overflow, all inputs/selects reach comfortable touch size — verified via screenshot.
4. **Empty states** (no search results, empty category) use the same bordered/dashed-box treatment
   as the rest of the design system, with a real "Clear filters" action rather than a dead end.
5. **Instructor avatars**: since seeded instructors have no photo, the fallback renders initials on
   a `primary-soft` circle instead of a broken image — confirmed on the homepage faculty section.

No layout, typography, or color discrepancies serious enough to require rework were found.

### Accessibility notes — Phase 2

- Product cards use a single real `<a>` around the title with a `absolute inset-0` overlay
  (the standard "stretched link" pattern) so the whole card is clickable via one keyboard-reachable,
  screen-reader-announced link — not a non-semantic `<div onclick>`.
- All filter/search/sort inputs have associated `<label>` elements (visually hidden where the design
  doesn't show a label, e.g. the hero and navbar search boxes).
- Decorative icons (`material-symbols-outlined` spans used as bullets/markers) are `aria-hidden="true"`.
- Focus states use `focus-visible:ring-2 focus-visible:ring-accent` consistently on interactive text
  links inside cards.
- The disabled detail-page CTA has `aria-disabled="true"` in addition to the native `disabled`
  attribute, plus a visible caption explaining why it's inert.

### Phase 1 visual QA summary (2026-09-21)

Compared the rendered homepage against `docs/design/stitch/index.html` at desktop (1440px) and mobile
(375px). Layout, typography, spacing, component sizing, colors, and responsive stacking all match the
Stitch reference closely (hero, product cards, category grid all visually verified via screenshot; the
remaining sections — formats, faculty, dark framework band, events, testimonials, footer — verified via
full-text content diff, matching section-for-section). No discrepancies found worth fixing further for a
Phase 1 prototype.

## Phase 3 — Product & Catalog System

- [x] Inspected Phase 2 implementation before changing anything (migrations, models, controllers,
  routes, Blade components, seeders/factories, tests, catalog/detail pages)
- [x] Base `Product` model finalized (enums for type/status, `currency`, `published_at`, generic
  `outcomes`/`requirements`/`audience`; `duration` and `meta` columns removed)
- [x] Course subtype
- [x] Webinar subtype
- [x] Workshop subtype
- [x] Bootcamp subtype
- [x] Mentoring subtype
- [x] Digital Product subtype
- [x] Product status lifecycle (draft/published/archived) via `ProductStatus` enum + `scopePublished()`
- [x] Pricing normalized (`price` integer + `currency` string, no floats)
- [x] Category refined (`status` field added, `scopeActive()`)
- [x] Instructor refined (`slug` field added, unique)
- [x] Product relationships (`belongsTo` category/instructor, `hasOne` × 6 subtypes, `hasMany` on
  Category/Instructor) plus scopes: `published`, `featured`, `ofType`, `forCategory`, `byInstructor`,
  `filter`, `sort`, `withTypeDetails`
- [x] `ProductType`/`ProductStatus` backed enums (raw strings were getting scattered across
  controllers/Blade/seeders; `Product::LEVELS` deliberately stayed a plain const — one usage site,
  not worth an enum)
- [x] Public catalog compatibility verified (`/products`, `/categories/{slug}`, `/products/{slug}`
  all still work — search, filters, sort, pagination all re-tested)
- [x] Product detail pages show type-specific sections per product type
- [x] Product cards reviewed and adjusted (category label + featured indicator added; duration
  removed — it's now type-specific, shown on the detail page instead)
- [x] Slugs unique (product, category, instructor)
- [x] Seed data covers all 6 product types, both draft and archived states
- [x] Database integrity (FKs with `cascadeOnDelete` product→subtype, unique `product_id` per
  subtype table, indexes on `slug`/`product_type`/`status`/`featured`/`price`)
- [x] Automated tests (18 new + 22 existing, all passing)
- [x] Visual QA
- [x] Documentation updated

### Architecture: base product + subtype tables

**Decision:** replaced Phase 2's single `products.meta` JSON column with six one-to-one subtype
tables (`courses`, `webinars`, `workshops`, `bootcamps`, `mentorings`, `digital_products`), each
`product_id`-keyed with a unique constraint and `cascadeOnDelete`.

**Why:** the brief explicitly asked for this ("Use a base product plus type-specific tables/models
... Do not use polymorphic relationships unless there is a clear architectural reason"), and it's a
genuine improvement over the JSON blob: real columns per type instead of an untyped bag mixing
event dates with marketing copy; a real FK so a subtype row can never outlive its product; and each
subtype can evolve its own schema (e.g. adding a `certificate_type` to `courses` later) without
touching the other five types or the base table.

**Why not polymorphic:** every product has *at most one* subtype, decided entirely by
`product_type` — there's no case where a `courses` row needs to attach to something other than
`Product`. A one-to-one FK is simpler to reason about, query, and constrain than a
`morphable_type`/`morphable_id` pair for a relationship that will only ever point at one model.

**Migration strategy:** rather than one destructive migration, Phase 3 shipped 9 incremental
migrations — one `normalize_products_table_for_catalog_domain` (add currency/published_at/outcomes/
requirements/audience, drop duration/meta), six `create_*_table` migrations for the subtypes, and
two small `add_*` migrations (instructor slug, category status). This repo has no real user data to
preserve (it's seeded demo content, refreshed with `migrate:fresh --seed` on every environment), so
the actual database refresh used a fresh migrate rather than a data-preserving UP migration — but
the migration *files* themselves are additive/incremental, not one big drop-and-recreate, so the
same sequence would work against a database that did have real data to keep.

**`product_type` value normalization:** Phase 2 used `digital-product` (hyphen); the Phase 3 brief's
canonical value is `digital_product` (underscore, matching the other snake_case values). Fixed via
the new `ProductType` enum — there's now exactly one place this value is defined, so this class of
drift can't recur.

### Visual QA summary — Phase 3 (2026-09-21)

Checked the catalog, and course/webinar/bootcamp/digital-product detail pages, at desktop (1440px)
and mobile (375px), after the schema/query/view rewrite.

1. **Webinar "Duration" showed the start time** (e.g. "19:00 WIB") instead of an actual duration —
   caught by reading the rendered sidebar, not by assumption. Fixed `Webinar::durationLabel()` to
   compute `end_time − start_time` (e.g. "1h 30m") when both are present.
2. **Digital product "Duration" showed the file type** (e.g. "PDF") — same class of bug, a static
   file has no duration at all. Fixed `DigitalProduct::durationLabel()` to return `null`, which
   correctly hides the row/icon rather than showing a nonsensical value.
3. **Product cards**: new category label and "★ Featured" tag render correctly and match the
   existing card's spacing/typography/radius — no new visual language introduced.
4. **Bootcamp detail page** ("Program details": duration/schedule/cohort/delivery mode) is new for
   Phase 3 (Phase 2 didn't distinguish bootcamp from course) and renders correctly using the design
   system's existing `<dl>`/card treatment from the webinar/workshop sections.
5. **Mobile**: catalog filters and product grid remain single-column with no overflow after the
   card layout change (category label + featured badge didn't push anything off-screen).

### Testing notes

`Vite dev server and the `laravel.test` container's PHP process needed restarting after each round
of model/Blade changes (`opcache.validate_timestamps=0`, see below) — visual bugs #1 and #2 above
were only caught because QA was done via a real browser render after restarting, not by trusting
`php artisan test` output alone (the test suite passed the whole time; it checks presence of text,
not whether "Duration: PDF" reads as nonsense to a human).

## Phase 4 — Authentication & User Area

- [x] Inspected before changing anything — confirmed no auth scaffolding existed (no Breeze/
  Fortify/Jetstream in `composer.json`, no `routes/auth.php`, `User` model was the untouched
  Laravel default). The base tables (`users`, `password_reset_tokens`, `sessions`) and
  `config/auth.php` were already present from the Laravel skeleton, just unused.
- [x] Registration (`/register`)
- [x] Login (`/login`) with generic failure messaging + per-credential rate limiting
- [x] Logout (session invalidation + token regeneration)
- [x] Password reset (`/forgot-password`, `/reset-password/{token}`) via `log` mail driver
- [x] Email verification (notice, resend, signed verify link) — implemented, not enforced (see below)
- [x] Authenticated route protection (`auth` middleware on `/account/*` and `logout`)
- [x] Account dashboard (`/account`)
- [x] Profile page (`/account/profile`)
- [x] Account settings (`/account/settings`) with password change
- [x] Authenticated navigation (navbar swaps Log In/Get Started for the user's name + account icon)
- [x] Tests: 31 new (registration, login, logout, password reset, email verification, account
  access, profile, settings) — all passing, 84 total across the whole suite
- [x] Documentation updated

### Architectural decision: hand-built auth instead of a starter kit

**Decision:** implemented registration/login/logout/password-reset/email-verification directly
against Laravel's own `Auth`, `Password`, and notification facades (`Illuminate\Auth\*`), rather
than installing Laravel Breeze, Fortify, or Jetstream.

**Why:** the brief's own guidance pulled in two directions — "do not build authentication manually
from scratch... choose the smallest appropriate starter kit" vs. "do not introduce a separate
authentication visual identity... use the existing design system... avoid unnecessary packages."
Breeze's Blade stack would have scaffolded a parallel layout/component system (its own
`layouts/guest.blade.php`, `layouts/navigation.blade.php`, `components/*`) that would need to be
almost entirely deleted and rewritten to match Episteme's existing `x-layouts.app` /
`x-ui.*` components anyway — every view Breeze generates was going to be replaced. Given that, the
`composer require` + full package footprint bought nothing that hand-written controllers don't
already get for free: `Auth::attempt()`, `Password::sendResetLink()`/`Password::reset()`,
`$user->sendEmailVerificationNotification()`, Laravel's built-in `VerifyEmail`/`ResetPassword`
notifications, the `auth`/`guest` middleware aliases, and the `current_password`/`Password::defaults()`
validation rules are all still "Laravel's established authentication ecosystem" — none of this is
custom cryptography or hand-rolled session handling (which the brief does prohibit). The seven auth
controllers (`app/Http/Controllers/Auth/*`) are intentionally thin — each mirrors what Breeze's own
controller for that flow does, just without the extra vendor package and without generating views
that would be thrown away. This is documented here specifically because it's a deliberate deviation
from the literal "install a starter kit" suggestion, made for design-consistency and package-minimalism
reasons the brief itself prioritizes elsewhere.

### Email verification: implemented, not enforced

`User` implements `MustVerifyEmail`. Registration sends the verification email immediately
(`$user->sendEmailVerificationNotification()`), the notice/resend/verify routes all work, and
verification status is visible on the dashboard, profile, and settings pages. **`/account/*` routes
do *not* require `verified` middleware** — per the brief's own instruction ("do not block basic
account management unnecessarily if verification is not required for the current phase"), an
unverified user can still use their account; they just see a persistent status indicator and a
one-click resend action. This can be tightened later (e.g. gating a future "purchase" action behind
`verified`) without restructuring anything now.

### Security decisions worth calling out

- **Account enumeration**: both the login failure message and the "forgot password" confirmation
  message are deliberately generic and byte-for-byte identical whether or not the email is
  registered. Laravel's password broker's default status strings *do* differ ("We can't find a user
  with that email address" vs. "we have emailed your password reset link"), so
  `PasswordResetLinkController` explicitly normalizes both cases to one message rather than passing
  the broker's string straight through — this deviates from Breeze's own default behavior, chosen
  because the brief explicitly flags enumeration risk in the login section and the same reasoning
  applies to password reset.
- **Rate limiting**: login uses a `RateLimiter`-backed lockout keyed on `email|ip` (5 attempts,
  mirroring the well-known Breeze `LoginRequest` pattern) rather than a blanket route `throttle`,
  since a per-credential lockout can't be used to hammer one specific account by rotating IPs as
  easily as a per-IP-only limit. Password-reset requests and verification-resend use the simpler
  `throttle:` middleware — lower-stakes endpoints, blanket throttling is sufficient there.
- **Session security**: `session()->regenerate()` on login, `invalidate()` + `regenerateToken()` on
  logout — standard Laravel session-fixation and CSRF-token protections, not custom.
- Passwords are validated with `Illuminate\Validation\Rules\Password::defaults()` and hashed via
  `Hash::make()` (bcrypt); nothing is ever logged or echoed back to a view.
- Profile/password updates always act on `Auth::user()` — there's no route parameter identifying
  which account to modify, so there's no IDOR surface to defend; `ProfileTest` still has an explicit
  test proving one user's update never touches another user's row.
- Changing the email address resets `email_verified_at` to `null` and re-sends a verification email
  — a new, unconfirmed address is never silently treated as verified.

### Routes added

| Name | Method + URI |
|---|---|
| `register` | GET/POST `/register` |
| `login` | GET/POST `/login` |
| `logout` | POST `/logout` |
| `password.request` / `password.email` | GET `/forgot-password` / POST `/forgot-password` |
| `password.reset` / `password.store` | GET `/reset-password/{token}` / POST `/reset-password` |
| `verification.notice` / `verification.verify` / `verification.send` | GET `/verify-email`, GET `/verify-email/{id}/{hash}` (signed), POST `/email/verification-notification` |
| `account.dashboard` | GET `/account` |
| `account.profile` / `account.profile.update` | GET `/account/profile` / PATCH `/account/profile` |
| `account.settings` / `account.settings.password` | GET `/account/settings` / PUT `/account/settings/password` |

`/`, `/products`, `/products/{slug}`, `/categories/{slug}` are unchanged and remain fully public —
verified by re-running the full Phase 2/3 test suite (still 53 passing) alongside the 31 new tests.

### New reusable components

`components/ui/text-input.blade.php` (labeled input + inline error, replaces duplicating
label+input+`@error` markup across 8 forms), `components/ui/alert.blade.php` (status/error banner),
`components/auth/card.blade.php` (centered card wrapper shared by the 5 auth pages). No new visual
language — all three compose existing design tokens (`border-border/40`, `rounded-card`,
`text-error`/`text-success`, the existing type scale).

### Known limitations — Phase 4

- No custom-branded transactional emails — verification/reset emails use Laravel's default
  notification templates (functional, `log`-driver-tested, just not visually Episteme-branded). Low
  priority until a real mail provider is wired up in a later phase.
- No account deletion — not requested by the brief, and deleting a user account has real
  implications for the future Commerce/Learning data that will reference it, better deferred.
- No "remember me" persistence testing beyond the checkbox wiring — Laravel's `remember_token`
  mechanics are the framework's own, not re-tested here.
- Registered users are not auto-assigned any role/permission — the brief explicitly scopes this
  phase to Guest vs. Authenticated only; Student/Instructor/Admin roles are out of scope.
- No Filament user management — untouched per the brief; the public account area is plain Blade.

## Phase 5 — Commerce, Checkout & Payment

Adds the transaction layer on top of the Phase 3 product catalog and Phase 4 authentication: a
per-user Cart, a Checkout flow that snapshots prices at purchase time, Orders with a payment
lifecycle, and a Midtrans-backed Payment with webhook confirmation. Deliberately stops short of
enrollment/course-access — see "Known limitations" below.

### Schema

```text
users
  └── carts (1:1)          — one cart per user, created lazily on first use
        └── cart_items      — product_id, quantity, unit_price (a *display* price only — never
                               trusted at checkout, see "Price snapshot strategy")
  └── orders (1:many)
        ├── order_items     — product_title, product_type, quantity, unit_price, subtotal — a full
        │                     SNAPSHOT, independent of the product's current state
        └── payments (1:many) — every payment ATTEMPT for the order, not just the latest
```

`order_items.product_id` is nullable with `nullOnDelete()` — deleting a product later must never
break historical order/receipt data, and every field needed to display a past order is already
copied onto the row. `cart_items` has a unique `[cart_id, product_id]` constraint so "add to cart"
can never create a duplicate row for the same product (see below).

### Price snapshot strategy

This is the one rule the whole phase is built around: **a price is only ever trusted at the moment
it's read from `products.price`, and only the server ever reads it.**

- `CartController::store` looks up the product's current price server-side; the client can send a
  `product_id` and an optional `quantity`, nothing else. `cart_items.unit_price` is a cached display
  value only (so the cart page doesn't need to join `products` to show a subtotal) — it is **never**
  read back at checkout.
- `CheckoutController::store` re-reads `$item->product->price` for every line, right before creating
  the `Order`, even though the same items were priced when added to the cart. If a product's price
  changed between "add to cart" and "checkout", the order uses the price at checkout time, not the
  stale cart price. Covered by `CheckoutTest::test_checkout_uses_the_products_current_price_not_the_stale_cart_price`.
- Cart items whose product has gone `draft`/`archived` since being added are excluded from checkout
  entirely (re-validated via `Product::isPurchasable()`, not read from a cached flag) and left in the
  cart with an inline "no longer available" warning rather than silently dropped or silently charged.

### Payment provider abstraction

`App\Payments\PaymentGateway` is a small interface (`charge()`, `verifyWebhookSignature()`,
`mapStatus()`); `App\Payments\MidtransGateway` is the only implementation. `AppServiceProvider`
binds the interface based on `config('payment.provider')` (from `PAYMENT_PROVIDER` in `.env`) via a
`match` with a `default => throw`, so `CheckoutController`, `OrderController`, and
`PaymentWebhookController` never reference Midtrans directly — they only depend on the interface.

**Why Midtrans, not Xendit:** neither was already configured (checked `composer.json` and `.env`
first, per the brief). Midtrans was chosen because it's the more common default for an
Indonesian-market product like Episteme (all pricing is already `IDR`-only) and its Snap API needs
no SDK — it's plain HTTP via Laravel's own `Http` facade, so no new Composer dependency was added.
**To add another provider later:** implement `PaymentGateway`, add one new `match` arm in
`AppServiceProvider::register()`, add its own webhook route — no other file changes.

**Per-attempt reference, not per-order:** Midtrans requires a unique `order_id` per transaction
attempt, not per Episteme order — reusing one after a failed attempt is rejected by their API. Each
`Payment` row gets its own reference (`{order_number}-P{payment_id}`, stored in
`provider_payment_id`) generated *before* calling the gateway, and that's exactly the value Midtrans
echoes back as `order_id` in its webhook — so `PaymentWebhookController` can look up the exact
attempt a notification is about, unambiguously, even when an order has several attempts.

### Order & payment status lifecycle

- `OrderStatus`: `Pending` → `Paid` | `Cancelled` | `Expired`. A `Failed` *payment* does **not**
  move the order out of `Pending` — the user can retry, which creates a new `Payment` row (never
  reuses or overwrites the failed one) via `PaymentAttemptStarter`, keeping the full attempt history.
- `PaymentStatus`: `Pending`, `Paid`, `Failed`, `Expired`, `Cancelled`. `Paid`/`Expired`/`Cancelled`
  are terminal (`PaymentStatus::isTerminal()`); `Pending`/`Failed` are not, and can transition again.
- A product priced at `0` (free) skips the gateway entirely — `CheckoutController` marks the order
  `Paid` and creates a `provider => 'free'` payment record synchronously, so free webinars/resources
  don't require setting up a real payment flow to "purchase."

### Webhook: signature verification, amount verification, idempotency

`POST /payments/webhook/midtrans` (CSRF-exempted in `bootstrap/app.php` — a provider webhook has no
Laravel session to carry a CSRF token; authenticity is established by the check below instead):

1. **Signature** — `MidtransGateway::verifyWebhookSignature()` recomputes
   `sha512(order_id + status_code + gross_amount + server_key)` and compares with `hash_equals()`
   against the payload's `signature_key`. Any mismatch is rejected with `403` before anything else
   runs.
2. **Amount** — the payload's `gross_amount` must equal the matched `Payment.amount` exactly, or the
   webhook is rejected with `422`. Prevents a tampered/duplicate notification from confirming an
   order for less than it actually costs.
3. **Idempotency** — the matched `Payment` row is re-fetched with `lockForUpdate()` inside a
   transaction; if its status is already terminal, the handler no-ops and returns `200` without
   touching anything. A redelivered webhook (Midtrans retries on non-200/timeouts) can never apply a
   status change twice or double-fire the order-paid transition.

### Security decisions worth calling out

- **Never trust the client for price, subtotal, total, or product identity** — every total in this
  phase is computed server-side from `products.price` at the moment it matters (add-to-cart,
  checkout). The client only ever sends a `product_id` and, optionally, a `quantity`.
- **No exposed cart_item/payment IDs in routes** — cart routes are `/cart/items/{product}`, always
  scoped through `$request->user()->cartOrCreate()`, mirroring the Phase 4 pattern of never accepting
  a route parameter that identifies someone else's resource. There is no cart IDOR surface by
  construction (`CartTest::test_a_user_cannot_modify_a_cart_item_that_belongs_to_another_users_cart`).
  Orders, which *are* looked up by an opaque-but-guessable `order_number`, are protected instead by
  an explicit `OrderPolicy::view()` checked in every controller action that touches one
  (`OrderTest::test_a_user_cannot_view_another_users_order`,
  `test_a_user_cannot_retry_payment_on_another_users_order`).
- **Webhook authenticity** is the signature check above, not IP allowlisting or a shared secret in
  the URL — matches Midtrans's own documented verification method.
- **No sensitive payment data is ever logged or stored** — `payments.raw_response` stores the
  provider's transaction-status payload (status, amounts, timestamps), never card details, API
  credentials, or secrets. `MIDTRANS_SERVER_KEY` never appears in a response, log line, or view.
- **Mass-assignment**: `CartController`/`CheckoutController` never pass request input directly into
  `create()`/`update()` — every write is built from server-derived values (`$product->price`,
  `$product->title`, …), with only `quantity` taken from the client, and even that is validated
  (`integer`, `min:1`).

### Routes added

| Name | Method + URI |
|---|---|
| `cart.index` | GET `/cart` |
| `cart.items.store` | POST `/cart/items` |
| `cart.items.update` / `cart.items.destroy` | PATCH / DELETE `/cart/items/{product}` |
| `checkout.show` / `checkout.store` | GET / POST `/checkout` |
| `orders.show` | GET `/orders/{order}` |
| `orders.retry-payment` | POST `/orders/{order}/retry-payment` |
| `account.orders.index` | GET `/account/orders` |
| `payments.webhook.midtrans` | POST `/payments/webhook/midtrans` (CSRF-exempt) |

All `cart.*`/`checkout.*`/`orders.*`/`account.orders.*` routes require `auth`. The webhook route is
intentionally outside that middleware group — it's a server-to-server callback, not a user action.

### New reusable components

`components/commerce/order-status-badge.blade.php` and `payment-status-badge.blade.php` (thin
wrappers around the existing `<x-ui.badge>`, driven by the enums' own `badgeVariant()` — extended
`<x-ui.badge>` with `success`/`error` variants using the `success`/`error` design tokens that already
existed in `app.css` but had no consumer yet), `components/commerce/order-summary.blade.php` (used
by both the checkout page and the order confirmation page — both just need a line-item list +
subtotal/discount/total, so one component covers both rather than the two the brief suggested),
`components/commerce/cart-item.blade.php` (the editable cart row — quantity form, remove button,
availability warning).

### Known limitations — Phase 5

- No refunds, partial refunds, or chargebacks — out of scope per the brief; `OrderStatus`/
  `PaymentStatus` have no state for it.
- No coupons/discount codes — `orders.discount` exists in the schema (always `0` for now) so adding
  a discount engine later doesn't need another migration, but nothing populates it yet.
- No capacity/inventory limits on webinars, workshops, bootcamps, or mentoring sessions — a product
  can be "purchased" any number of times with no seat cap. Documented here as a known future need,
  not solved, per the brief.
- No enrollment/course access/lesson progress/certificates/protected file delivery — a paid Order
  exists, but nothing yet grants access to anything. That's the whole of Phase 6.
- No Filament admin CRUD for Cart/Order/Payment — untouched per the brief.
- Only one payment provider (Midtrans) is implemented, and only card/Snap-redirect flows are
  exercised in practice — no production credentials exist in this environment, so the real gateway
  round-trip is verified via `Http::fake()` in tests and manual QA confirms the graceful failure path
  (order stays `Pending`, payment marked `Failed`, "Retry Payment" available) when no sandbox key is
  configured, which is the expected local-dev state until a developer adds their own free sandbox key.
- Retrying a payment can, in principle, be spammed by re-clicking "Retry Payment" — no rate limit was
  added since it only ever creates rows scoped to the requesting user's own order and each attempt
  still goes through the same server-side total; not a priority to harden further this phase.

### Local payment testing

1. Register a free account at https://dashboard.sandbox.midtrans.com and copy the **Sandbox** Server
   Key / Client Key into `.env` (`MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`). No production
   credentials are ever needed for local development — `MIDTRANS_IS_PRODUCTION=false` targets
   `app.sandbox.midtrans.com` exclusively.
2. Without any sandbox key configured, checkout still works end-to-end for the important paths:
   the Order and initial `Payment` (status `pending`→`failed`) are created, and the order page shows
   a clear "could not start the payment" message with a "Retry Payment" action — this is what the
   automated tests exercise via `Http::fake()`, and it's also real, reproducible local behavior.
3. To exercise an actual Snap redirect, add real sandbox keys, then use Midtrans's documented test
   card numbers on the Snap page it redirects to.
4. To receive real webhook callbacks locally (optional — not required for the checkout flow itself
   to work), expose `http://localhost:8010/payments/webhook/midtrans` with a tunnel (e.g. `cloudflared
   tunnel --url http://localhost:8010` or `ngrok http 8010`) and paste the resulting HTTPS URL into
   the sandbox dashboard's Payment Notification URL setting. No tunnel configuration is committed to
   the repo.
5. To simulate a webhook without a tunnel, POST a JSON payload to
   `/payments/webhook/midtrans` with a `signature_key` computed as
   `sha512(order_id + status_code + gross_amount + MIDTRANS_SERVER_KEY)` — see
   `tests/Feature/Payments/PaymentWebhookTest::signedPayload()` for a working example.

### Tests added

`tests/Feature/Cart/CartTest.php` (12), `tests/Feature/Checkout/CheckoutTest.php` (9),
`tests/Feature/Orders/OrderTest.php` (8), `tests/Feature/Payments/PaymentWebhookTest.php` (7) — 36
new tests covering: cart add/update/remove and its IDOR-safety, draft/archived products being
unpurchasable, duplicate-add incrementing quantity instead of duplicating rows, the stale-price vs.
current-price checkout scenario, free-product checkout skipping payment, order ownership
authorization, retry creating a new payment attempt, and webhook signature/amount/idempotency
handling. All 120 tests (84 from Phases 2–4 + 36 new) pass — see the end of this document for the
`git` checkpoint.

### Bug caught during this phase's own QA

`User::cartOrCreate()` originally read `$this->cart ?? $this->cart()->create()` — accessing
`$this->cart` caches the (null) relation lookup, but `create()` on the relation doesn't update that
cache. A second call to `cartOrCreate()` on the same in-memory `User` instance (e.g. the navbar and a
controller both resolving the cart during one request, or reusing a user across two requests in a
test via `actingAs`) would find the cached `null` again and try to create a *second* cart, violating
`carts.user_id`'s unique constraint. Fixed by explicitly `setRelation('cart', $cart)` after creating.
Caught by the very tests written for this phase, not by manual QA — a good example of why the
"increment then create" duplicate-add-to-cart test and the free-product checkout test both exist.

## Phase 6 — Learning

Turns a paid order into something the buyer can actually use: enrollment, a course player with
progress, protected access details for live products, and protected downloads. No detailed brief
was supplied for this phase, so the scope below was derived from the roadmap (`docs/PHASE.md`:
"Bisa belajar") and the deferrals recorded in Phases 3–5.

### Schema

```text
users ── enrollments (unique [user_id, product_id]) ── products
              │  order_id → orders (nullOnDelete)
              └── lesson_progress (unique [enrollment_id, lesson_id]) ── lessons
courses ── course_modules ── lessons
products ── product_accesses (1:1: join_url, instructions)
digital_products.file_path  (private disk)
```

`course_modules`/`lessons` hang off the existing `courses` subtype row, so Phase 3's public
`total_modules`/`total_lessons` stay as the marketing counts. `product_accesses` is a **separate
table** from the subtype tables on purpose: Phase 3 made those publicly readable (and said never to
put meeting links in them); this one is only ever rendered for enrolled users.

### How enrollment is granted

`Order::grantEnrollments()` runs `firstOrCreate` per order item. It is called from exactly two
places: the free-order branch of `CheckoutController::store` and, inside the existing transaction,
`PaymentWebhookController` when the mapped status is `Paid`. The unique `[user_id, product_id]` key
makes it idempotent, so a redelivered webhook or a repeat purchase cannot create a second row.
Nothing else grants access — placing a pending order, or a failed/expired payment, enrolls nobody.

Already-owned products are blocked at both ends: `CartController::store` refuses them, and
`CheckoutController::purchasableItems()` filters them out if one slipped into the cart earlier.

### Access control

Every learning route resolves the enrollment **through the signed-in user's own enrollments**, by
product slug (`$request->user()->enrollments()->whereHas('product', …)->firstOrFail()`). There is no
enrollment id in any URL, so nothing to guess; a non-owner gets `404`, not `403`, so the response
doesn't confirm the product exists. This is the same pattern as Phase 5's cart, and is why no new
Policy was needed. Lesson routes additionally assert the lesson belongs to *this* enrollment's
course, so a lesson id from another course can't be read or marked complete through it.

Because access is via the enrollment and not `Product::resolveRouteBinding()` (published-only), a
product that is later archived stays accessible to the people who already bought it.

### Progress

`Enrollment::progressPercent()` = completed lessons ÷ the course's lessons. `markLesson()` is
idempotent (unique key) and keeps `completed_at` in sync both ways — finishing the last lesson sets
it, undoing a lesson clears it.

### Protected downloads

`digital_products.file_path` points at the **private** `local` disk. `LearningController::download`
streams it via `Storage::download()` after the enrollment check; the path never appears in any URL
or view (covered by a test). Missing file → `404`.

### Routes added

| Name | Method + URI |
|---|---|
| `account.learning.index` | GET `/account/learning` |
| `learning.show` | GET `/learning/{slug}` |
| `learning.lessons.show` | GET `/learning/{slug}/lessons/{lesson}` |
| `learning.lessons.complete` / `.uncomplete` | POST / DELETE `/learning/{slug}/lessons/{lesson}/complete` |
| `learning.download` | GET `/learning/{slug}/download` |

All require `auth`.

### UI added

`account/learning`, `learning/show` (course outline + progress / live-access card / download card,
chosen by product type), `learning/lesson`, and `components/learning/progress-bar`. Account nav gets
a "Learning" tab; the dashboard's placeholder card is replaced; the paid order page links to My
Learning; the product page swaps "Add to cart" for "Go to My Learning" when owned; the cart flags
owned items.

### Seed data

`LearningContentSeeder` (called from `DatabaseSeeder`) generates modules/lessons per course from its
`total_modules`/`total_lessons`, a `product_accesses` row per webinar/workshop/bootcamp/mentoring
(join links are `meet.example.com` placeholders), and a small placeholder text file per digital
product. All of it is demo filler, not real content.

**Dev gotcha:** run seeders as the web user — `docker compose exec -u sail laravel.test php artisan
db:seed …`. Run as root (the default), the seeder creates `storage/app/private/digital-products`
owned by root and the web server then can't read the file (download 404s). Fix with
`docker compose exec laravel.test chown -R sail:sail storage/app/private`.

### Tests added

`tests/Feature/Learning/EnrollmentTest.php` (9) and `LearningAccessTest.php` (16): enrollment on
paid webhook and free checkout only, webhook/duplicate idempotency, owned-product cart/checkout
blocking, per-user isolation of enrollments and progress, cross-course lesson rejection, progress
and completion math (including undo), archived-product access, join details hidden from the public
page, download authorization and path non-disclosure. 145 tests pass (120 + 25).

### Known limitations — Phase 6

- No quizzes, assessments, or certificates — not in the roadmap's "Bisa belajar" milestone as
  scoped here.
- Lessons are plain text; no video hosting, attachments, or rich content editor.
- No admin UI to author courses, lessons, access details, or files, and no way to revoke an
  enrollment — all Phase 7 (Admin).
- No capacity/seat limits and no calendar/booking for mentoring sessions — an enrolled mentoring
  buyer just gets the access card. Still a documented future need.
- Webinar/workshop "join" shows a link with no time-gating (visible as soon as the user is enrolled).
- No refunds, so nothing ever revokes access after payment.
- Demo "downloads" are `.txt` placeholders even where `file_type` says PDF/ZIP.
- E2E/browser tests and in-depth QA are intentionally deferred to Phase 8; this phase got a
  smoke check (all learning routes + one screenshot) only.

## Phase 7 — Admin

Operational back office on the Filament panel that was installed in Phase 0 but never used. Lives at
`/admin`. As with Phase 6, no detailed brief was supplied; scope comes from the roadmap
("Admin — Operasional") and the "belongs to Phase 7" deferrals in earlier phases.

### Access control — the important part

Filament's default is that `canAccessPanel()` returns `true` **in the `local` environment** for any
`User` that doesn't implement `FilamentUser` — i.e. until this phase, *any registered user* could
open `/admin` locally. Now:

- `users.is_admin` (boolean, default `false`). It is **not** in `User`'s `#[Fillable]`, so it can't be
  set through registration, profile update, or any mass-assignment. Tests post a crafted `is_admin=1`
  to both and assert it is ignored.
- `User implements FilamentUser`; `canAccessPanel()` returns `(bool) $this->is_admin`. The `(bool)`
  matters: a user created earlier in the same request has no `is_admin` attribute loaded (`null`),
  and the strict `bool` return type turned that into a 500 instead of a 403 (caught by the tests).
- Non-admins get `403` on every `/admin` page; guests are redirected to `/admin/login`.
- First admin: `php artisan episteme:make-admin {email}` (promotes an *existing* user — there is no
  seeded admin with a known password). After that, admins can promote/demote others in the panel.
  An admin can't demote themselves, so at least one admin always remains.

### What the panel manages

| Group | Resource | Notes |
|---|---|---|
| Catalog | Categories, Instructors | Simple (modal) CRUD. |
| Catalog | Products | Full form incl. the subtype's fields (shown per `product_type`), marketing copy, and — for live types — the enrolled-only join link/instructions; digital products get a private-disk file upload. |
| Sales | Orders | **Read-only** (list + view with items and payment attempts). |
| Learning | Course modules | Modules per course, with lessons managed inside each module. |
| Learning | Enrollments | Grant access manually (`order_id` null = "Manual grant"), revoke. |
| Users | Users | List only, plus make/remove admin. |

Dashboard: revenue (paid orders only), paid/pending order counts, enrollments, users.

### Decisions worth calling out

- **Orders are read-only.** An order's status only changes via the verified payment webhook. An editable
  status would let staff create "paid" orders with no payment and no enrollment. Access for
  exceptional cases goes through *Enrollments → Grant access* instead, which is explicit and visible.
- **Destructive deletes are blocked where the schema cascades.** `products.category_id` cascades, so
  a category with products can't be deleted; `enrollments.product_id` cascades, so a product that has
  been bought (enrollment or order item) can't be deleted — archive it. Implemented in each
  resource's `canDelete()`, which Filament applies to both table and page actions.
- **`product_type` is locked after creation.** Changing it would orphan the old subtype row and
  leave the product without its new one.
- **`OrderPolicy` is bypassed in the panel on purpose.** It encodes the storefront rule "only the
  owner sees an order", and Filament would otherwise apply it and show admins nothing.
  `OrderResource` checks `is_admin` directly instead. The storefront rule is unchanged — a test
  asserts an admin still gets `403` on `/orders/{number}` for someone else's order.
- **`is_admin` changes via explicit confirmed actions** (`forceFill`), never a form field, so it
  can't ride along on an ordinary edit.
- **Payment `raw_response` is not displayed** in the panel.
- Products and orders are addressed by their route key in admin URLs (`/admin/products/{slug}/edit`,
  `/admin/orders/{order_number}`), not by id. Drafts/archived products are editable in the panel —
  Filament doesn't go through the storefront's published-only `resolveRouteBinding()`.
- Empty "outcomes"/"requirements" lists start with zero rows. The default single required row made
  it impossible to save a product without filling them (caught by the tests).

### Tests added

`tests/Feature/Admin/AdminAccessTest.php` (9) and `AdminResourcesTest.php` (17): guest/non-admin/admin
access to every page, no self-promotion through registration or profile update, the `make-admin`
command, admin vs. storefront order visibility, product creation per type (right subtype row, no
stray rows, member access, `published_at`), slug uniqueness, locked type, delete guards,
read-only orders, manual grant/duplicate/revoke, promote/demote and the self-demotion guard,
lessons via the relation manager, and the dashboard totals. 171 tests pass (145 + 26).

### Known limitations — Phase 7

- Single admin role — no finer permissions (e.g. support staff who can see orders but not edit
  products). Add Filament Shield or policies if that's needed.
- No refund/cancel/manual-payment actions and no coupon management (not built in Phase 5).
- Product thumbnails and instructor avatars are URL fields, not uploads, because the storefront
  renders them as raw URLs. Only the digital-product file is an upload (private disk).
- No audit log of admin actions.
- No bulk actions, CSV export, or reporting beyond the five dashboard figures.
- Lessons are plain text; no rich editor.
- The panel uses Filament's own styling (Sky primary colour), not the storefront design system.
- Browser/E2E testing is deferred to Phase 8; this phase got Livewire feature tests plus a smoke
  check of every panel page and the product edit/create forms.

## Follow-up to Phases 6–7 — quizzes, rich lessons, product button

Raised after hands-on testing. This **supersedes** the Phase 6 limitation "No quizzes, assessments…"
and the Phase 7 limitation "Lessons are plain text; no rich editor".

### Product page button

Once the user's cart already holds a product, the primary button is replaced by "In your cart —
view cart" (a link to `/cart`) instead of offering "Enroll Now" again. Owned products still show "Go
to My Learning", which wins over "in cart". `ProductController::show` computes `$owned`/`$inCart` so
the view stays dumb. (Server-side, adding an already-carted product still increments its quantity —
unchanged from Phase 5 — but the UI no longer offers it.)

### Rich-text lessons

- Lesson `content` is now HTML, edited in the admin with Filament's `RichEditor` (Trix): headings,
  bold/italic/strike, links, lists, blockquote, code block, image attachments, undo/redo. There is
  no raw-HTML source view.
- **Image attachments** are stored on the `public` disk under `lesson-attachments/`, so the dev
  environment needs a `public/storage` symlink (`php artisan storage:link`; inside the container,
  `ln -s ../storage/app/public public/storage` gives a relative link that also resolves from the
  WSL host). Filament 3.3's RichEditor has **no type or size limit** for attachments, which on a
  public disk would let any file (`.php`, `.html`, `.svg`) be uploaded, so uploads go through
  `App\Support\LessonAttachment`: the type is decided from the file's *actual bytes* (never the
  client's name or declared type), only PNG/JPEG/WebP/GIF are accepted, ≤ 2 MB, and the stored name
  is `{uuid}.{detected extension}` — a script renamed `.png` or an SVG is rejected. The tests use real
  files rather than `UploadedFile::fake()`, which derives its type from the *filename* and would have
  let the renamed-script case pass for the wrong reason. Images are publicly reachable by URL (they're
  not access-controlled like downloads).
- **Sanitized on output.** The lesson page prints `content_html` unescaped, so every render goes
  through `App\Support\RichText` (`symfony/html-sanitizer`, now an explicit dependency): scripts,
  iframes, event handlers, inline styles and `javascript:` links are removed; safe links get
  `rel="noopener noreferrer"` + `target="_blank"`. This is the *only* place author content is printed
  unescaped. Defence in depth — admins are trusted, but a compromised admin account or pasted snippet
  must not become stored XSS against every student.
- A data migration wraps existing plain-text lessons in `<p>` so they keep their paragraphs.
  Not reversible (documented in the migration).
- `.lesson-content` styles in `resources/css/app.css` — Tailwind's preflight strips list/heading
  styling, so it's restated for that container.

### Quizzes / assessments

```text
course_modules ── quizzes (1:1, unique course_module_id)
                    └── quiz_questions ── quiz_options (is_correct)
enrollments ── quiz_attempts (stored score + answers) ── quizzes
```

- **One quiz per module.** A "final assessment" is a quiz in the last module. Each quiz has a pass
  mark (default 70%); retakes are unlimited and a quiz counts as done once *any* attempt passed.
- **Question types** are derived, not stored: one correct option → radio, several → checkboxes
  ("Select all that apply"). Multiple-answer questions are all-or-nothing: the chosen set must equal
  the correct set. (No essay/free-text — it can't be auto-graded.)
- **Grading is server-side only** (`Quiz::grade()`). The quiz page renders option text without
  `is_correct`, and explanations/correct answers appear only on the result page *after* submitting
  (tests assert neither is in the pre-submit HTML). Option ids that don't belong to the question are
  ignored, blanks score zero, and a question mistakenly configured with no correct option can't
  hand out free credit.
- **The score is stored**, not recomputed: editing a quiz later can't change a past grade. The
  result page's per-question review reads current questions, so it can drift from what the student
  saw if the quiz was edited afterwards — the percentage and pass/fail never do.
- **Course completion now requires passing every quiz** as well as completing every lesson;
  `progressPercent()` counts both. Quizzes with no questions are invisible to students and never
  block completion. Courses without quizzes behave exactly as before.
- **Access** follows the Phase 6 rule: everything resolves through the user's own enrollment; a quiz
  from another course, another user's attempt, or a quiz with no questions is a `404`.
- **Admin** (Learning → Quizzes): module picker, pass mark, and a nested repeater of questions →
  options with a "Correct" toggle. Validation: ≥ 1 question, ≥ 2 options per question, ≥ 1 correct
  option, one quiz per module. A quiz that already has student attempts can't be deleted.
- Seed data adds a placeholder "Kuis Akhir" (3 generic demo questions) to each course's last module.

### Routes added

`learning.quizzes.show` (GET `/learning/{slug}/quizzes/{quiz}`), `learning.quizzes.attempts.store`
(POST `…/attempts`), `learning.quizzes.attempts.show` (GET `…/attempts/{attempt}`). All `auth`.

### Tests added

`QuizTest` (18), `LessonContentTest` (6), `ProductCtaTest` (6), `AdminQuizTest` (8). 209 tests pass.

### Still not built

Certificates, quiz time limits / attempt caps, question banks or randomization, essay questions,
non-image attachments in lessons, video embeds, a raw-HTML source view (swap in TinyMCE if that's wanted — it
needs a Filament plugin and a licence/API-key decision), and quiz analytics in the admin.

## Phase 8 (part 1) — QA pass

Deployment is deliberately **not** part of this pass (hosting not chosen yet). Automated browser
E2E tests and a Pest migration are also still to do. This was a hands-on QA sweep; every bug found
got a regression test. 242 tests pass (217 → 242).

### How it was done

1. **Route crawl** — ~65 URLs (every product incl. draft/archived, every category, auth, account,
   learning, admin, orders of other users) requested as guest / plain user / admin; checked for any 5xx
   and for the right 200/302/403/404.
2. **Security probes** — reflected XSS in search, IDOR on orders/learning/quizzes, forged webhooks,
   CSRF, response headers, rate limits, mass assignment.
3. **Query audit** — query counts and repeated-query (N+1) detection for the main pages
   (max 25 queries; nothing repeated ≥ 5×).
4. **Browser DOM audit** — headings, labels, alt text, unnamed controls, duplicate ids, dead links,
   horizontal overflow, console errors; at 1280 px **and 375 px**.
5. **Production-cache compatibility** — `config:cache`, `route:cache`, `view:cache`, `event:cache`,
   `filament:cache-components` all work and the app serves normally; no `env()` outside `config/`.
6. **Contrast** — WCAG ratios computed for the token pairs.
7. **Edge cases** — absurd numbers, double-submit, boundary values.

### Bugs found and fixed

| # | Finding | Severity | Fix |
|---|---|---|---|
| 1 | **Forged payment webhook accepted when `MIDTRANS_SERVER_KEY` is empty** — the signature is `sha512(… + key)`, computable by anyone with an empty key, so anyone could mark an order paid (and grant access). | **Critical** | A missing key now rejects every webhook (and `charge()` refuses to run). |
| 2 | Malformed webhook body (array where a string is expected) → 500 | Medium | Every signed field must be present and scalar; otherwise a clean 403. |
| 3 | **Rate limits shared one bucket.** Laravel's numeric `throttle:N,1` keys by user id (or IP) only, so five login attempts also blocked "forgot password", and cart clicks could 429 checkout. (Pre-existing from Phase 4.) | Medium | Named limiters in `AppServiceProvider` (`login`, `register`, `forgot-password`, `checkout`, `quiz`, …), each with its own key. |
| 4 | No throttle on `register`, `reset-password`, password change, cart, **checkout/retry (which call the payment API)**, lesson progress, quiz submit, profile update | Medium | Added via the named limiters. |
| 5 | **No security headers** and `X-Powered-By` leaked the PHP version | Medium | `SecurityHeaders` middleware (nosniff, X-Frame-Options, Referrer-Policy, Permissions-Policy, HSTS only over HTTPS in production); `X-Powered-By` removed. No CSP — see below. |
| 6 | Quantity/price/total had no upper bound → **500** (unsigned-int overflow) on cart update and checkout | Medium | Per-line quantity ≤ 10, order total ≤ Rp2.000.000.000, admin price ≤ Rp100.000.000, and bounds on other admin numeric fields. |
| 7 | Double-clicking "Place order" could create two orders (both requests read the same cart) | Medium | Cart rows are `lockForUpdate`d inside the checkout transaction; the loser bails to the cart. |
| 8 | Times shown in UTC (an order "placed 06:49" at 13:49 WIB) | Medium | `APP_TIMEZONE`, default `Asia/Jakarta` (also in `.env.example`). Existing rows written under UTC will show 7 h off; irrelevant for a fresh database. |
| 9 | Generic, unstyled Laravel error pages | Low | Branded 404/403/419/429; self-contained 500/503 (no layout/DB/asset dependency). |
| 10 | Footer: 12 `#` placeholder links, anchors that only worked on the homepage, a **newsletter form that did nothing**, a static "All systems operational" claim; two dead homepage CTAs (`#catalog`, "Request Cohort Syllabus") | Low | Footer rewritten with only real links; homepage CTAs point at the catalog/bootcamps. |
| 11 | Catalog page had no `<h1>` | Low | `section-heading` accepts `as="h1"`. |
| 12 | Contrast failures: `text-outline` 4.0–4.5:1; green text 2.5:1; accent badge 3.0:1 | Low | `outline` → `#626D74`, new `success-strong` for green text, accent badge text → dark. |

Verified **fine**: reflected XSS (escaped), IDOR on orders/learning/quizzes (403/404), CSRF, archived
/draft products (404), no 5xx on any crawled URL, no horizontal overflow at 375 px on any page,
console clean, query counts, production caches, labels/`autocomplete` on auth forms, product-type
toggling in the admin form.

### Needs a product decision (not changed)

- **Fabricated homepage content.** "98.4% Completion Rigor", "Top 1% Domain Specialists" and the two
  testimonials come from the Stitch mock-up; they are not real data. Replace with real content or
  remove before launch.
- **No Privacy Policy / Terms of Service pages** (the placeholder footer links were removed). Needed
  before taking real payments; legal text has to come from you.
- **Email verification is not enforced** — unverified users can buy and learn (Phase 4 decision).
- **Locale is English** (`APP_LOCALE=en`) so dates print as "25 November 2026" while the content is
  Indonesian.
- **No Content-Security-Policy.** The storefront loads fonts from Google, Vite is a separate origin in
  dev, and Filament/Livewire use inline scripts, so a useful CSP needs nonces and per-environment
  tuning. Do it with deployment.
- Product images are `placehold.co` URLs when a product has no thumbnail (an external dependency).

### Production checklist (for the deploy step)

`APP_ENV=production`, `APP_DEBUG=false`, a real `APP_KEY`, `APP_URL` with https,
`SESSION_SECURE_COOKIE=true`, real `MIDTRANS_*` keys (and the webhook URL in the Midtrans
dashboard), a real mail driver (currently `log`), persistent/object storage for
`lesson-attachments` and private digital files, `php artisan storage:link`, `expose_php=Off`, run
`php artisan optimize` + `filament:cache-components`, and give the container a non-dev OPcache
config (the dev one disables timestamp validation).

### Still to do in Phase 8

Optional Pest migration, a deploy target + production Dockerfile + CI (run both test suites there),
dependency updates (below), and the product decisions above.

## Phase 8 (part 2) — End-to-end tests

Playwright (`tests/e2e/`, Chromium), run with `bash scripts/e2e.sh`. 14 tests (2 setup + 12
scenarios); they passed twice back to back.

| Spec | Journey |
|---|---|
| `auth` | register → logout → login; wrong password; redirect back to the intended page after login |
| `learning` | free course: add to cart → button changes → checkout → My Learning → lesson progress → fail quiz → retry and pass → product page offers learning |
| `checkout` | paid course: gateway receives the server price → pending grants nothing → forged and underpaid notifications refused → genuine (twice) settles → access granted once; declined → retry → access |
| `access` | guests redirected; non-admin 403; unowned learning 404; another customer's order 403 |
| `admin` | create and publish a webinar → visible publicly without the join link; edit a lesson in the rich editor → student sees the formatting |
| `mobile` | no sideways scroll at 375 px on 12 pages |

### How it stays isolated and deterministic

- **Own database** (`e2e`, created by the script) and **own server** on port 8011 with environment
  overrides (`tests/e2e/env.mjs`). `e2e:reset` (`migrate:fresh` + `E2eSeeder`) refuses to run unless
  both `APP_ENV=e2e` and the connected database is `e2e`; the seeder (which creates a fixed-password
  admin) refuses outside `e2e`. PHPUnit covers both guards.
- **Local Midtrans stand-in** (`tests/e2e/stub/`). `MIDTRANS_SNAP_URL` redirects the gateway to it;
  the override is **ignored when `APP_ENV=production`** (tested). Tests then send real signed webhooks.
- Built assets go to `public/build-e2e` with their own "hot" file, so a running Vite dev server
  neither affects the tests nor is affected by them.
- One worker (shared database); the admin signs in once and the session is reused, so the login rate
  limit isn't tripped.
- Every test also fails on any uncaught JavaScript error.
- The suite was checked for teeth: with access granting temporarily removed from the webhook, the
  paid-checkout test failed at the access assertion.

### Found by this suite

- **Filament 3.3.55 bug:** closing a modal that contains a `RichEditor` throws
  `Cannot set properties of undefined (setting 'value')` from `rich-editor.js` (its state watcher runs
  after the element is removed). It fires after the save and has no visible effect. `fixtures.mjs`
  ignores exactly that message *from that file*; remove the exception once Filament is updated.

### Not covered

Real Midtrans (needs sandbox keys and network), password reset and email verification (covered by
PHPUnit), file uploads in the admin, other browsers than Chromium, and visual regression.

### Dependency advisories (as of 2026-11-26, not yet applied)

`composer audit`: 12 advisories in `laravel/framework`, `league/commonmark` (one high), `league/csv`,
and `symfony/{http-foundation,http-kernel,mime,routing,yaml}`; a `composer update` of those picks up
newer releases. `npm audit`: 4 (2 critical, 2 high) in dev-only tooling (`shell-quote` via
`concurrently`, `vite`, `source-map-js`); `npm audit --omit=dev` is clean. Apply and re-run both
suites before deploying.

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

- Phase 2 added `/products` (catalog) and `/products/{slug}` (detail) using Laravel's conventional
  resource naming, since the Stitch export never named a `/catalog` URL — the earlier Phase 1 note
  about this is now resolved.
- Laravel's default skeleton now ships `AGENTS.md`/`CLAUDE.md` with Laravel Boost bootstrap
  instructions (composer require + `boost:install`). Boost is agent tooling, unrelated to the
  Episteme product scope; since this task explicitly targets a Docker-only workflow (no host PHP),
  Boost setup was left for a follow-up rather than blocking phase work.
- Out of scope through Phase 3 (explicitly deferred to later phases): authentication, registration,
  login, payment gateway, checkout, orders, enrollment, course progress/modules/lessons/quizzes,
  webinar/mentoring booking, certificates, reviews backend, complex user dashboard, marketplace
  transaction logic, Filament CRUD for the new resources, production deployment. See
  `docs/PROJECT.md` → "Known limitations (Phase 2 & 3)" for specifics.
- Phase 3 deliberately did **not** touch Filament — the brief was explicit that admin CRUD belongs to
  a later phase, and the models/relationships/validation are structured so Filament resources can be
  added later without another schema rewrite (e.g. every subtype is a normal `hasOne`/`belongsTo`
  relation Filament's relation managers understand natively).
