<?php

declare(strict_types=1);

namespace Tests\Doubles;

use Prex\Audit\Domain\ServiceInteraction;
use Prex\Audit\Domain\ServiceInteractionRepository;
use Prex\Shared\Domain\ValueObject\UserId;

final class InMemoryServiceInteractionRepository implements ServiceInteractionRepository
{
    /** @var list<ServiceInteraction> */
    public array $saved = [];

    public function save(ServiceInteraction $interaction): void
    {
        $this->saved[] = $interaction;
    }

    public function last(): ?ServiceInteraction
    {
        return $this->saved === [] ? null : $this->saved[count($this->saved) - 1];
    }

    /**
     * @return list<ServiceInteraction>
     */
    public function findByUser(UserId $userId, int $limit = 50): array
    {
        return array_values(array_slice(array_filter(
            $this->saved,
            fn (ServiceInteraction $interaction): bool => $interaction->userId?->equals($userId) === true
        ), 0, $limit));
    }
}
