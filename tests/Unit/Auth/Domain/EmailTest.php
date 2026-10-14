<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Domain;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prex\Auth\Domain\Email;
use Prex\Auth\Domain\Exception\InvalidEmail;

final class EmailTest extends TestCase
{
    #[Test]
    public function it_normalises_case_and_spaces(): void
    {
        self::assertSame('ada@prex.local', Email::fromString('  Ada@Prex.Local  ')->value);
    }

    #[Test]
    public function two_emails_with_different_case_are_equal(): void
    {
        self::assertTrue(Email::fromString('ada@prex.local')->equals(Email::fromString('ADA@prex.local')));
    }

    #[Test]
    #[DataProvider('invalidEmails')]
    public function it_rejects_invalid_values(string $value): void
    {
        $this->expectException(InvalidEmail::class);

        Email::fromString($value);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidEmails(): array
    {
        return [
            'empty' => [''],
            'only spaces' => ['   '],
            'missing at' => ['ada.prex.local'],
            'missing domain' => ['ada@'],
            'with spaces' => ['ada lovelace@prex.local'],
        ];
    }
}
