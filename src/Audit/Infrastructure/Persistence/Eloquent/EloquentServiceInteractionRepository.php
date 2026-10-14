<?php

declare(strict_types=1);

namespace Prex\Audit\Infrastructure\Persistence\Eloquent;

use Prex\Audit\Domain\ServiceInteraction;
use Prex\Audit\Domain\ServiceInteractionRepository;
use Prex\Shared\Domain\ValueObject\UserId;

final readonly class EloquentServiceInteractionRepository implements ServiceInteractionRepository
{
    public function __construct(private ServiceInteractionMapper $mapper) {}

    public function save(ServiceInteraction $interaction): void
    {
        ServiceInteractionModel::query()->create($this->mapper->toAttributes($interaction));
    }

    /**
     * @return list<ServiceInteraction>
     */
    public function findByUser(UserId $userId, int $limit = 50): array
    {
        return ServiceInteractionModel::query()
            ->where('user_id', $userId->value)
            ->orderByDesc('occurred_at')
            ->limit($limit)
            ->get()
            ->map(fn (ServiceInteractionModel $model): ServiceInteraction => $this->mapper->toDomain($model))
            ->values()
            ->all();
    }
}
