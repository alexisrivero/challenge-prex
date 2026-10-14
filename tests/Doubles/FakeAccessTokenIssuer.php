<?php

declare(strict_types=1);

namespace Tests\Doubles;

use DateInterval;
use Prex\Auth\Domain\AccessToken;
use Prex\Auth\Domain\AccessTokenIssuer;
use Prex\Auth\Domain\User;
use Prex\Shared\Domain\Clock;

final class FakeAccessTokenIssuer implements AccessTokenIssuer
{
    /** @var list<User> */
    public array $issuedFor = [];

    public function __construct(
        private readonly Clock $clock,
        private readonly int $ttlInSeconds = 1800,
    ) {}

    public function issueFor(User $user): AccessToken
    {
        $this->issuedFor[] = $user;

        return new AccessToken(
            value: 'token-for-user-'.$user->id->value,
            expiresAt: $this->clock->now()->add(new DateInterval('PT'.$this->ttlInSeconds.'S')),
        );
    }
}
