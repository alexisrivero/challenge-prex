<?php

declare(strict_types=1);

namespace Tests\Unit\Audit\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prex\Audit\Domain\Payload;

final class PayloadTest extends TestCase
{
    #[Test]
    public function it_redacts_sensitive_keys_regardless_of_their_case(): void
    {
        $payload = Payload::fromArray([
            'email' => 'ada@prex.local',
            'Password' => 'super-secret',
        ])->redacting(['password']);

        self::assertSame([
            'email' => 'ada@prex.local',
            'Password' => Payload::REDACTED,
        ], $payload->toArray());
    }

    #[Test]
    public function it_redacts_nested_keys(): void
    {
        $payload = Payload::fromArray([
            'data' => [
                'user' => ['email' => 'ada@prex.local'],
                'access_token' => 'jwt',
            ],
        ])->redacting(['access_token']);

        self::assertSame(Payload::REDACTED, $payload->toArray()['data']['access_token']);
        self::assertSame('ada@prex.local', $payload->toArray()['data']['user']['email']);
    }

    #[Test]
    public function it_decodes_a_json_string(): void
    {
        $payload = Payload::fromMixed('{"query":"cats"}');

        self::assertSame(['query' => 'cats'], $payload->toArray());
    }

    #[Test]
    public function it_keeps_a_non_json_body_as_raw_content(): void
    {
        $payload = Payload::fromMixed('<html>oops</html>');

        self::assertSame(['raw' => '<html>oops</html>'], $payload->toArray());
    }

    #[Test]
    public function an_absent_body_becomes_an_empty_payload(): void
    {
        self::assertTrue(Payload::fromMixed(null)->isEmpty());
        self::assertTrue(Payload::fromMixed('')->isEmpty());
        self::assertTrue(Payload::fromMixed([])->isEmpty());
    }

    #[Test]
    public function it_truncates_payloads_bigger_than_the_allowed_size(): void
    {
        $payload = Payload::fromArray(['blob' => str_repeat('a', 1000)])->truncated(100);

        self::assertTrue($payload->toArray()[Payload::TRUNCATED_KEY]);
        self::assertSame(1011, $payload->toArray()['bytes']);
        self::assertSame(100, strlen((string) $payload->toArray()['preview']));
    }

    #[Test]
    public function it_keeps_payloads_within_the_allowed_size_untouched(): void
    {
        $payload = Payload::fromArray(['query' => 'cats'])->truncated(100);

        self::assertSame(['query' => 'cats'], $payload->toArray());
    }
}
