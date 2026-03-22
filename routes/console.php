<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('rsvp:create-database', function (): int {
    $cfg = config('database.connections.mysql');
    $name = $cfg['database'] ?? 'rsvp';

    if (($cfg['driver'] ?? '') !== 'mysql') {
        $this->error('DB_CONNECTION must be mysql (currently: '.config('database.default').').');

        return 1;
    }

    $host = $cfg['host'] ?? '127.0.0.1';
    $port = (int) ($cfg['port'] ?? 3306);
    $user = $cfg['username'] ?? 'root';
    $pass = $cfg['password'] ?? '';

    $dsn = "mysql:host={$host};port={$port}";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    } catch (PDOException $e) {
        $this->error('Could not connect to MySQL: '.$e->getMessage());
        $this->line('Set DB_HOST, DB_USERNAME, and DB_PASSWORD in .env, then try again.');

        return 1;
    }

    $quoted = '`'.str_replace('`', '``', $name).'`';
    $pdo->exec("CREATE DATABASE IF NOT EXISTS {$quoted} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $this->info("Database [{$name}] is ready.");

    return 0;
})->purpose('Create the MySQL database named in DB_DATABASE using .env credentials');

Artisan::command('rsvp:provision-user', function (): int {
    $rootPassword = env('MYSQL_ROOT_PASSWORD');
    if ($rootPassword === null || $rootPassword === '') {
        $this->error('Set MYSQL_ROOT_PASSWORD to your MySQL root password for this one command only.');
        $this->line('Example: MYSQL_ROOT_PASSWORD=yourRootPass php artisan rsvp:provision-user');
        $this->line('Or run: mysql -u root -p < database/provision_app_mysql_user.sql');

        return 1;
    }

    $cfg = config('database.connections.mysql');

    if (($cfg['driver'] ?? '') !== 'mysql') {
        $this->error('DB_CONNECTION must be mysql.');

        return 1;
    }

    $appUser = (string) ($cfg['username'] ?? '');
    $appPass = (string) ($cfg['password'] ?? '');
    $dbName = (string) ($cfg['database'] ?? 'rsvp');

    if ($appUser === '' || ! preg_match('/^[a-zA-Z0-9_]{1,32}$/', $appUser)) {
        $this->error('Set DB_USERNAME in .env to a valid MySQL username (letters, numbers, underscore).');

        return 1;
    }

    $host = $cfg['host'] ?? '127.0.0.1';
    $port = (int) ($cfg['port'] ?? 3306);

    try {
        $pdo = new PDO("mysql:host={$host};port={$port}", 'root', $rootPassword, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    } catch (PDOException $e) {
        $this->error('Could not sign in as MySQL root: '.$e->getMessage());
        $this->line('Use the real password you type for `mysql -u root -p` — not the placeholder text from the README.');

        return 1;
    }

    $q = fn (string $s): string => $pdo->quote($s);
    $dbQuoted = '`'.str_replace('`', '``', $dbName).'`';

    $pdo->exec("CREATE DATABASE IF NOT EXISTS {$dbQuoted} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    foreach (['localhost', '127.0.0.1'] as $clientHost) {
        $pdo->exec(sprintf(
            'CREATE USER IF NOT EXISTS %s@%s IDENTIFIED BY %s',
            $q($appUser),
            $q($clientHost),
            $q($appPass)
        ));
        $pdo->exec(sprintf(
            'ALTER USER %s@%s IDENTIFIED BY %s',
            $q($appUser),
            $q($clientHost),
            $q($appPass)
        ));
        $pdo->exec(sprintf(
            'GRANT ALL PRIVILEGES ON %s.* TO %s@%s',
            $dbQuoted,
            $q($appUser),
            $q($clientHost)
        ));
    }

    $pdo->exec('FLUSH PRIVILEGES');

    $this->info("Database [{$dbName}] and user [{$appUser}] are ready.");
    $this->line('Next: php artisan migrate --seed');

    return 0;
})->purpose('Create DB and MySQL user from DB_* using MYSQL_ROOT_PASSWORD once');
