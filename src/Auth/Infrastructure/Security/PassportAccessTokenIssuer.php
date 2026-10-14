<?php

declare(strict_types=1);

namespace Prex\Auth\Infrastructure\Security;

use DateInterval;
use Prex\Auth\Domain\AccessToken;
use Prex\Auth\Domain\AccessTokenIssuer;
use Prex\Auth\Domain\Exception\InvalidCredentials;
use Prex\Auth\Domain\User;
use Prex\Auth\Infrastructure\Persistence\Eloquent\UserModel;
use Prex\Shared\Domain\Clock;

/**
 * Adapter for the AccessTokenIssuer port backed by Laravel Passport.
 *
 * Passport is a full OAuth2 server: the token returned here is a signed JWT
 * registered in `oauth_access_tokens` and validated by the `api` guard on every
 * subsequent request. Its lifetime is configured in `config/oauth.php`
 * (30 minutes, as required by the challenge).
 */
final readonly class PassportAccessTokenIssuer implements AccessTokenIssuer
{
    public function __construct(
        private Clock $clock,
        private string $tokenName = 'api',
        /** @var list<string> */
        private array $scopes = [],
    ) {}

    public function issueFor(User $user): AccessToken
    {
        $model = UserModel::query()->find($user->id->value);

        if ($model === null) {
            // The user vanished between the lookup and the token issuance.
            throw InvalidCredentials::create();
        }

        $result = $model->createToken($this->tokenName, $this->scopes);

        return new AccessToken(
            value: (string) $result->accessToken,
            expiresAt: $this->clock->now()->add(
                new DateInterval(sprintf('PT%dS', (int) $result->expiresIn))
            ),
            type: AccessToken::TYPE_BEARER,
        );
    }
}
