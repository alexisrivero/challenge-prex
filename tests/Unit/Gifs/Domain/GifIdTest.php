<?php

declare(strict_types=1);

namespace Tests\Unit\Gifs\Domain;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prex\Gifs\Domain\Exception\InvalidGifId;
use Prex\Gifs\Domain\GifId;

final class GifIdTest extends TestCase
{
    #[Test]
    #[DataProvider('validIds')]
    public function it_accepts_the_identifiers_used_by_the_provider(string $value, string $expected): void
    {
        self::assertSame($expected, GifId::fromString($value)->value);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function validIds(): array
    {
        return [
            'giphy alphanumeric id' => ['3oEjI6SIIHBdRxXI40', '3oEjI6SIIHBdRxXI40'],
            'numeric id as the challenge describes' => ['123456', '123456'],
            'with dashes and underscores' => ['abc-123_XYZ', 'abc-123_XYZ'],
            'surrounded by spaces' => ['  abc123  ', 'abc123'],
        ];
    }

    #[Test]
    #[DataProvider('invalidIds')]
    public function it_rejects_values_that_cannot_identify_a_gif(string $value): void
    {
        $this->expectException(InvalidGifId::class);

        GifId::fromString($value);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidIds(): array
    {
        return [
            'empty' => [''],
            'only spaces' => ['   '],
            'with slash' => ['abc/123'],
            'with spaces inside' => ['abc 123'],
            'too long' => [str_repeat('a', GifId::MAX_LENGTH + 1)],
        ];
    }

    #[Test]
    public function it_compares_by_value(): void
    {
        self::assertTrue(GifId::fromString('abc')->equals(GifId::fromString('abc')));
        self::assertFalse(GifId::fromString('abc')->equals(GifId::fromString('abd')));
    }
}
