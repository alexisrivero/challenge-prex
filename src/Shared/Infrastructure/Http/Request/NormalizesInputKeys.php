<?php

declare(strict_types=1);

namespace Prex\Shared\Infrastructure\Http\Request;

/**
 * The challenge documents the inputs in upper case (QUERY, LIMIT, GIF_ID...),
 * while the de-facto standard for JSON APIs is snake_case. Instead of choosing
 * one and breaking the other, every request accepts both spellings: the keys
 * are lower-cased before validation.
 */
trait NormalizesInputKeys
{
    protected function prepareForValidation(): void
    {
        $input = $this->all();
        $normalised = [];

        foreach ($input as $key => $value) {
            $lowered = strtolower((string) $key);

            if ($lowered !== $key && ! array_key_exists($lowered, $input)) {
                $normalised[$lowered] = $value;
            }
        }

        if ($normalised !== []) {
            $this->merge($normalised);
        }
    }
}
