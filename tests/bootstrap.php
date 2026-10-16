<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Environment isolation
|--------------------------------------------------------------------------
|
| PHPUnit applies the <env> entries of phpunit.xml to $_ENV and putenv(), but
| Laravel's environment repository reads $_SERVER first. Inside the Docker
| container DB_CONNECTION, DB_HOST & co. are *real* environment variables, so
| without this synchronisation the suite would silently run against the
| development database - migrating and truncating it.
|
*/

foreach ($_ENV as $key => $value) {
    $_SERVER[$key] = $value;
}

/*
|--------------------------------------------------------------------------
| Database under test
|--------------------------------------------------------------------------
|
| The application itself always runs on MariaDB (see config/database.php and
| docker-compose.yml). The *test suite* defaults to an in-memory SQLite
| database because it needs no service, starts clean on every run and keeps the
| whole suite at ~1.5s - which is what makes it useful to run on every change.
|
| Set TEST_DB_CONNECTION=mariadb (shortcut: `make test-mariadb`) to run exactly
| the same tests against the engine used in production, exercising the real
| schema: JSON columns, the unique index on favorite_gifs and the foreign keys.
|
*/

$connection = $_SERVER['TEST_DB_CONNECTION'] ?? 'sqlite';

$database = $connection === 'sqlite'
    ? ':memory:'
    : ($_SERVER['TEST_DB_DATABASE'] ?? 'prex_gifs_test');

foreach (['DB_CONNECTION' => $connection, 'DB_DATABASE' => $database] as $key => $value) {
    $_SERVER[$key] = $value;
    $_ENV[$key] = $value;
    putenv($key.'='.$value);
}

require __DIR__.'/../vendor/autoload.php';
