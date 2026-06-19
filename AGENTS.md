# TOBI LODA — Agent Guide

## Stack
- **Backend:** PHP 8+ (PDO/MySQL), single-entry router at `api/index.php?action=`
- **Frontend:** Vue 3 (CDN `unpkg.com/vue@3`), Tailwind CSS (CDN), Axios (CDN), Font Awesome (CDN)
- **Database:** MySQL — connection config is `api/db_exemple.php`, copy to `api/db.php` (gitignored)
- **The `package.json` (Next.js) is unused scaffolding** — the app is purely PHP files served by Apache/XAMPP

## Project structure
- `api/index.php` — JSON API router; dispatches `?action=` to functions in `api/functions.php`
- `api/functions.php` — all CRUD logic (sales, orders, products, claims, expenses, clients, notifications)
- `api/config.php` — empty, not in use
- Root `.php` files — each is a full page with an inline Vue 3 SPA (createApp, no build step)
- `sidebar.php` — included in every page
- `sales0.php` — stale backup of `sales.php`, do not edit
- `sql/` — empty directory
- `public/images/` — static assets (logo)

## Database setup
- Copy `api/db_exemple.php` → `api/db.php`, edit credentials
- DB name: `gbemiro`, charset `utf8mb4`
- PDO: `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, emulated prepares off

## Development
- Run via XAMPP/Apache (PHP files served directly, no build step)
- No testing framework, no linting/typechecking for PHP
- No Composer dependencies — pure PHP with CDN frontend assets

## API conventions
- All requests to `api/index.php` with `?action=` parameter
- POST data: JSON body (`php://input`) for most actions, FormData for file uploads/products
- Uploads stored in `api/uploads/payments/` and `api/uploads/order_payments/`
- Notifications auto-created via `addNotification()` in key CRUD operations

## Page router (from sidebar)
- `index.php` — dashboard (Vue counts)
- `sales.php` / `orders.php` / `claims.php` / `expenses.php` / `products.php` / `notifications.php`
- `login.php` — no session guard; all other pages start with `session_start()` + redirect check

## Git notes
- Commits use generic message "any sin" — commit style is informal
- `.gitignore` only hides `api/db.php` (database credentials)
