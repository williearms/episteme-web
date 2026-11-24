# Episteme – Learning Course Web Project

**Episteme** is a web-based learning platform where people can discover, buy, and learn from educational products — online courses, workshops, webinars, bootcamps, mentoring, and digital products — all in one place.

The journey it supports: **Discover → Explore → Understand → Purchase → Learn.**

## ✨ Features

**For learners**

- **Learning Catalog** – Search, filter (category, type, level, price), and sort a catalog of educational products.
- **Six product types** – Courses, webinars, workshops, bootcamps, mentoring, and digital products, each with its own details.
- **Accounts** – Registration, login, password reset, email verification, profile and password management.
- **Cart & Checkout** – Server-side pricing (the browser never decides a price), free orders, and order history.
- **Online payment** – Midtrans (Snap) with signature-verified, idempotent webhooks and a retry flow for failed payments.
- **My Learning** – Access to everything you've bought, unlocked automatically once payment is confirmed.
- **Course player** – Modules and lessons with rich-text content (including images), progress tracking, and completion.
- **Quizzes & assessments** – Single- and multiple-answer questions graded on the server, a pass mark, unlimited retakes, and a review with explanations.
- **Live-session access** – Join links and instructions for webinars, workshops, bootcamps, and mentoring, visible only to enrolled users.
- **Protected downloads** – Digital products are streamed from private storage to buyers only.

**For administrators** (`/admin`)

- **Admin panel (Filament)** – Manage categories, instructors, products (with type-specific details), course modules and lessons, quizzes, and enrollments (grant or revoke access).
- **Orders & users** – Read-only order and payment history; promote or demote admins.
- **Dashboard** – Paid revenue, order counts, enrollments, and users at a glance.

**Security highlights**

- Admin access is a single `is_admin` flag that cannot be set through any form or mass-assignment.
- Lesson HTML is sanitized before display; image uploads are validated by their real content.
- Access to learning content is always resolved through the signed-in user's own enrollment.

> Roles are intentionally simple: guest, signed-in user, and admin. There is no finer-grained permission system yet.

## 🎯 Project Goals

- Centralize educational programs in one discoverable catalog.
- Provide a complete path from browsing to paying to learning.
- Give staff a practical back office for managing content and access.
- Build a foundation for a scalable digital learning ecosystem.

## 🛠️ Technology Stack

| Component | Technology |
|---|---|
| Backend | Laravel 13 (PHP 8.3+; PHP 8.5 in the Docker image) |
| Frontend | Blade templates, Tailwind CSS v4, Vite |
| Database | MySQL 8.4 |
| Admin panel | Filament 3 |
| Payments | Midtrans Snap (sandbox for development) |
| Environment | Docker (Laravel Sail) |
| Testing | PHPUnit feature tests |
| Development Tools | Git, GitHub, AI-Assisted Development |

## 🚀 Getting Started

### Prerequisites

- **Docker Desktop**. On Windows, use the WSL2 backend and keep the project **inside the WSL2 filesystem** (for example `~/projects/episteme`), not on a Windows drive — bind mounts from Windows drives were measured ~40× slower.
- **Git**
- **Composer** on your machine *for the very first install only* (the Docker image is built from files inside `vendor/`). If you don't have PHP/Composer locally, use the one-off container command in step 3.

No local PHP, Node, or MySQL installation is needed after that — everything runs in containers.

### Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/williearms/episteme-web.git
   cd episteme-web
   ```

2. Create your environment file:

   ```bash
   cp .env.example .env
   ```

   The example file is already set up for the Docker environment (MySQL service, site on port 8010, throwaway local database credentials), so no edits are required. Optional tweaks:

   - If port 3306 is already used on your machine, uncomment `FORWARD_DB_PORT` and set it to `3307`. This only changes the port published on your host; the app still talks to `mysql:3306` inside Docker.
   - On Linux/WSL, if `id -u` is not `1000`, set `WWWUSER` and `WWWGROUP` to your user/group id.

3. Install PHP dependencies (first time only):

   ```bash
   composer install
   ```

   Without local Composer, use Laravel's bootstrap container instead:

   ```bash
   docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html \
     laravelsail/php84-composer:latest composer install --ignore-platform-reqs
   ```

4. Start the containers:

   ```bash
   docker compose up -d
   ```

5. Generate the app key, create the database tables with demo data, and link public storage:

   ```bash
   docker compose exec laravel.test php artisan key:generate
   docker compose exec -u sail laravel.test php artisan migrate --seed
   docker compose exec -u sail laravel.test php artisan storage:link
   ```

   Run seeders with `-u sail`: as root they create private files the web server can't read.

6. Install front-end dependencies and start Vite (leave it running):

   ```bash
   docker compose exec laravel.test npm install
   docker compose exec -d laravel.test npm run dev -- --host 0.0.0.0
   ```

7. Open **http://localhost:8010**. Without the Vite step the pages load but appear unstyled.

### Day-to-day

```bash
docker compose up -d                                              # start
docker compose exec -d laravel.test npm run dev -- --host 0.0.0.0 # Vite (re-run after any container restart)
docker compose exec laravel.test php artisan test                 # run the test suite
```

> **PHP changes not showing up?** OPcache timestamp checks are disabled for speed (`docker/php/opcache-dev.ini`). Run `docker compose restart laravel.test` after editing PHP or Blade files, then start Vite again.

### Creating an admin

Register a normal account on the site first, then promote it:

```bash
docker compose exec -u sail laravel.test php artisan episteme:make-admin you@example.com
```

Sign in, then open **http://localhost:8010/admin**. Admins can promote or demote other users from the panel afterwards.

### Payments (optional in development)

Create a free Midtrans sandbox account and add your **sandbox** keys to `.env`:

```dotenv
PAYMENT_PROVIDER=midtrans
MIDTRANS_SERVER_KEY=your-sandbox-server-key
MIDTRANS_CLIENT_KEY=your-sandbox-client-key
MIDTRANS_IS_PRODUCTION=false
```

Without keys, free products work end-to-end; paid checkouts stop at a "payment could not be started" page with a Retry button. Never commit real keys — `.env` is git-ignored. Receiving real webhooks locally needs a tunnel pointing at `/payments/webhook/midtrans`; see `docs/PROGRESS.md` for details.

### Running the tests

```bash
docker compose exec laravel.test php artisan test
```

The suite covers the catalog, authentication, cart and checkout, payments and webhooks, enrollment, learning access, quizzes, and the admin panel.

## 📚 Documentation

| File | Contents |
|---|---|
| [`docs/PROJECT.md`](docs/PROJECT.md) | Overview, domain model, local development, scope boundaries |
| [`docs/PROGRESS.md`](docs/PROGRESS.md) | Phase-by-phase log: architecture, security decisions, known limitations |
| [`docs/design/design-system.md`](docs/design/design-system.md) | Design tokens and reusable Blade components |

## 📁 Project Status

🚧 **In active development.** The product, commerce, learning, and admin features are built and tested; the final quality-assurance and production-readiness phase is still ahead. Behaviour and architecture may still change.

## 🗺️ Roadmap

- [x] **Phase 0** – Project foundation (Laravel, Docker, Tailwind, Filament)
- [x] **Phase 1** – Design system and UI foundation
- [x] **Phase 2** – Public website and product discovery
- [x] **Phase 3** – Product and catalog system (six product types)
- [x] **Phase 4** – Authentication and user area
- [x] **Phase 5** – Commerce: cart, checkout, orders, and payment
- [x] **Phase 6** – Learning: enrollment, course player, live access, downloads
- [x] **Phase 7** – Admin panel
- [x] Quizzes, rich-text lessons, and lesson images
- [ ] **Phase 8** – QA and production: end-to-end tests, in-depth QA, deployment
- [ ] Certificates
- [ ] Coupons, refunds, and seat/capacity limits
- [ ] Finer-grained roles and permissions

## 🤝 Contributing

Contributions, suggestions, and feedback are welcome.

1. Fork this repository.
2. Create a new branch:

   ```bash
   git checkout -b feature/your-feature-name
   ```

3. Make your changes and make sure the tests pass:

   ```bash
   docker compose exec laravel.test php artisan test
   ```

4. Commit your changes:

   ```bash
   git commit -m "Add: your feature description"
   ```

5. Push your branch and open a Pull Request:

   ```bash
   git push origin feature/your-feature-name
   ```

## 📄 License

This project is licensed under the **MIT License**.

See the [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

**Episteme – Learning Course Web Project**

Developed by [williearms](https://github.com/williearms) as a web-based educational platform project.

---

*Building a better way to discover and access learning opportunities.*
