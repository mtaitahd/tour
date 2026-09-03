<?php

namespace Tests\Unit\Pricing;

use App\Pricing\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_parses_decimal_strings_exactly(): void
    {
        $this->assertEquals('684.10', Money::fromCents(68410)->toString());
        $this->assertEquals('100.00', Money::fromDecimalString('100')->toString());
        $this->assertEquals('100.50', Money::fromDecimalString('100.5')->toString());
        $this->assertEquals('0.25', Money::fromDecimalString('0.25')->toString());
        $this->assertEquals('0.00', Money::fromDecimalString('0')->toString());
    }

    public function test_rejects_malformed_and_negative_amounts(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Money::fromDecimalString('-5');
        $this->expectException(InvalidArgumentException::class);
        Money::fromDecimalString('1.234');
        $this->expectException(InvalidArgumentException::class);
        Money::fromDecimalString('abc');
    }

    public function test_additions_and_multiplication_are_integer_exact(): void
    {
        $a = Money::fromDecimalString('123.45');
        $b = Money::fromDecimalString('1.05');

        $this->assertSame(12450, $a->add($b)->cents());
        $this->assertSame(12240, $a->sub($b)->cents());
        $this->assertSame(24690, $a->multiply(2)->cents());
    }

    public function test_multiply_quantity_uses_thousandths(): void
    {
        $rate = Money::fromDecimalString('100.00');

        $this->assertSame(25000, $rate->multiplyQuantity(2500)->cents()); // 100 x 2.500 = 250.00
        $this->assertSame(3330, $rate->multiplyQuantity(333)->cents());   // 100 x 0.333 = 33.30
        $this->assertSame(0, $rate->multiplyQuantity(0)->cents());
    }

    public function test_divide_rounds_half_up_to_the_cent(): void
    {
        $this->assertSame(0, Money::fromCents(1)->divide(3)->cents());   // 0.01 / 3 = 0.00
        $this->assertSame(1, Money::fromCents(2)->divide(3)->cents());   // 0.02 / 3 = 0.01
        $this->assertSame(333, Money::fromCents(1000)->divide(3)->cents()); // 10.00 / 3 = 3.33
    }

    public function test_percent_application_is_integer_exact(): void
    {
        $this->assertSame(1800, Money::fromCents(10000)->applyPercentHundredths(1800)->cents()); // 18%
        $this->assertSame(9, Money::fromCents(50)->applyPercentHundredths(1800)->cents()); // 0.50 x 18% = 0.09
    }

    public function test_round_to_cent_multiple_half_up(): void
    {
        $this->assertSame(12300, Money::fromCents(12345)->roundToCentMultiple(100)->cents());  // nearest $1
        $this->assertSame(12500, Money::fromCents(12345)->roundToCentMultiple(500)->cents());  // nearest $5
        $this->assertSame(12000, Money::fromCents(12345)->roundToCentMultiple(1000)->cents()); // nearest $10
        $this->assertSame(10000, Money::fromCents(12345)->roundToCentMultiple(5000)->cents()); // nearest $50
        $this->assertSame(13000, Money::fromCents(12500)->roundToCentMultiple(1000)->cents()); // midpoint rounds up
    }

    public function test_output_is_never_a_messy_float_string(): void
    {
        // A value that would be 684.099999… in binary floating point.
        $result = Money::fromCents(68410)->toString();

        $this->assertSame('684.10', $result);
        $this->assertStringNotContainsString('9999', $result);
    }
}