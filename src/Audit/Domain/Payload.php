<?php

declare(strict_types=1);

namespace Prex\Audit\Domain;

/**
 * Body of a request or a response, ready to be audited.
 *
 * Three domain rules live here, because they are policies about *what we are
 * allowed to store*, not technical details:
 *  - secrets are never persisted in clear text ({@see redacting()});
 *  - a payload is capped in size so a huge response cannot flood the audit
 *    trail ({@see truncated()});
 *  - a payload is always valid UTF-8, so that nothing a client sends can make
 *    it unstorable and thus escape the audit trail.
 */
final readonly class Payload
{
    public const string REDACTED = '[redacted]';

    public const string TRUNCATED_KEY = '_truncated';

    /**
     * U+FFFD REPLACEMENT CHARACTER, written over every byte sequence that is
     * not valid UTF-8.
     */
    private const int SUBSTITUTE = 0xFFFD;

    /** @var array<array-key, mixed> */
    public array $data;

    /**
     * @param  array<array-key, mixed>  $data
     */
    private function __construct(array $data)
    {
        // Enforced here, once, so every instance is storable by construction:
        // a request carrying invalid UTF-8 (`?query=%FF`) would otherwise make
        // the JSON encoding of the audit row fail, and the interaction would
        // never be persisted.
        $this->data = self::intoValidUtf8($data);
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * Accepts whatever a transport gives us (array, JSON string, scalar, null)
     * and normalises it into a structured payload.
     */
    public static function fromMixed(mixed $value): self
    {
        if ($value === null || $value === '' || $value === []) {
            return self::empty();
        }

        if (is_array($value)) {
            return new self($value);
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            return new self(is_array($decoded) ? $decoded : ['raw' => $value]);
        }

        return new self(['raw' => $value]);
    }

    /**
     * @param  list<string>  $keys  case insensitive keys whose value must be hidden
     */
    public function redacting(array $keys): self
    {
        if ($keys === [] || $this->data === []) {
            return $this;
        }

        $lowered = array_map('strtolower', $keys);

        return new self($this->redactRecursively($this->data, $lowered));
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @param  list<string>  $keys
     * @return array<array-key, mixed>
     */
    private function redactRecursively(array $data, array $keys): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if (is_string($key) && in_array(strtolower($key), $keys, true)) {
                $result[$key] = self::REDACTED;

                continue;
            }

            $result[$key] = is_array($value) ? $this->redactRecursively($value, $keys) : $value;
        }

        return $result;
    }

    /**
     * Replaces the content by a short preview when the encoded payload exceeds
     * the given size.
     */
    public function truncated(int $maxBytes): self
    {
        if ($maxBytes <= 0 || $this->data === []) {
            return $this;
        }

        $encoded = $this->toJson();

        if (strlen($encoded) <= $maxBytes) {
            return $this;
        }

        return new self([
            self::TRUNCATED_KEY => true,
            'bytes' => strlen($encoded),
            // mb_strcut, not substr: cutting in the middle of a multibyte
            // character would produce a preview that cannot be encoded.
            'preview' => mb_strcut($encoded, 0, $maxBytes, 'UTF-8'),
        ]);
    }

    public function isEmpty(): bool
    {
        return $this->data === [];
    }

    /**
     * @return array<array-key, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Recursively replaces invalid UTF-8 in both keys and values.
     *
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    private static function intoValidUtf8(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            $key = is_string($key) ? self::stringIntoValidUtf8($key) : $key;

            $result[$key] = match (true) {
                is_array($value) => self::intoValidUtf8($value),
                is_string($value) => self::stringIntoValidUtf8($value),
                default => $value,
            };
        }

        return $result;
    }

    private static function stringIntoValidUtf8(string $value): string
    {
        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        $previous = mb_substitute_character();
        mb_substitute_character(self::SUBSTITUTE);

        try {
            return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        } finally {
            mb_substitute_character($previous);
        }
    }

    public function toJson(): string
    {
        return json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE) ?: '{}';
    }
}
