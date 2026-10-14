<?php

declare(strict_types=1);

namespace Prex\Auth\Domain\Exception;

use Prex\Shared\Domain\Exception\DomainException;
use Prex\Shared\Domain\Exception\Unauthorized;

/**
 * Raised when the email does not exist or the password does not match.
 *
 * Both cases share a single message on purpose, so the API does not leak which
 * emails are registered (user enumeration).
 */
final class InvalidCredentials extends DomainException implements Unauthorized
{
    public static function create(): self
    {
        return new self('The provided credentials are invalid.');
    }

    public function errorCode(): string
    {
        return 'invalid_credentials';
    }
}
