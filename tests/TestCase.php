<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Passport\Passport;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureOAuthKeysExist();
    }

    /**
     * Passport signs its tokens with an RSA key pair that is generated per
     * environment and therefore not committed. Generating it here (once per
     * run) keeps `php artisan test` working on a fresh clone.
     */
    private function ensureOAuthKeysExist(): void
    {
        if (file_exists(Passport::keyPath('oauth-private.key'))) {
            return;
        }

        $this->artisan('passport:keys', ['--force' => true])->run();
    }
}
