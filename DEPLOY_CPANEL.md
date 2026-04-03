# Gusto — cPanel Production Deployment Guide

> **Requirements:** PHP 8.3+, MySQL 8+, Node.js 20+ (for build only), Composer 2.x  
> **App:** Laravel 13 + Inertia.js + Vue 3 + Vite

---

## Database Strategy

| Environment | Driver | Why |
|---|---|---|
| Local dev | SQLite (via `.env`) | Zero config, fast iteration |
| Production | MySQL | Concurrent writes, cPanel native, auto-backup |

The codebase defaults to MySQL. Local `.env` overrides it with `DB_CONNECTION=sqlite`.

---

## 1. Pre-Deployment Checklist (Local)

Before uploading, run the production build locally:

```bash
npm run build          # compiles assets into public/build/
composer install --no-dev --optimize-autoloader
```

Verify `public/build/` is populated — this folder goes to the server.

---

## 2. cPanel File Upload

### Option A — Git Clone via cPanel Terminal (recommended)
Most modern cPanel hosts provide a terminal under **Advanced → Terminal**.

```bash
# Navigate to your home dir (NOT public_html)
cd ~

# Clone the repo
git clone https://github.com/kalingobiz/Gusto.git gusto

# Go into the project
cd gusto
git checkout claude/restaurant-order-system-XxnMf   # or main once merged
```

### Option B — Upload via File Manager / FTP
1. Zip the entire project (excluding `node_modules/`).
2. Upload the zip to `~/gusto/` (outside `public_html`).
3. Extract it there.

---

## 3. Document Root Configuration

Laravel's entry point is `public/index.php`. In cPanel you must point the domain's
document root to `~/gusto/public`.

**cPanel → Domains → (your domain) → Document Root → set to:** `gusto/public`

If you cannot change the document root, use a symlink workaround:
```bash
# In cPanel Terminal
rm -rf ~/public_html
ln -s ~/gusto/public ~/public_html
```

---

## 4. Create the MySQL Database

1. **cPanel → MySQL Databases**
   - Create database: `gusto_prod`
   - Create user: `gusto_user` with a strong password
   - Add user to database with **All Privileges**

2. Note down:
   - DB Host (usually `127.0.0.1` or `localhost`)
   - DB Name, DB User, DB Password

---

## 5. Environment Configuration

In cPanel Terminal (inside `~/gusto/`):

```bash
cp .env.example .env
```

Edit `.env` with your production values:

```ini
APP_NAME="Gusto"
APP_ENV=production
APP_KEY=                         # leave blank — generated in step 6
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=single
LOG_LEVEL=error

# ── Database ──────────────────────────────────────────
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gusto_prod
DB_USERNAME=gusto_user
DB_PASSWORD=your_strong_password

# ── Session / Cache ───────────────────────────────────
SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database

# ── Mail (optional — for password resets) ────────────
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=465
MAIL_USERNAME=no-reply@yourdomain.com
MAIL_PASSWORD=your_mail_password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=no-reply@yourdomain.com
MAIL_FROM_NAME="Gusto"
```

---

## 6. Server-Side Setup

Run these commands in cPanel Terminal from `~/gusto/`:

```bash
# 1. Install PHP dependencies (no dev packages)
composer install --no-dev --optimize-autoloader

# 2. Generate application key
php artisan key:generate

# 3. Run migrations
php artisan migrate --force

# 4. Seed initial data (roles, admin user, sample tables)
php artisan db:seed --force

# 5. Cache config, routes, views for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Set storage permissions
chmod -R 775 storage bootstrap/cache
```

> **Note on assets:** `public/build/` should already be uploaded from your local
> `npm run build`. No Node.js is needed on the server.

---

## 7. Storage Symlink

```bash
php artisan storage:link
```

This creates `public/storage → storage/app/public` for uploaded files (QR codes, etc.).

---

## 8. PHP Version & Extensions

In **cPanel → MultiPHP Manager**, set PHP to **8.3** for your domain.

In **cPanel → MultiPHP INI Editor**, ensure these extensions are enabled:

```
extension=pdo_mysql
extension=mbstring
extension=openssl
extension=tokenizer
extension=xml
extension=ctype
extension=json
extension=bcmath
extension=fileinfo
extension=intl
```

Recommended `php.ini` tweaks:
```ini
memory_limit = 256M
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 60
```

---

## 9. Cron Job (Queue Worker)

If you use queued jobs (emails, background tasks), set up a cron in
**cPanel → Cron Jobs**:

```
* * * * * /usr/local/bin/php /home/YOUR_CPANEL_USER/gusto/artisan schedule:run >> /dev/null 2>&1
```

Replace `YOUR_CPANEL_USER` with your actual cPanel username.

---

## 10. SSL / HTTPS

1. **cPanel → SSL/TLS → Let's Encrypt** — issue a free certificate for your domain.
2. Ensure `APP_URL` in `.env` uses `https://`.
3. Force HTTPS by adding to `.htaccess` inside `public/`:

```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## 11. .htaccess Check

The `public/.htaccess` included with Laravel handles URL rewriting. Verify
`mod_rewrite` is enabled — most cPanel hosts have it on by default.

---

## 12. Post-Deployment Verification

| Check | URL / Command |
|---|---|
| Homepage loads | `https://yourdomain.com` |
| Login works | `https://yourdomain.com/login` |
| Admin dashboard | `https://yourdomain.com/admin/dashboard` |
| Kitchen display | `https://yourdomain.com/kitchen` |
| QR order page | `https://yourdomain.com/order/{tableToken}` |
| Storage accessible | `https://yourdomain.com/storage/...` |

---

## 13. Future Updates (Re-deploy)

```bash
cd ~/gusto

git pull origin claude/restaurant-order-system-XxnMf

composer install --no-dev --optimize-autoloader

# Upload fresh public/build/ from your local machine after running npm run build

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Default Admin Credentials

After seeding, log in with:
- **Email:** `admin@gusto.com`
- **Password:** `password`

**Change these immediately after first login.**
