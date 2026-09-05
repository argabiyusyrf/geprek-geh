# AGENTS.md

Vanilla PHP 8.4 MVC e-commerce ("Geprek Geh"), served from `/var/www/html/geprek-geh`. No Composer, no npm, no build step, no tests.

## Setup & run
- Install/seeds DB: `php install.php` (idempotent; recreates schema, re-seeds. On re-run clears only `cart/order_items/orders/products/categories`, not users). Seeded logins: `admin@geprekgeh.com / admin123`, `budi@email.com / customer123`.
- DB config: `config/database.php` (live local creds, committed). Schema: `database/schema.sql`.
- Run: Apache docroot `/var/www/html` serves app at base path `/geprek-geh`, or `php -S localhost:8080 router.php` from project root.
- Verify changes with `php -l file.php` + manual browse at `http://localhost/geprek-geh/` (Playwright browser available). No lint/test tooling exists.

## Architecture
- `index.php` is the ONLY entrypoint: sets up `spl_autoload` (controllers/ plus `Admin\` prefix → `controllers/admin/`) and registers **all routes** there via `$router->get/post()`. Every new URL must be added here (`{param}` patterns supported, e.g. `/products/{slug}`).
- Controllers are plain classes; views in `views/` (Indonesian). `models/` is empty — all DB access through the `Database` singleton (`query/fetchAll/fetchOne/insert/update/delete/count`).
- Core: `Router`, `Database`, `Auth`, `Totp` (2FA), `helpers.php` (must-know globals: `e`, `rupiah`, `slug`, `redirect`, `flash`/`flash_set`, `csrf_token`/`csrf_field`/`verify_csrf`, `time_ago`, `generate_invoice`, `format_status`, `order_log`, `wa_link`).
- Guard convention: call `Auth::requireLogin()` / `Auth::requireAdmin()` at the top of controller methods; check `$_SESSION['role'] === 'admin'`.
- Every POST must render `csrf_field()` in the form and call `verify_csrf()` (or `AuthController` pattern) in the handler.

## Gotchas
- Base path `/geprek-geh` is hardcoded in `.htaccess`, `router.php`, `index.php`, and every view link/redirect (`Auth::logout`, `requireLogin`, etc.). Moving the app requires changing all of them.
- `auto-push.sh` runs every minute via cron: any file change is auto-committed (`auto: <timestamp>`) and pushed to `origin/main`. Do not manually `git add/commit/push` unless asked — your edits are version-controlled automatically. `logs/` and `assets/uploads/` are gitignored.
- Payment proof uploads live in `assets/uploads/` (gitignored).
- Omit dotfiles php lint passes for `public/fonts/*.woff2` (binary, not PHP).
- Static hosting (GitHub Pages) cannot run this app — it requires PHP + MySQL.
- `.github/workflows/opencode.yml` triggers OpenCode on `/oc` or `/opencode` issue comments (needs `OPENCODE_API_KEY` secret); unrelated to app runtime.