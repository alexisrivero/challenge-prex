<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Application;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prex\Auth\Application\Login\LoginCommand;
use Prex\Auth\Application\Login\LoginHandler;
use Prex\Auth\Domain\Email;
use Prex\Auth\Domain\Exception\InvalidCredentials;
use Prex\Auth\Domain\Exception\InvalidEmail;
use Prex\Auth\Domain\PlainPassword;
use Prex\Auth\Domain\User;
use Prex\Shared\Domain\ValueObject\UserId;
use Tests\Doubles\FakeAccessTokenIssuer;
use Tests\Doubles\FakePasswordHasher;
use Tests\Doubles\FixedClock;
use Tests\Doubles\InMemoryUserRepository;

/**
 * The Login use case is tested with plain PHPUnit: no database, no HTTP, no
 * framework container. Everything it needs arrives through ports.
 */
final class LoginHandlerTest extends TestCase
{
    private InMemoryUserRepository $users;

    private FakePasswordHasher $hasher;

    private FakeAccessTokenIssuer $issuer;

    private FixedClock $clock;

    private LoginHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->users = new InMemoryUserRepository;
        $this->hasher = new FakePasswordHasher;
        $this->clock = new FixedClock('2026-03-05 10:00:00');
        $this->issuer = new FakeAccessTokenIssuer($this->clock, ttlInSeconds: 1800);

        $this->handler = new LoginHandler($this->users, $this->hasher, $this->issuer, $this->clock);

        $this->users->add(new User(
            id: UserId::fromInt(7),
            name: 'Ada Lovelace',
            email: Email::fromString('ada@prex.local'),
            hashedPassword: $this->hasher->hash(PlainPassword::fromString('s3cret-pass')),
        ));
    }

    #[Test]
    public function it_issues_an_access_token_for_valid_credentials(): void
    {
        $response = $this->handler->handle(new LoginCommand('ada@prex.local', 's3cret-pass'));

        self::assertSame('token-for-user-7', $response->accessToken);
        self::assertSame('Bearer', $response->tokenType);
        self::assertSame(7, $response->userId);
        self::assertSame('Ada Lovelace', $response->userName);
        self::assertSame('ada@prex.local', $response->userEmail);
        self::assertCount(1, $this->issuer->issuedFor);
    }

    #[Test]
    public function the_issued_token_expires_in_thirty_minutes(): void
    {
        $response = $this->handler->handle(new LoginCommand('ada@prex.local', 's3cret-pass'));

        self::assertSame(1800, $response->expiresIn);
        self::assertSame('2026-03-05T10:30:00+00:00', $response->expiresAt->format(DATE_ATOM));
    }

    #[Test]
    public function it_normalises_the_email_before_looking_the_user_up(): void
    {
        $response = $this->handler->handle(new LoginCommand('  ADA@PREX.LOCAL ', 's3cret-pass'));

        self::assertSame(7, $response->userId);
    }

    #[Test]
    public function it_rejects_a_wrong_password(): void
    {
        $this->expectException(InvalidCredentials::class);

        $this->handler->handle(new LoginCommand('ada@prex.local', 'not-my-password'));
    }

    #[Test]
    public function it_rejects_an_unknown_email_without_leaking_that_it_is_unknown(): void
    {
        try {
            $this->handler->handle(new LoginCommand('nobody@prex.local', 's3cret-pass'));
            self::fail('An InvalidCredentials exception was expected.');
        } catch (InvalidCredentials $exception) {
            self::assertSame('The provided credentials are invalid.', $exception->getMessage());
            self::assertSame('invalid_credentials', $exception->errorCode());
        }
    }

    #[Test]
    public function it_does_not_issue_a_token_when_the_credentials_are_invalid(): void
    {
        try {
            $this->handler->handle(new LoginCommand('ada@prex.local', 'wrong'));
        } catch (InvalidCredentials) {
            // expected
        }

        self::assertSame([], $this->issuer->issuedFor);
    }

    #[Test]
    public function it_rejects_a_malformed_email(): void
    {
        $this->expectException(InvalidEmail::class);

        $this->handler->handle(new LoginCommand('not-an-email', 's3cret-pass'));
    }

    /**
     * Regression: an unknown email returned without hashing anything, so it
     * answered in ~12ms against ~220ms for a registered one - enough to
     * enumerate accounts by timing alone.
     */
    #[Test]
    public function an_unknown_email_costs_a_verification_too(): void
    {
        try {
            $this->handler->handle(new LoginCommand('nobody@prex.local', 's3cret-pass'));
            self::fail('Expected InvalidCredentials.');
        } catch (InvalidCredentials) {
            self::assertSame(1, $this->hasher->decoyVerifications());
        }
    }
}
