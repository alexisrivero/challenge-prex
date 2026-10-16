<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Test bootstrap
|--------------------------------------------------------------------------
|
| PHPUnit applies the <env> entries of phpunit.xml to $_ENV and putenv(), but
| Laravel's environment repository reads $_SERVER first. Inside the Docker
| container DB_CONNECTION, DB_HOST & co. are *real* environment variables, so
| without this synchronisation the suite would silently run against the
| development database - migrating and truncating it - instead of the in-memory
| SQLite one declared in phpunit.xml.
|
| Keeping it generic (instead of repeating the list of variables) means adding a
| new <env force="true"> entry to phpunit.xml is all that is ever needed.
|
*/

foreach ($_ENV as $key => $value) {
    $_SERVER[$key] = $value;
}

require __DIR__.'/../vendor/autoload.php';
