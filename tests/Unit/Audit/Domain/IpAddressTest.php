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
}
