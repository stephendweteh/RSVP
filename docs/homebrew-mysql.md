# Homebrew MySQL (macOS)

Use this when MySQL was installed with [Homebrew](https://brew.sh/) (`brew install mysql`).

## Paths

- **Prefix:** `$(brew --prefix mysql)` — often `/usr/local/opt/mysql` (Intel) or `/opt/homebrew/opt/mysql` (Apple Silicon).
- **Data directory:** often `$(brew --prefix mysql)/var`.
- **Client / server binaries:** `$(brew --prefix mysql)/bin/mysql`, `mysqld`, etc.

Check yours:

```bash
brew --prefix mysql
```

## Service (start / stop / restart)

```bash
brew services start mysql
brew services stop mysql
brew services restart mysql
brew services list   # shows mysql state
```

## Sign in as `root`

```bash
mysql -u root -p
```

If you see socket errors, try the default Homebrew socket:

```bash
mysql --socket=/tmp/mysql.sock -u root -p
```

## Create the `rsvp` database (this project)

Default `.env` uses MySQL **`root`** (empty `DB_PASSWORD` if your server allows it). Set `DB_PASSWORD` in `.env` if `root` has a password.

**Recommended (Artisan, uses `DB_*` from `.env`):**

```bash
php artisan rsvp:create-database
php artisan migrate --seed
```

**Or SQL only:**

```bash
mysql -u root -p < database/create_rsvp_database.sql
php artisan migrate --seed
```

**Optional — dedicated user `stephen`** (instead of `root` in `.env`): run `database/provision_app_mysql_user.sql` as `root`, or `MYSQL_ROOT_PASSWORD=... php artisan rsvp:provision-user` after setting `DB_USERNAME` / `DB_PASSWORD` in `.env` to match that user.

## Forgotten `root` password

There is no default password; reset it if needed.

1. Stop MySQL:

   ```bash
   brew services stop mysql
   ```

2. Start `mysqld` with grant checks disabled (adjust prefix if not Intel `/usr/local`):

   ```bash
   "$(brew --prefix mysql)/bin/mysqld_safe" --datadir="$(brew --prefix mysql)/var" --skip-grant-tables &
   ```

3. In **another** terminal, connect without a password:

   ```bash
   mysql -u root
   ```

4. Set a new password:

   ```sql
   FLUSH PRIVILEGES;
   ALTER USER 'root'@'localhost' IDENTIFIED BY 'your_new_password';
   FLUSH PRIVILEGES;
   EXIT;
   ```

5. Stop the `mysqld_safe` / `mysqld` process you started (e.g. `pkill mysqld` or find the PID and kill it), then start the service normally:

   ```bash
   brew services start mysql
   ```

6. Verify: `mysql -u root -p`

If `mysqld_safe` is unavailable or paths differ, use the official guide: [MySQL: Resetting the root password](https://dev.mysql.com/doc/refman/8.0/en/resetting-permissions.html) and substitute your Homebrew `datadir`.

## Laravel `.env` checklist

| Variable        | Local (default)              | Production (server)        |
| --------------- | ---------------------------- | -------------------------- |
| `DB_CONNECTION` | `mysql`                      | `mysql`                    |
| `DB_HOST`       | `127.0.0.1`                  | host from your provider    |
| `DB_PORT`       | `3306` (or `3307` + Docker)  | usually `3306`             |
| `DB_DATABASE`   | `rsvp`                       | name your host assigns     |
| `DB_USERNAME`   | `root`                       | app user (often not root)  |
| `DB_PASSWORD`   | empty or your local root pass| strong password from host  |

## Alternative: Docker MySQL (database `rsvp` created automatically)

If Homebrew `root` is awkward or Docker is easier:

1. Install [Docker Desktop](https://www.docker.com/products/docker-desktop/) for Mac.
2. From the project root: `docker compose up -d`
3. Set in `.env` (port **3307** avoids clashing with a local MySQL on 3306):

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3307
   DB_DATABASE=rsvp
   DB_USERNAME=root
   DB_PASSWORD=rsvp_dev_root
   ```

   The image creates the **`rsvp`** database on first start; you only need:

   ```bash
   php artisan migrate --seed
   ```

## Further reading

- [Homebrew MySQL formula](https://formulae.brew.sh/formula/mysql)
- [MySQL 8.0 reference manual](https://dev.mysql.com/doc/refman/8.0/en/)
