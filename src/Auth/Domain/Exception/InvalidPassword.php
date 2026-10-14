<?php

declare(strict_types=1);

namespace Prex\Auth\Domain\Exception;

use Prex\Shared\Domain\Exception\DomainException;

final class InvalidPassword extends DomainException
{
    public static function empty(): self
    {
        return new self('The password cannot be empty.');
    }

    public function errorCode(): string
    {
        return 'invalid_password';
    }
}
