<?php

declare(strict_types=1);

namespace Prex\Gifs\Domain;

use Prex\Gifs\Domain\Exception\InvalidSearchTerm;

final readonly class SearchTerm
{
    public const int MAX_LENGTH = 255;

    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $normalised = trim(preg_replace('/\s+/', ' ', $value) ?? '');

        if ($normalised === '') {
            throw InvalidSearchTerm::empty();
        }

        if (mb_strlen($normalised) > self::MAX_LENGTH) {
            throw InvalidSearchTerm::tooLong($normalised);
        }

        return new self($normalised);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
