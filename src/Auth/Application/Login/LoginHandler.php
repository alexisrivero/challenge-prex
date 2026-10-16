<?php

declare(strict_types=1);

namespace Prex\Auth\Application\Login;

use Prex\Auth\Domain\AccessTokenIssuer;
use Prex\Auth\Domain\Email;
use Prex\Auth\Domain\Exception\InvalidCredentials;
use Prex\Auth\Domain\PasswordHasher;
use Prex\Auth\Domain\PlainPassword;
use Prex\Auth\Domain\UserRepository;
use Prex\Shared\Domain\Clock;

/**
 * Use case: authenticate a user with email + password and hand back an OAuth2
 * access token.
 *
 * Everything it touches is a port, so the whole flow is unit testable without
 * a database nor an authorisation server.
 */
final readonly class LoginHandler
{
    public function __construct(
        private UserRepository $users,
        private PasswordHasher $passwordHasher,
        private AccessTokenIssuer $tokenIssuer,
        private Clock $clock,
    ) {}

    public function handle(LoginCommand $command): LoginResponse
    {
        $user = $this->users->findByEmail(Email::fromString($command->email));

        if ($user === null) {
            // Same work as the branch below, so that "unknown email" and
            // "wrong password" are indistinguishable from the outside.
            $this->passwordHasher->verifyDecoy(PlainPassword::fromString($command->password));

            throw InvalidCredentials::create();
        }

        if (! $user->authenticate(PlainPassword::fromString($command->password), $this->passwordHasher)) {
            throw InvalidCredentials::create();
        }

        $token = $this->tokenIssuer->issueFor($user);

        return new LoginResponse(
            accessToken: $token->value(),
            tokenType: $token->type,
            expiresIn: $token->expiresIn($this->clock->now()),
            expiresAt: $token->expiresAt,
            userId: $user->id->value,
            userName: $user->name,
            userEmail: $user->email->value,
        );
    }
}
