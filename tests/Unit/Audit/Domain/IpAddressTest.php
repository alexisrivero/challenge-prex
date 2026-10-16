<?php

declare(strict_types=1);

namespace Tests\Unit\Audit\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prex\Audit\Domain\Exception\InvalidIpAddress;
use Prex\Audit\Domain\IpAddress;

final class IpAddressTest extends TestCase
{
    #[Test]
    public function it_accepts_ipv4_and_ipv6(): void
    {
        self::assertSame('192.168.0.10', IpAddress::fromString('192.168.0.10')->value);
        self::assertSame('2001:db8::1', IpAddress::fromString('2001:db8::1')->value);
    }

    #[Test]
    public function a_missing_origin_is_recorded_as_unknown(): void
    {
        $ip = IpAddress::fromString(null);

        self::assertSame(IpAddress::UNKNOWN, $ip->value);
        self::assertFalse($ip->isKnown());
    }

    #[Test]
    public function it_rejects_a_value_that_is_not_an_ip(): void
    {
        $this->expectException(InvalidIpAddress::class);

        IpAddress::fromString('not-an-ip');
    }

    /**
     * Regression: `unknown` was written for a missing origin but rejected when
     * read again, so listing the audit trail of that user blew up.
     */
    #[Test]
    public function the_unknown_marker_can_be_read_back(): void
    {
        $stored = IpAddress::fromString(null)->value;

        self::assertSame(IpAddress::UNKNOWN, IpAddress::fromString($stored)->value);
    }
}
