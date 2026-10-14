<?php

declare(strict_types=1);

namespace Prex\Audit\Domain\Exception;

use Prex\Shared\Domain\Exception\DomainException;

final class InvalidServiceInteractionId extends DomainException
{
    public static function withValue(string $value): self
    {
        return new self(sprintf('"%s" is not a valid service interaction id (UUID expected).', $value));
    }

    public function errorCode(): string
    {
        return 'invalid_service_interaction_id';
    }
}
