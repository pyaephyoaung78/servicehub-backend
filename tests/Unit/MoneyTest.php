<?php

namespace Tests\Unit;

use App\Support\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_decimal_values_are_converted_to_exact_minor_units(): void
    {
        $this->assertSame(10025, Money::toMinor('100.25'));
        $this->assertSame(250010, Money::toMinor('2500.10'));
        $this->assertSame('100.25', Money::fromMinor(10025));
        $this->assertSame('-100.25', Money::fromMinor(-10025));
    }

    public function test_values_with_more_than_two_decimal_places_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::toMinor('100.125');
    }
}
