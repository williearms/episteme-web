# Episteme Design System

Source of truth: [`docs/design/stitch/index.html`](./stitch/index.html) (Google Stitch export, homepage only).
This document translates that visual reference into the tokens and components implemented in
`tailwind.config.js` and `resources/views/components/`.

> The Stitch export uses Material Design 3–style token names (`primary`, `on-surface`,
> `surface-container-low`, …). We kept the underlying color/type values (they already match the
> requested Sky Blue direction almost exactly) but renamed the tokens to plain, framework-agnostic
> names so they read naturally in Blade/Tailwind classes.

## Colors

| Token | Value | Stitch source | Usage |
|---|---|---|---|
| `background` | `#FAF8FF` | `background` | Page background (very soft lavender-white, not pure white — gives Episteme its cool, editorial tone) |
| `surface` | `#FFFFFF` | `surface-container-lowest` | Card / panel background |
| `surface-subtle` | `#F2F3FF` | `surface-container-low` | Section tint bands (e.g. Categories section) |
| `surface-muted` | `#EAEDFF` | `surface-container` | Icon chips, subtle fills |
| `surface-strong` | `#E2E7FF` | `surface-container-high` | Hover fills, stronger chips |
| `text` | `#131B2E` | `on-surface` | Primary text, headings |
| `text-muted` | `#3E484F` | `on-surface-variant` | Body copy on light backgrounds |
| `outline` | `#6E7980` | `outline` | Tertiary text, icon placeholders |
| `border` | `#BDC8D1` (used at ~40% opacity — "hairline") | `outline-variant` | Card/section hairline borders |
| `primary` | `#00668A` | `primary` | Links, labels, eyebrow text, icon accents |
| `primary-hover` | `#006398` | `secondary` | Hover state for primary text/links |
| `primary-soft` | `#C4E7FF` | `primary-fixed` | Soft tinted backgrounds behind primary content |
| `accent` (Sky Blue) | `#38BDF8` | `primary-container` | Primary CTA fill, highlight dot, active tab, top borders |
| `lavender` | `#6D3BD7` | `tertiary` | Secondary accent (icon chips, node-graph lines) |
| `inverse` | `#283044` | `inverse-surface` | Dark "framework" band background |
| `inverse-text` | `#FAF8FF` | `surface` (on inverse) | Text on the dark band |
| `success` | `#10B981` (Tailwind `emerald-500`) | ad hoc in Stitch | Status dots ("Active Cohort", "All systems operational"); Phase 5 added `<x-ui.badge variant="success">` for paid orders/payments |
| `error` | `#BA1A1A` | `error` | Form/validation errors; Phase 5 added `<x-ui.badge variant="error">` for cancelled/failed/expired orders and payments |

Semantic aliases used in components: `border` is always applied as `border-border/40` (a hairline,
never a full-strength line) — this is the single most repeated visual signature in the Stitch
reference and is encoded as the `.hairline` utility.

## Typography

Fonts: **Hanken Grotesk** (headings, body, labels) + **JetBrains Mono** (eyebrows, tags, small
uppercase labels, price/stat figures) + Material Symbols Outlined (icons).

| Token | Size / Line-height / Tracking / Weight | Usage |
|---|---|---|
| `display-hero` | 4.5rem / 1.02 / -0.04em / 700 (mobile: 2.75rem / 1.08 / -0.03em) | Hero H1 only |
| `headline-xl` | 3rem / 1.1 / -0.03em / 600 (mobile: 2rem / 1.15 / -0.02em) | Section H2 |
| `headline-lg` | 2rem / 1.2 / -0.025em / 600 | Large sub-sections |
| `headline-md` | 1.5rem / 1.25 / -0.02em / 600 | Card/format headings |
| `headline-sm` | 1.25rem / 1.3 / -0.015em / 600 | Product/instructor card titles, stat numbers |
| `body-lead` | 1.125rem / 1.55 / -0.01em / 400 | Section intros, hero subhead, testimonial quotes |
| `body-md` | 0.9375rem / 1.5 / -0.005em / 400 | Default body copy |
| `body-sm` | 0.8125rem / 1.45 / 0em / 400 | Card descriptions, meta text |
| `label-md` | 0.875rem / 1.2 / -0.01em / 500 | Nav links, buttons |
| `label-sm` | 0.75rem / 1 / 0.04em / 600, uppercase | Eyebrows, badges, micro-labels |
| `code-sm` | 0.8125rem / 1.4 / -0.02em / 500, JetBrains Mono | Eyebrow tags, price figures, terminal-style status text |

Hierarchy rule: every section follows **eyebrow (code-sm, uppercase, primary) → H2
(headline-xl) → optional lead paragraph (body-lead, text-muted)**. This is implemented as the
`<x-ui.section-heading>` component.

## Spacing

- Page container: `max-w-[1360px] mx-auto`, gutter `px-6` (mobile) / `px-16` (desktop).
- Section rhythm: `py-20` (5rem) for standard sections, `py-24` (6rem) for the dark framework band
  and the final CTA.
- Card padding: `p-5` (product cards), `p-6` (category/instructor cards), `p-7`–`p-8` (format
  blocks, testimonials).
- Grid gaps: `gap-4` (dense category grid) to `gap-8` (instructor grid, testimonials).

## Radius

| Token | Value | Usage |
|---|---|---|
| `sm` (`DEFAULT`) | 0.25rem | Small chips |
| `md` (`lg`) | 0.5rem | Buttons, badges, inputs |
| `card` (`xl`) | 0.75rem | Product/category/instructor cards |
| `2xl` | 1rem | Format blocks, testimonials, hero graphic panel |
| `3xl` | 1.5rem | Final CTA banner |
| `full` | 9999px | Pills (nav search, tags, status dots) |

## Shadows

Deliberately restrained — two levels only:

- `shadow-sm` — default resting state for cards, hero panel, buttons.
- `shadow-md` — hover state for cards and primary CTAs only.

No large ambient shadows, no colored shadows except one subtle glow on the "Practice" step icon in
the framework band (`shadow-[0_0_15px_rgba(56,189,248,0.3)]`) — kept as a one-off, not a token.

## Components

| Component | Blade path | Notes |
|---|---|---|
| Button | `components/ui/button.blade.php` | Variants: `filled` (accent bg), `outline` (hairline border), `ghost` (text only). Sizes: `sm`, `md`, `lg`. |
| Badge | `components/ui/badge.blade.php` | Pill label. Variants: `neutral`/`accent`/`lavender` (product-type tags), `meta` (meta tags), `success`/`error` (order/payment status — added Phase 5, using the `success`/`error` tokens that existed but had no consumer before). |
| Input | `components/ui/input.blade.php` | Search-style input with optional leading icon slot. |
| Section heading | `components/ui/section-heading.blade.php` | Eyebrow + H2 + optional lead paragraph, optional trailing slot (e.g. "View all →" link). |
| Navbar | `components/layout/navbar.blade.php` | Sticky, blurred, logo + nav links + search + auth CTAs. |
| Footer | `components/layout/footer.blade.php` | 5-column editorial footer with newsletter form. |
| Product card | `components/product/card.blade.php` | Takes a single `:product` (Eloquent `Product`) prop. Renders thumbnail, type badge, level badge, a "★ Featured" tag when applicable, category name, title (accessible stretched-link to the detail page), short description, instructor, price. Rating/learner-count/duration were dropped from the card (Phase 3) — no reviews model exists, and duration is now type-specific (subtype tables), shown on the detail page instead of forcing a lowest-common-denominator string onto every card. |
| Instructor card | `components/instructor/card.blade.php` | Avatar (falls back to initials when null), name, role, bio, optional skill tags, optional footer caption. The Phase 1 "View Faculty Profile →" link was removed — there's no instructor profile page, and a dead link would violate the "don't pretend it works" rule. |
| Order status badge | `components/commerce/order-status-badge.blade.php` | Thin wrapper over `<x-ui.badge>`, colored by `OrderStatus::badgeVariant()`. **(Phase 5)** |
| Payment status badge | `components/commerce/payment-status-badge.blade.php` | Same pattern, driven by `PaymentStatus::badgeVariant()`. **(Phase 5)** |
| Order summary | `components/commerce/order-summary.blade.php` | Line items + subtotal/discount/total. Shared by the checkout page (cart items) and the order confirmation page (order items) — both need the same shape. **(Phase 5)** |
| Progress bar | `components/learning/progress-bar.blade.php` | `percent` prop, accent fill on a `surface-muted` track, with `role="progressbar"`. **(Phase 6)** |
| Cart item row | `components/commerce/cart-item.blade.php` | Editable cart row: thumbnail, title, quantity form, remove button, an inline warning if the product is no longer purchasable. **(Phase 5)** |

Components not yet built as standalone: category card, format card, event row, testimonial card,
and the dark CTA band are still implemented directly in the homepage view since each only appears
once — they'll be extracted into components once a second place needs them.

### Shared page partials (Phase 2)

Not design-system components (they're page-composition partials, not reusable UI atoms), but
worth documenting since two pages depend on them:

| Partial | Path | Used by |
|---|---|---|
| Filter bar | `resources/views/products/_filters.blade.php` | Catalog (`products.index`) and category (`categories.show`) — a single GET form (search, category*, type, level, price, sort). `showCategoryFilter` hides the category `<select>` on the category page since it's implicit in the URL. |
| Result grid | `resources/views/products/_grid.blade.php` | Same two pages — result count, product grid, empty state, pagination. |

Both pages call these identically so the search/filter/sort/pagination/empty-state behavior and
markup never has to be maintained in two places.

## Pages

- `/` — homepage, matching the full Stitch reference section-for-section. Hero search, featured
  products, categories, and instructors are now database-driven (Phase 2); pedagogical formats,
  the framework band, testimonials, and the final CTA remain static editorial content, per Stitch.
- `/products` — catalog: search, filter (category/type/level/price), sort, paginated grid. **(Phase 2)**
- `/products/{product}` — product detail, route-bound by slug, scoped to published products only. **(Phase 2)**
- `/categories/{category}` — category page, reuses the catalog's filter/grid partials scoped to one category. **(Phase 2)**
- `/cart` — cart page: editable line items, subtotal, empty state. **(Phase 5, auth-only)**
- `/checkout` — read-only order review (buyer details, line items, order summary) + "Place Order" action. **(Phase 5, auth-only)**
- `/orders/{order}` — order confirmation/detail: status badges, payment info, "Retry Payment" when applicable. **(Phase 5, auth-only, owner-only via `OrderPolicy`)**
- `/account/orders` — order history list, empty state. **(Phase 5, auth-only)**
- `/account/learning` — enrolled products as cards with progress, empty state. **(Phase 6, auth-only)**
- `/learning/{slug}` — per-type learning page: course outline + progress, live-access card, or download card. **(Phase 6, enrolled-only)**
- `/learning/{slug}/lessons/{lesson}` — lesson reader with module sidebar, complete/undo, previous/next. **(Phase 6, enrolled-only)**

No `/catalog` URL exists — the Stitch reference never named one, and `/products` is the
conventional Laravel resource name for this content.
