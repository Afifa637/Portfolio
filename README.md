# Afifa Sultana — Portfolio

An interactive engineering workspace rather than a list of links. Visitors can
move through the technology graph, ask questions answered from a local knowledge
base, run a request through a project's architecture, query a toy SQL engine, and
open any project as a full case study.

PHP 8.2, no framework, no front-end build step. Server-rendered first, enhanced
with native ES modules. Deployable to anything that runs PHP — including hosting
with no database at all.

**Admin:** `/admin` · **Case studies:** `/projects/<slug>` · **Printable résumé:** `/resume.php`

---

## Run it locally

```bash
git clone https://github.com/Afifa637/Portfolio.git
cd Portfolio
composer install
cp .env.example .env              # set APP_ENV=local while developing

php -S 127.0.0.1:8899 router.php  # http://127.0.0.1:8899
```

Use `router.php`. The PHP dev server ignores `.htaccess`, and the router gives it
the same behaviour: pretty `/projects/<slug>` URLs, `/sitemap.xml`, the custom
404, and refusing to serve `src/`, `config/`, `.env` and other internals.

That is a complete site, driven by `config/profile.php`. For the admin panel, set
up the database below. With Docker instead: `docker compose up -d` →
<http://localhost:8080>.

---

## What is on the page

| Section | What it does |
|---|---|
| **System core** | SVG graph of every technology used. Node size and every link are computed from project stacks — a link means two technologies shipped in the same project. Hover or tab to a node for its projects. |
| **How I think** | Engineering principles, each tied to the projects that evidence it, beside an annotated request lifecycle. |
| **Stack** | Skills as an ecosystem: pick one to see where it was used and what it was used alongside. No percentage bars. |
| **Featured builds** | The first four featured projects (admin order) as full showcases. |
| **Under the hood** | Choose a system, run its example request through each architecture layer, or run it without permission and watch the security layer reject it with a 403. Timings are labelled illustrative. **Build mode** lets a visitor pick technologies and see which real projects match. |
| **Archive** | Every project: filter, sort, search, live count, empty state. |
| **Lab** | Working experiments: JWT decoder, a small SQL engine (tokenizer → parser → plan → executor), sorting visualiser with comparison counts. |
| **Activity** | GitHub data from the API, cached server-side. Nothing is invented when the API is unavailable — the section says so. |
| **Journey / Career** | Growth stages, education, activities, and a résumé with download, print and open. |
| **Contact** | Validated form with enquiry type; saved to the database *and* emailed. |

Across the site:

- **Command palette** — `Ctrl/⌘ K` or `/`. Fuzzy search over sections, projects, skills and actions.
- **Ask Afifa** — answers questions from the site's own content using intent routing and BM25 retrieval, and cites the section each answer came from. No external AI call; if it does not know, it says so.
- **Terminal** — `~` or `` ` ``. `help` lists commands.
- **Recruiter mode** — `?view=recruiter`, or from the palette: one condensed screen with education, strongest projects and contact.
- **Case studies** — `/projects/<slug>`, numbered sections that appear only when they have content, an interactive architecture diagram, and previous/next navigation.
- Small things for the curious: the console, the Konami code, clicking the logo five times.

Dark ("technical lab") and light ("engineering notebook") themes, the system
preference by default, switched with a view transition where supported.

---

## Content rules

Every number on the site is derived, not typed. Project counts, technology
counts, graph edges, "used in N projects" and GitHub figures come from
`src/Knowledge.php` and the GitHub client. Where data is missing, the section is
omitted rather than filled with a placeholder claim. Keep it that way when
editing: a case-study field left blank hides its section.

---

## The admin panel

Sign in at `/admin`. Everything is editable, reorderable by drag, and live on save.

| Screen | Controls |
|---|---|
| **Site & SEO** | Name, title, pitch, about, contact copy, meta tags, share image, system status (focus, mode, time zone) |
| **Projects** | Case studies: summary, goal, problem, hardest part, engineering decisions, security notes, outcome, lessons, future improvements, features, stack, **architecture layers**, **example request**, screenshot, featured/published |
| **How I think** | Principles and the projects that evidence them |
| **Request blueprint** | Stages of the annotated request lifecycle |
| **Journey** | Growth stages, tools and resulting projects |
| **Skill groups / Skills** | The Stack section |
| **Education / Experience / Activities / Services** | Career and résumé |
| **Hero roles / Social links / Contact info / Enquiry types / Categories** | Smaller lists used across the site |
| **Messages** | Contact inbox |
| **Email setup** | Delivery status and a real test send |
| **Media** | Uploads, resized and converted to WebP |

A project with architecture layers gets a diagram on its case study and on its
showcase card. Add an example request (`POST /api/auth/login`) and it also
appears in **Under the hood**.

New list-type content is added by describing it in `admin/_resources.php`;
`admin/resource.php` generates the CRUD screen.

---

## Architecture

```
Request ─► index.php / project.php / resume.php
             │
             ├─ includes/bootstrap.php   env, session, error handling, autoload
             ├─ src/Content.php          config/profile.php, overridden by DB rows
             ├─ src/Knowledge.php        derived graph, metrics, JSON payload
             └─ views/                   server-rendered HTML (works without JS)
                    │
                    └─ <script type="application/json" id="portfolio-data">
                       read by the modules below
```

**No bundler.** `import_map()` in `src/helpers.php` writes an import map with
every module's modification time in its URL, so each file is cache-busted
individually and can be cached as immutable.

```
assets/js/main.js           entry: core behaviours, then lazy sections
assets/js/core/             theme, nav, reveal, pointer, overlays, palette, keys, fuzzy
assets/js/modules/          one per feature, imported when its section nears the viewport
```

Section modules load through `IntersectionObserver` + dynamic `import()`, so the
first paint ships only the core. Everything degrades: without JavaScript the
graph, projects, case studies, résumé and contact form all still work.

**Database is optional.** A circuit breaker stops reconnect attempts for 60
seconds after a failure, so an outage costs one slow request, not every request.

**GitHub never blocks a render.** Cached to disk, refreshed after load via
`api/github.php`, with a bundled snapshot for a first deploy.

```
config/profile.php     Default content — the fallback and the migration source
src/                   Content, Knowledge, Database, GitHub, ContactHandler, Mailer, helpers
views/layout/          head (SEO, JSON-LD), header, footer + overlays
views/sections/        hero, think, stack, featured, hood, archive, lab, activity, journey, career, contact
assets/css/            app.css (design system), sections.css
project.php            Case-study page          resume.php   Printable A4 résumé
router.php             Dev-server router        sitemap.php  Home, résumé, every case study
admin/                 Content management       database/    schema.sql, migrate.php, create_admin.php
```

---

## Database (for the admin panel)

```bash
mysql -u root -p -e "CREATE DATABASE portfolio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p portfolio_db < database/schema.sql
php database/migrate.php        # creates/upgrades tables, imports profile.php content
php database/create_admin.php   # prompts for a password
```

`migrate.php` is additive and idempotent: it adds new columns and tables,
reconciles column types against `schema.sql`, imports content into empty tables,
and fills only blank fields. It never overwrites edits made in the admin.
`--reimport` forces content back to `config/profile.php`.

On hosting without a shell, use the browser installer described in
[DEPLOY.md](DEPLOY.md).

### Email

```dotenv
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=you@gmail.com
MAIL_PASSWORD=your-16-char-app-password
MAIL_FROM_ADDRESS=you@gmail.com
MAIL_TO=you@gmail.com
```

Gmail needs an [App Password](https://myaccount.google.com/apppasswords). Either
the database or email alone is enough for the form to report success.

### GitHub token

Optional. Raises the API limit from 60 to 5,000 requests an hour. Create one at
<https://github.com/settings/tokens?type=beta> with **no scopes** and set
`GITHUB_TOKEN=`. It is used server-side only.

---

## Deployment

See [DEPLOY.md](DEPLOY.md) for free hosting, Railway, cPanel and Docker.

Case studies use `/projects/<slug>`. Apache (`.htaccess`) and the Docker image
handle that out of the box; Nginx needs the rewrite in DEPLOY.md. On a host that
cannot rewrite at all, set `PRETTY_URLS=false` and links switch to
`project.php?slug=…`.

---

## Security

| Concern | Handling |
|---|---|
| XSS | `e()` on every interpolation; client modules escape before inserting HTML |
| SQL injection | Prepared statements throughout |
| CSRF | Token on every POST, `hash_equals`, rotated after success |
| Spam | Honeypot, submission timing, session rate limit, enquiry type whitelisted server-side |
| Header injection | Control characters stripped from mail headers |
| Sessions | `HttpOnly`, `SameSite=Lax`, `Secure` on HTTPS, ID regenerated on login |
| Brute force | Five failed logins locks the form for five minutes |
| Secrets | `.env` git-ignored and denied; internals denied by `.htaccess` and `router.php` |
| Headers | CSP, `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, HSTS |

No default credentials ship with the project.

---

## Performance and accessibility

- No third-party JavaScript. Fonts from Google Fonts with `display=swap`.
- Per-module fingerprinting via the import map; feature code loads on approach.
- Images resized and served as WebP with fallbacks (`tools/optimize-images.php`).
- Scroll-driven animation only where `animation-timeline` is supported;
  `prefers-reduced-motion` turns motion off everywhere, including the graph, the
  request animation and view transitions.
- Custom cursor on fine pointers only; never replaces focus indication.
- Skip link, landmarks, numbered nav with `aria-current`, focus-trapped overlays
  that restore focus, labelled fields with inline errors, `aria-live` results,
  every interactive graph node reachable by keyboard.
- Checked from 1920 px down to 360 px with no horizontal scroll.

---

## Troubleshooting

**`/projects/…` returns 404.** The host is not rewriting. Enable `mod_rewrite`
with `AllowOverride All`, add the Nginx rule, or set `PRETTY_URLS=false`.

**Locally, pages work but `/projects/…` 404s.** Start the server with
`php -S 127.0.0.1:8899 router.php` — the router argument matters.

**Projects show no GitHub stats.** Rate limited or unreachable. Set `GITHUB_TOKEN`
and make sure `storage/cache/` is writable.

**"Database unavailable" on `/admin`.** The public site is unaffected. Check
`.env` and that `schema.sql` has been applied.

**Styles look stale after an update.** Hard refresh (Ctrl+Shift+R).

---

## License

Source code MIT. Written content, CV and project imagery remain the property of
Afifa Sultana.
