<?php

declare(strict_types=1);

namespace Prex\Auth\Application\Login;

use SensitiveParameter;

/**
 * Input of the Login use case, expressed with primitives so that the
 * application layer stays independent from the transport (HTTP, CLI, queue...).
 */
final readonly class LoginCommand
{
    public function __construct(
        public string $email,
        #[SensitiveParameter] public string $password,
    ) {}
}
