<?php

declare(strict_types=1);

namespace Prex\Audit\Domain;

use Prex\Shared\Domain\ValueObject\UserId;

/**
 * Port (driven side): persistence of the audit trail.
 */
interface ServiceInteractionRepository
{
    public function save(ServiceInteraction $interaction): void;

    /**
     * @return list<ServiceInteraction>
     */
    public function findByUser(UserId $userId, int $limit = 50): array;
}
