# RSVP

Guest wedding RSVP site and admin tools, built with [Laravel](https://laravel.com).

## Wedding RSVP (this repo)

Guest form: `/rsvp` (home redirects there). Admins: `/admin/login`. Set `ADMIN_EMAIL`, `ADMIN_PASSWORD`, `ADMIN_NAME` in `.env`, then run `php artisan db:seed` to create or reset that admin user. **User accounts:** `/admin/users` (list, create, view, edit, delete) — you cannot remove the only admin or delete yourself while signed in.

**MySQL (local):** `.env` defaults to **`root`** with an **empty** `DB_PASSWORD` (common on fresh installs). If your MySQL requires a root password (e.g. Homebrew), set `DB_PASSWORD` in `.env`. Create the `rsvp` database and tables:

```bash
php artisan rsvp:create-database
php artisan migrate --seed
php artisan serve
```

**Deploying to a server:** set `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` on the server’s `.env` to whatever your host gives you (often a non-root user). Run `php artisan migrate --seed` there; you usually **do not** need `rsvp:create-database` if the host already created the database.

**Optional dedicated app user** (local or server): `database/provision_app_mysql_user.sql` or `MYSQL_ROOT_PASSWORD=... php artisan rsvp:provision-user` after pointing `DB_USERNAME` / `DB_PASSWORD` at that user in `.env`.

**Homebrew MySQL on macOS:** see [docs/homebrew-mysql.md](docs/homebrew-mysql.md) (login, service, password reset, paths).

**Docker MySQL (optional):** `docker compose up -d` then set `.env` as in [docs/homebrew-mysql.md](docs/homebrew-mysql.md#alternative-docker-mysql-database-rsvp-created-automatically) (`DB_PORT=3307`, `DB_PASSWORD=rsvp_dev_root`). The `rsvp` database is created by the container.

Sessions use the database (`sessions` table); ensure migrations have run.

**Features:** optional **email** to guests when they provide an address (submission + approve/reject) — uses `MAIL_*` in `.env` (default `log` / `array` in tests). **Export CSV** from Admin → RSVPs (`/admin/rsvps/export`, respects status filter). Public RSVP `POST` is **rate limited** (20/minute). **User profile photos:** stored under `storage/app/public/avatars`; run `php artisan storage:link`. Image URLs use `/storage/...` (same host as the browser) so they still work if `APP_URL` uses `localhost` but you open the site via `127.0.0.1`.

---

## Framework

This app uses the Laravel framework ([documentation](https://laravel.com/docs)). Framework security issues should be reported per [Laravel’s security policy](https://github.com/laravel/framework/security/policy).

## License

MIT (application code in this repository). Laravel is MIT-licensed separately.
