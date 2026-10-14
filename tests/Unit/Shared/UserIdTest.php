<?php

declare(strict_types=1);

namespace Tests\Unit\Shared;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prex\Shared\Domain\Exception\InvalidUserId;
use Prex\Shared\Domain\ValueObject\UserId;

final class UserIdTest extends TestCase
{
    #[Test]
    public function it_accepts_a_positive_integer(): void
    {
        self::assertSame(7, UserId::fromInt(7)->value);
        self::assertSame(7, UserId::fromString('7')->value);
    }

    #[Test]
    public function it_rejects_zero_and_negatives(): void
    {
        $this->expectException(InvalidUserId::class);

        UserId::fromInt(0);
    }

    #[Test]
    public function it_rejects_a_non_numeric_string(): void
    {
        $this->expectException(InvalidUserId::class);

        UserId::fromString('seven');
    }

    #[Test]
    public function it_compares_by_value(): void
    {
        self::assertTrue(UserId::fromInt(7)->equals(UserId::fromInt(7)));
        self::assertFalse(UserId::fromInt(7)->equals(UserId::fromInt(8)));
    }
}
