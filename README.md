# Afifa Sultana — Portfolio

A production-ready personal portfolio built with PHP 8.2, no framework and no
front-end build step. Server-rendered, dependency-light, and deployable to
anything that runs PHP — including hosting with no database at all.

**Live:** set `APP_URL` in `.env` · **Admin:** `/admin`

---

## What this is

A full content-managed portfolio. Everything the public site shows — your name,
pitch, every project case study, skills, education, services, contact details,
SEO metadata — is edited at `/admin` and goes live immediately. No code change,
no redeploy.

**It still renders without a database.** `config/profile.php` is a complete
fallback, so a database outage degrades to the last-known content instead of a
white screen. A circuit breaker suppresses reconnection for 60 seconds after a
failure, so an outage costs one slow request rather than two seconds on every page.

**GitHub data never blocks a page load.** The activity section comes from the
GitHub API, cached to disk and refreshed by the browser *after* load via
`api/github.php`. A bundled snapshot means a first deploy shows real data before
any API call succeeds.

**Contact messages reach you two ways.** Every enquiry is written to the database
*and* emailed. Either alone is enough to report success, so a mail outage never
loses an opportunity. `/admin/email.php` reports exactly how delivery is
configured and sends a real test message.

**No CDN JavaScript.** ScrollReveal, MixItUp and Typed.js — three third-party
libraries from three origins — are replaced by vanilla JS using
`IntersectionObserver` and plain DOM filtering.

**Images are 91% smaller.** Screenshots were committed at capture resolution, one
of them a 2.5 MB PNG for a 640 px card. `tools/optimize-images.php` resizes and
emits WebP with a JPEG fallback: 7.9 MB → 675 KB delivered.

---

## The admin panel

Sign in at `/admin`. Everything below is editable, reorderable by drag, and live
on save.

| Screen | What it controls |
|---|---|
| **Site & SEO** | Name, job title, hero pitch, about text, contact copy, meta description, social share image |
| **Projects** | Full case studies — problem, features, hardest part, outcome, lessons, stack, screenshot, featured and published flags |
| **Skill groups / Skills** | The Stack section, grouped by domain |
| **Education / Experience** | Timeline entries |
| **Activities / Services** | Clubs and competitions; what you can build |
| **About facts** | The label/value list beside your portrait |
| **Hero roles** | The rotating job titles in the typing effect |
| **Social links / Contact info** | Everywhere your links appear |
| **Enquiry types** | Options in the contact form dropdown |
| **Categories** | The project filter buttons |
| **Messages** | Contact inbox, read/unread, reply |
| **Email setup** | Delivery status and a real test send |
| **Media** | Upload screenshots — resized and converted automatically |

Adding a new editable section means describing it in `admin/_resources.php`;
`admin/resource.php` generates the whole CRUD screen from that description.

---

## Requirements

| | Minimum | Notes |
|---|---|---|
| PHP | 8.1 | 8.2+ recommended |
| Extensions | `mbstring`, `json` | Always required |
| | `mysqli` | Only for `/admin` and storing messages |
| | `curl` *or* `allow_url_fopen` | Only for live GitHub data |
| | `gd` | Only to re-run the image tools locally |
| MySQL / MariaDB | 5.7 / 10.3 | Optional |
| Composer | 2.x | To install dependencies |

---

## Quick start

```bash
git clone https://github.com/Afifa637/Portfolio.git
cd Portfolio

composer install
cp .env.example .env          # then edit it

php -S localhost:8000         # http://localhost:8000
```

That is enough for a fully working site driven by `config/profile.php`.
To manage content through the admin panel instead, set up the database below.

### With Docker

```bash
docker compose up -d          # site on http://localhost:8080
```

Compose starts MariaDB, applies `database/schema.sql` and `database/seed.sql`
on first boot, and mounts the source for live editing.

---

## Optional setup

### Database (for the admin panel)

```bash
mysql -u root -p -e "CREATE DATABASE portfolio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p portfolio_db < database/schema.sql
php database/migrate.php                            # schema + imports your content
php database/create_admin.php                       # prompts for a password
```

`database/migrate.php` is the one command that matters. It is additive and
idempotent — safe to run on an existing database with real content — and it:

- creates or upgrades every table, including correcting `projects.category`,
  which the original schema declared as `ENUM('web','app','terminal')` and
  silently discarded any newer value written into it;
- **imports everything from `config/profile.php` into the database**, so the
  admin panel opens fully populated rather than showing empty forms;
- backfills `repo_name` from each project's GitHub URL;
- merges duplicate projects that the two sources named differently;
- corrects image filename casing, which resolves on Windows and 404s on Linux.

Run it again any time; it only fills gaps and never overwrites your edits.
`--reimport` forces content back to the `config/profile.php` version.

### GitHub API token

Unauthenticated requests are capped at 60/hour per IP, which a shared host can
exhaust. A token raises that to 5,000/hour.

1. Create one at <https://github.com/settings/tokens?type=beta> — **no scopes
   are needed**, only public data is read.
2. Set `GITHUB_TOKEN=` in `.env`.

The token is used server-side only and is never sent to the browser.

### Contact form email

Messages are stored in the database *and* emailed. Either alone is enough for
the form to report success, so a mail outage never loses a message.

```dotenv
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=you@gmail.com
MAIL_PASSWORD=your-16-char-app-password
MAIL_FROM_ADDRESS=you@gmail.com
MAIL_TO=you@gmail.com
```

For Gmail, create an [App Password](https://myaccount.google.com/apppasswords)
(needs 2-Step Verification). Never use your account password. Leave `MAIL_HOST`
blank to fall back to PHP's `mail()`.

---

## Editing content

Most edits are one file: [`config/profile.php`](config/profile.php) — identity,
about, skills, education, services, and every project case study.

```php
'projects' => [
    [
        'slug'     => 'my-project',
        'repo'     => 'my-repo-name',   // merges live GitHub stats
        'title'    => 'My Project',
        'category' => 'backend',        // backend|fullstack|mobile|systems|ai|frontend
        'featured' => true,
        'stack'    => ['PHP', 'MySQL'],
        'problem'  => 'What needed solving…',
        'features' => ['…'],
        // …
    ],
],
```

With a database connected, matching rows override these values, so `/admin`
keeps working. Matching is by repository name first and title second, which
avoids duplicate cards when a title differs slightly between the two sources.

After adding screenshots to `assets/images/`:

```bash
php -d extension=gd tools/optimize-images.php   # resize + WebP
php -d extension=gd tools/make-images.php       # regenerate the social card
```

---

## Deployment

### Shared hosting (cPanel and similar)

1. Upload everything **except** `.env`, `vendor/`, and `database/backups/`.
2. Run `composer install --no-dev --optimize-autoloader`, or upload a locally
   built `vendor/` if the host has no Composer.
3. Create `.env` from `.env.example` and set `APP_ENV=production` and `APP_URL`.
4. Make `storage/cache/` writable: `chmod -R 775 storage`.
5. Confirm `.htaccess` is being read — if `/sitemap.xml` 404s, `AllowOverride`
   is off and you need `mod_rewrite` enabled.
6. Uncomment the HTTPS redirect block in `.htaccess` once your certificate is
   installed.

### Nginx

`.htaccess` is Apache-only. Equivalent server block:

```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;
    root /var/www/portfolio;
    index index.php;

    add_header X-Content-Type-Options    "nosniff"                        always;
    add_header X-Frame-Options           "SAMEORIGIN"                     always;
    add_header Referrer-Policy           "strict-origin-when-cross-origin" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    location = /sitemap.xml { try_files $uri /sitemap.php; }
    error_page 404 /404.php;

    # Application internals are never served directly.
    location ~ ^/(includes|src|config|storage|database|vendor|tools)/ { deny all; }
    location ~ /\.                                                    { deny all; }
    location ~ \.(sql|md|lock|json)$                                  { deny all; }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # Assets carry a ?v=<mtime> fingerprint, so immutable is safe.
    location ~* \.(css|js|png|jpe?g|webp|svg|woff2?)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    location / { try_files $uri $uri/ /index.php?$query_string; }
}
```

### Docker / VPS

```bash
docker build -t portfolio .
docker run -d -p 80:80 --env-file .env --name portfolio portfolio
```

The image is multi-stage: Composer runs in a build stage and never ships.

### Production checklist

- [ ] `APP_ENV=production` — errors are logged, never displayed
- [ ] `APP_URL` set to the real domain (canonical tags, Open Graph, sitemap)
- [ ] `.env` is **not** web-reachable — visit `/.env` and confirm 403/404
- [ ] HTTPS enforced; HSTS header uncommented in `.htaccess`
- [ ] `storage/cache/` writable by the web server
- [ ] A real admin password set via `php database/create_admin.php`
- [ ] Submit `sitemap.xml` to [Google Search Console](https://search.google.com/search-console)
- [ ] Preview the social card with the
      [LinkedIn Post Inspector](https://www.linkedin.com/post-inspector/)

---

## Project layout

```
config/profile.php     All site content — the single source of truth
src/
  Content.php          Merges profile.php with optional database overrides
  Database.php         Optional MySQL, circuit breaker, prepared statements
  GitHub.php           Cached API client with layered fallbacks
  ContactHandler.php   CSRF, rate limit, honeypot, validation, delivery
  Mailer.php           SMTP via PHPMailer, falling back to mail()
  helpers.php          Escaping, inline SVG icons, <picture>, asset fingerprints
views/
  layout/              head (SEO, JSON-LD), header, footer
  sections/            hero, about, skills, projects, github, resume, contact
assets/css/app.css     Design system: tokens, components, sections
assets/js/app.js       Theme, nav, reveals, filtering, modal, form
api/github.php         Off-render-path GitHub refresh
database/              schema.sql, seed.sql, migrate.php, create_admin.php
tools/                 Image optimisation and social-card generation
admin/                 Content management screens
```

---

## Security

| Concern | Handling |
|---|---|
| XSS | `e()` escapes every interpolation; no `innerHTML` from user data |
| SQL injection | Prepared statements throughout `Database` |
| CSRF | Token on every POST, `hash_equals` comparison, rotated after success |
| Spam | Honeypot field, submission-timing check, session rate limit |
| Header injection | Control characters stripped from all mail header values |
| Session | `HttpOnly`, `SameSite=Lax`, `Secure` on HTTPS, ID regenerated on login |
| Brute force | Five failed logins locks the form for five minutes |
| Secrets | `.env` git-ignored and denied by `.htaccess`; token stays server-side |
| Headers | CSP, `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, HSTS |
| File exposure | `.htaccess` guards in `includes/`, `src/`, `config/`, `storage/`, `database/` |

Admin passwords are bcrypt hashes. No default credentials ship with the project.

---

## Performance

| | Before | After |
|---|---|---|
| Images delivered | 7.9 MB | 675 KB (WebP) |
| Third-party JS | 3 CDN libraries | none |
| Icon delivery | Font Awesome CSS + webfont | inline SVG |
| TTFB, database down | ~2.1 s | ~0.06 s |
| Blocking API calls on render | GitHub, synchronous | none |

Other measures: `?v=<mtime>` asset fingerprinting with immutable caching,
`font-display: swap` with preconnect, lazy loading below the fold,
`fetchpriority="high"` on the hero portrait, `content-visibility`-friendly
section structure, and `prefers-reduced-motion` honoured throughout.

---

## Accessibility

Semantic landmarks, a skip link, visible focus rings, `aria-current` on the
active nav item, a focus-trapped modal that restores focus on close, labelled
form fields with `aria-invalid` and inline error text, `aria-live` status
messages, decorative imagery marked `aria-hidden`, and full keyboard operation.
Colour pairings meet WCAG AA in both themes.

---

## Troubleshooting

**Site loads but projects show no GitHub stats.** The API is rate limited or
unreachable. Set `GITHUB_TOKEN` in `.env`. Cached data is served meanwhile —
check `storage/cache/` is writable.

**"Database unavailable" on `/admin`.** The public site is unaffected by design.
Check `.env`, confirm MySQL is running, and that `database/schema.sql` has been
applied.

**Contact form reports an error.** With neither a database nor SMTP configured
there is nowhere to put the message. Configure one.

**`/sitemap.xml` 404s on Apache.** `mod_rewrite` is off or `AllowOverride` is
not `All`. `/sitemap.php` works regardless.

**Styles look wrong after an update.** Assets are fingerprinted by modification
time; a hard refresh (Ctrl+Shift+R) clears a stale proxy cache.

---

## License

Source code MIT. Written content, CV, and project imagery remain the property of
Afifa Sultana.
