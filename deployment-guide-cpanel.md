# Deploying Afro-Vertex Tours to Shared cPanel Hosting

This is a **fresh deployment** — nothing from your local `vendor/`, `node_modules/`,
`.env`, or `storage/app` gets uploaded. The server builds its own dependencies and
starts with a clean database.

---

## 0. Before you start — check what your host actually gives you

Log into cPanel and check for these, since they change which path below you take:

- **"Terminal" or "SSH Access"** icon — if present, you have command-line access.
  This makes everything below much easier. Most modern cPanel hosts (especially
  anything running CloudLinux) include this.
- **"Setup Node.js App" / "Setup PHP App"** — if present, cPanel can manage a
  Composer-based PHP app for you, including running `composer install` through the UI.
- **"MultiPHP Manager"** — lets you pick the PHP version per domain. You need this.
- **"Git Version Control"** — some hosts let you deploy straight from a Git repo
  through cPanel itself, which is worth using if available instead of manual uploads.

If you have Terminal access, follow the main path below. If not, see the
**"No SSH access" fallback"** section at the end.

---

## 1. PHP version

Laravel 10 requires **PHP 8.1 or higher** (8.2 recommended). Shared hosts often
default new domains to an older PHP version.

- cPanel → **MultiPHP Manager** → select your domain → set PHP version to 8.2.
- Then go to **MultiPHP INI Editor** (or "Select PHP Extensions") for that domain and
  make sure these are **enabled**:
  - `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `ctype`, `json`,
    `bcmath`, `fileinfo`, `curl`, `gd` (or `imagick`), `zip`, `intl`
  - Media Library's image conversions (all your hero/gallery images) specifically
    need **GD or Imagick** — don't skip this one.
- While in the INI editor, bump `memory_limit` to at least `256M` and `upload_max_filesize`/
  `post_max_size` to something like `20M` (image uploads through the admin will fail silently
  otherwise).

## 2. Create the database

cPanel → **MySQL Databases**:
1. Create a new database (e.g. `youruser_avt`).
2. Create a new database user with a strong password.
3. Add that user to the database with **All Privileges**.
4. Write down all three values — you'll need them for `.env`.

Don't import anything yet — migrations will build the schema fresh.

## 3. Get the code onto the server

**If cPanel has Git Version Control:** point it at your GitHub/GitLab repo directly —
easiest option, and makes future deploys a `git pull` away.

**Otherwise, via Terminal:**
```bash
cd ~
git clone <your-repo-url> avt-app
cd avt-app
```
If you don't use Git yet, zip your project locally (**excluding** `vendor/`,
`node_modules/`, `.env`, and `storage/app/public/*` uploads) and upload/extract it
into `~/avt-app` via File Manager instead.

**Important — folder placement:** upload the project **outside** `public_html`,
into its own folder like `~/avt-app`. `public_html` will only get the contents of
Laravel's `public/` folder (step 6) — never the whole app. This keeps your `.env`,
`app/`, and everything else outside the web-accessible root.

## 4. Install dependencies fresh, on the server

```bash
cd ~/avt-app
composer install --no-dev --optimize-autoloader
```
`--no-dev` skips testing/debug tooling you don't need in production.
If `composer` isn't found, check cPanel's PHP Selector / Software section for a
"Composer" installer — most hosts bundle one.

You do **not** need Node.js on the server itself. Build your frontend assets
locally (`npm run build`), and just upload the resulting `public/build` (or
wherever your compiled CSS/JS lands) along with the rest of `public/`.

## 5. Set up `.env`

```bash
cp .env.example .env
php artisan key:generate
```
Then edit `.env` (via `nano .env` or File Manager) and set at minimum:

```
APP_NAME="Afro-Vertex Tours & Safaris"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=youruser_avt
DB_USERNAME=youruser_avtuser
DB_PASSWORD=your-db-password

MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS="..."
MAIL_FROM_NAME="Afro-Vertex Tours & Safaris"

RECAPTCHA_SITE_KEY=...
RECAPTCHA_SECRET_KEY=...

QUEUE_CONNECTION=sync
```

**`APP_DEBUG=false` is not optional in production** — leaving it `true` exposes
stack traces (including database credentials in error pages) to anyone who
triggers an error.

**reCAPTCHA note (relevant to the bug you just hit):** your site key/secret are
tied to specific domains in Google's reCAPTCHA admin console
(https://www.google.com/recaptcha/admin). Add your real production domain there
now, or the contact/booking forms will fail verification the same way they did
on `localhost`.

**`QUEUE_CONNECTION=sync`** is the right call here — shared hosting generally
can't run a persistent queue worker process, so `sync` (run jobs immediately,
inline) is the practical choice. Emails will send synchronously during the
request instead of in the background; fine at your current traffic level.

## 6. Point the domain at Laravel's `public/` folder

Shared hosting serves whatever's in `public_html`, but Laravel's entry point is
`public_html/../avt-app/public`. Two ways to bridge that:

**Option A — if your domain is an addon/subdomain with its own document root field:**
cPanel → Domains → set the Document Root directly to `avt-app/public`. Done, skip
to step 7.

**Option B — if it's your main domain and `public_html` is fixed as the root:**
```bash
cp -r ~/avt-app/public/* ~/public_html/
```
Then edit `~/public_html/index.php` and fix these two lines to point at the real
app location:
```php
require __DIR__.'/../avt-app/vendor/autoload.php';
$app = require_once __DIR__.'/../avt-app/bootstrap/app.php';
```
(adjust the relative path if your folder structure differs)

## 7. Run migrations

```bash
cd ~/avt-app
php artisan migrate --force
```
`--force` is required in production since Laravel normally prompts for
confirmation, which Terminal-over-SSH can still answer, but scripts/deploy
hooks can't.

This builds every table fresh — Destinations, Accommodations, Testimonials,
Activities, Pages, Inquiries, everything — with no leftover local data.

## 8. Storage symlink (needed for Media Library images to display)

```bash
php artisan storage:link
```
If this errors on shared hosting (some disable `symlink()`), create the link
manually via File Manager: create a symlink named `storage` inside `public/`
pointing to `../storage/app/public`. If your host's File Manager doesn't support
symlinks either, ask their support — this one genuinely needs it, there's no
clean workaround.

## 9. File permissions

```bash
chmod -R 755 storage bootstrap/cache
```
If you still get "permission denied" errors writing to logs/cache, try `775`.
Never go to `777` — real security risk for no real benefit.

## 10. Cache everything for production performance

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
**Important:** if you ever change `.env` after this, you must run
`php artisan config:cache` again — cached config takes priority over `.env` once
this runs, so edits to `.env` alone will silently do nothing until you re-cache.

## 11. Create your first admin user

There's no public registration for admin accounts (correctly so). Easiest path:

```bash
php artisan tinker
```
```php
$user = new App\Models\User();
$user->name = 'Your Name';
$user->email = 'you@yourdomain.com';
$user->password = Illuminate\Support\Facades\Hash::make('a-strong-password');
$user->save();
exit
```

## 12. SSL

cPanel → **SSL/TLS Status** → run **AutoSSL** for your domain (free Let's Encrypt
certificate, auto-renewing). Once active, confirm `APP_URL` in `.env` uses
`https://`, then re-run `php artisan config:cache`.

## 13. Cron (optional, but good practice)

Even with `QUEUE_CONNECTION=sync`, Laravel's task scheduler is worth having wired
up for any future scheduled commands. cPanel → **Cron Jobs**:
```
* * * * * php /home/youruser/avt-app/artisan schedule:run >> /dev/null 2>&1
```

---

## Final smoke test — do this before calling it done

1. Visit the homepage — hero image, destinations, tours, testimonials all render?
2. Log into `/login` with the admin user from step 11.
3. Admin → Settings → fill in Header & Footer tab (phone, email, WhatsApp, socials)
   — none of this carries over from local, it's a fresh database.
4. Submit the **contact form** for real — confirm it lands in Admin → Inquiries
   *and* you receive the notification email *and* the sender gets the confirmation email.
5. Submit a **tour booking** the same way.
6. Upload an image through the Media Library and confirm it displays (tests the
   storage symlink from step 8).

If steps 4–5 fail, it's almost certainly the reCAPTCHA domain registration
mentioned in step 5, or `MAIL_*` credentials — check those first.

---

## No-SSH fallback (basic shared hosting without Terminal)

If your host truly has no Terminal/SSH and no Composer installer:

1. Run `composer install --no-dev` **locally**, then upload the entire project
   including the generated `vendor/` folder (this is the one exception to "nothing
   from local" — Composer's *output* still needs to exist somewhere, it just can't
   be built by the server itself in this scenario).
2. Generate `APP_KEY` locally (`php artisan key:generate --show`) and paste the
   value into the server's `.env` manually.
3. Run `php artisan migrate` **locally against a database you can reach from your
   machine** (many hosts allow "Remote MySQL" access — enable it in cPanel, migrate
   from your machine pointing at the live DB credentials, then disable Remote MySQL
   again afterward for security), or ask your host's support to run one migration
   command for you.
4. For the storage symlink, ask host support directly — this genuinely can't be
   worked around from File Manager alone on most restrictive setups.

This path is more fragile and harder to repeat for future updates — worth asking
your host directly whether Terminal access can be added to your plan before
resorting to it.
