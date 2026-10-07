# Deploying this portfolio

The site needs **PHP 8.1+**. MySQL is optional — without it the site renders
from `config/profile.php`; with it, `/admin` becomes the live content manager.

Pick the option that matches what you want. Option 1 gets you online today for
free; option 3 is what I would run long term.

---

## Option 1 — Free PHP hosting (no card, ~15 minutes)

Best if you want a live URL now and are happy with a free subdomain.

**InfinityFree** (`infinityfree.com`) gives PHP 8.2, MySQL, free SSL and a
subdomain like `afifa.rf.gd`. Alternatives: **AwardSpace**, **ByetHost**.

1. Sign up and create a hosting account. Note the FTP host, username and
   password it shows you.
2. In the control panel, create a MySQL database. Write down the generated
   database name, username, password and host — they are not the same as your
   login.
3. Upload the project to `htdocs/` over FTP (FileZilla is fine). Upload
   everything **except** `.env`, `dist/`, `database/backups/` and
   `assets/images/original/`.
4. `vendor/` must be present. If the host has no Composer, run
   `composer install --no-dev --optimize-autoloader` locally and upload the
   resulting `vendor/` folder.
5. Create `.env` in the site root (use the control panel's file manager):

   ```dotenv
   APP_ENV=production
   APP_URL=https://your-subdomain.rf.gd

   DB_ENABLED=true
   DB_HOST=sqlXXX.infinityfree.com
   DB_PORT=3306
   DB_USERNAME=if0_XXXXXXX
   DB_PASSWORD=your-db-password
   DB_DATABASE=if0_XXXXXXX_portfolio
   ```

6. Import the schema through phpMyAdmin: run `database/schema.sql`, then
   `database/seed.sql`.
7. Create your admin login. Most free hosts have no SSH, so generate the hash
   locally and insert it by hand:

   ```bash
   php -r "echo password_hash('your-strong-password', PASSWORD_BCRYPT), PHP_EOL;"
   ```

   Then in phpMyAdmin:

   ```sql
   INSERT INTO admins (name, email, password)
   VALUES ('Afifa Sultana', 'afifasultana637@gmail.com', '<paste-the-hash>');
   ```

8. Visit `/admin` and sign in.

> **Skipping MySQL?** Set `DB_ENABLED=false` and skip steps 2, 6 and 7. The site
> works fully; you just edit `config/profile.php` instead of using `/admin`.

---

## Option 2 — Railway (deploy straight from GitHub)

Railway builds the included `Dockerfile` and provisions MySQL for you. It has a
trial credit; beyond that it is paid.

1. Sign in at `railway.app` with GitHub.
2. **New Project → Deploy from GitHub repo →** `Afifa637/Portfolio`.
3. **New → Database → MySQL.** Railway injects `MYSQLHOST`, `MYSQLUSER`,
   `MYSQLPASSWORD`, `MYSQLDATABASE`, `MYSQLPORT`.
4. On the web service, add these variables:

   | Variable | Value |
   |---|---|
   | `APP_ENV` | `production` |
   | `APP_URL` | your Railway URL |
   | `DB_HOST` | `${{MySQL.MYSQLHOST}}` |
   | `DB_PORT` | `${{MySQL.MYSQLPORT}}` |
   | `DB_USERNAME` | `${{MySQL.MYSQLUSER}}` |
   | `DB_PASSWORD` | `${{MySQL.MYSQLPASSWORD}}` |
   | `DB_DATABASE` | `${{MySQL.MYSQLDATABASE}}` |

5. Open the service shell and run:

   ```bash
   php database/migrate.php
   php database/create_admin.php
   ```

Every push to `main` redeploys automatically.

---

## Option 3 — Shared cPanel hosting (recommended long term)

Around $2–4/month (Namecheap, Hostinger, A2) buys a real domain, reliable mail
and no ads. Worth it for a portfolio you are sending to employers.

1. Point your domain at the host.
2. Upload the project to `public_html/`, excluding the same files as option 1.
3. Create a MySQL database and user in cPanel; grant all privileges.
4. Create `.env` with those credentials and `APP_URL=https://your-domain.com`.
5. In **Terminal** (or over SSH):

   ```bash
   composer install --no-dev --optimize-autoloader
   php database/migrate.php
   php database/create_admin.php
   ```

6. Enable free **AutoSSL / Let's Encrypt** in cPanel.
7. Open `.htaccess` and uncomment the HTTPS redirect block and the
   `Strict-Transport-Security` header.
8. `chmod -R 775 storage` so the GitHub cache is writable.

---

## Option 4 — VPS with Docker

```bash
git clone https://github.com/Afifa637/Portfolio.git
cd Portfolio
cp .env.example .env     # edit it
docker compose up -d
docker compose exec web php database/migrate.php
docker compose exec web php database/create_admin.php
```

Put Nginx or Caddy in front for TLS. The README has a ready Nginx server block.

---

## After deploying, in order

1. **Sign in to `/admin`** and change the password if you created it by hand.

2. **Set up email** — `/admin/email.php`. Without it the contact form still
   saves messages, but you will not be notified. Gmail needs an
   [App Password](https://myaccount.google.com/apppasswords), not your account
   password:

   ```dotenv
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_ENCRYPTION=tls
   MAIL_USERNAME=afifasultana637@gmail.com
   MAIL_PASSWORD=your-16-char-app-password
   MAIL_FROM_ADDRESS=afifasultana637@gmail.com
   MAIL_TO=afifasultana637@gmail.com
   ```

   Then press **Send test email** and confirm it arrives.

3. **Add a GitHub token** — without one the API allows 60 requests/hour per IP,
   which a shared host shares across every site on it, so your activity section
   will often serve stale data. Create a token at
   [github.com/settings/tokens](https://github.com/settings/tokens?type=beta)
   with **no scopes at all**, then set `GITHUB_TOKEN=` in `.env`. That raises the
   limit to 5,000/hour.

4. **Check `.env` is not reachable.** Visit `https://your-domain.com/.env` — it
   must return 403 or 404. If it shows the file, your host is ignoring
   `.htaccess` and you must move the site behind a `public/` document root
   before going further.

5. **Submit the sitemap** to
   [Google Search Console](https://search.google.com/search-console):
   `https://your-domain.com/sitemap.xml`.

6. **Check the social card** with the
   [LinkedIn Post Inspector](https://www.linkedin.com/post-inspector/) — this is
   what recruiters see when you share the link.

---

## Production checklist

- [ ] `APP_ENV=production` — errors logged, never displayed
- [ ] `APP_URL` set to the real domain
- [ ] `/.env` returns 403 or 404
- [ ] HTTPS working; redirect and HSTS uncommented in `.htaccess`
- [ ] `storage/cache/` writable by the web server
- [ ] Admin password is strong and not the one from any setup guide
- [ ] Contact form test email received
- [ ] `/admin` loads over HTTPS only

---

## Troubleshooting

**Blank white page.** PHP fatal error with display off. Check the host's error
log. Nine times out of ten `vendor/` is missing — run `composer install`.

**"Database unavailable" on /admin.** The public site is fine by design. Check
the `.env` credentials; on shared hosting `DB_HOST` is rarely `localhost`.

**Styles missing.** `.htaccess` is being ignored, or `mod_rewrite` is off. The
site still works; ask support to enable `AllowOverride All`.

**Contact form says something went wrong.** Neither the database nor SMTP is
configured, so there is nowhere to put the message. Set up one of them.

**GitHub section shows old data.** Rate limited. Add `GITHUB_TOKEN`.
