<?php

declare(strict_types=1);

namespace Prex\Gifs\Domain\Favorite\Exception;

use Prex\Gifs\Domain\GifId;
use Prex\Shared\Domain\Exception\Conflict;
use Prex\Shared\Domain\Exception\DomainException;
use Prex\Shared\Domain\ValueObject\UserId;

/**
 * Business rule: the same user cannot save the same gif twice.
 */
final class FavoriteGifAlreadySaved extends DomainException implements Conflict
{
    private function __construct(
        string $message,
        private readonly string $gifId,
        private readonly int $userId,
    ) {
        parent::__construct($message);
    }

    public static function create(UserId $userId, GifId $gifId): self
    {
        return new self(
            sprintf('The gif "%s" is already in the favorites of user %d.', $gifId->value, $userId->value),
            $gifId->value,
            $userId->value,
        );
    }

    public function errorCode(): string
    {
        return 'favorite_gif_already_saved';
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return ['gif_id' => $this->gifId, 'user_id' => $this->userId];
    }
}
