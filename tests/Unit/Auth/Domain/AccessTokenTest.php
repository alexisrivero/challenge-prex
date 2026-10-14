<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Domain;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prex\Auth\Domain\AccessToken;

final class AccessTokenTest extends TestCase
{
    #[Test]
    public function it_computes_the_remaining_lifetime(): void
    {
        $token = new AccessToken('jwt', new DateTimeImmutable('2026-03-05 10:30:00'));

        self::assertSame(1800, $token->expiresIn(new DateTimeImmutable('2026-03-05 10:00:00')));
    }

    #[Test]
    public function an_expired_token_never_reports_a_negative_lifetime(): void
    {
        $token = new AccessToken('jwt', new DateTimeImmutable('2026-03-05 10:00:00'));

        self::assertSame(0, $token->expiresIn(new DateTimeImmutable('2026-03-05 11:00:00')));
    }

    #[Test]
    public function it_hides_the_token_value_from_dumps(): void
    {
        $token = new AccessToken('super-secret-jwt', new DateTimeImmutable('2026-03-05 10:30:00'));

        self::assertStringNotContainsString('super-secret-jwt', print_r($token->__debugInfo(), true));
    }
}
