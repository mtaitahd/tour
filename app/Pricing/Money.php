<?php

namespace App\Pricing;

use InvalidArgumentException;

/**
 * Immutable money value using integer minor units (1 unit = 0.01). This is
 * the project's decimal-safe strategy: no binary floating point is ever used
 * for monetary arithmetic. All division (`divide`, `applyPercentHundredths`,
 * `multiplyQuantity`) rounds half-up at a single defined point (the smallest
 * resulting cent), so intermediate values can never appear as 684.09999… .
 *
 * Precision contract:
 * - Internal precision: integer minor units, exactly 2 decimal places.
 * - Rounding point: every derived money value rounds half-up to the cent,
 *   except the final per-person price which may be rounded further by the
 *   configured rounding rule.
 * - Database precision: decimal(12, 2) for all money columns.
 * - JSON serialization: `toString()` — decimal strings with exactly 2 decimal
 *   places (never floats, never scientific notation).
 */
final class Money
{
    private function __construct(private int $cents)
    {
    }

    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    /**
     * Parses a non-negative decimal string with up to 2 decimal places.
     *
     * @throws InvalidArgumentException on malformed or negative input.
     */
    public static function fromDecimalString(mixed $amount): self
    {
        if ($amount instanceof Money) {
            return $amount;
        }

        if (! is_string($amount) && ! is_int($amount) && ! is_float($amount)) {
            throw new InvalidArgumentException('Money amount must be a decimal string, integer or Money.');
        }

        if (is_int($amount)) {
            return self::fromCents($amount);
        }

        if (is_float($amount)) {
            $amount = number_format($amount, 2, '.', '');
        }

        $amount = trim($amount);

        if (! preg_match('/^\d+(\.\d{1,2})?$/', $amount)) {
            throw new InvalidArgumentException(
                "Invalid money amount '{$amount}' (expected non-negative with up to 2 decimal places)."
            );
        }

        if (str_contains($amount, '.')) {
            [$whole, $fraction] = explode('.', $amount, 2);
            $fraction = str_pad($fraction, 2, '0');

            return new self(((int) $whole * 100) + (int) $fraction);
        }

        return new self((int) $amount * 100);
    }

    public function cents(): int
    {
        return $this->cents;
    }

    public function add(Money $other): self
    {
        return new self($this->cents + $other->cents);
    }

    public function sub(Money $other): self
    {
        $result = $this->cents - $other->cents;

        // Engine validates non-negative inputs; this guards against drift.
        if ($result < 0) {
            throw new InvalidArgumentException('Money cannot become negative during subtraction.');
        }

        return new self($result);
    }

    public function multiply(int $factor): self
    {
        if ($factor < 0) {
            throw new InvalidArgumentException('Money cannot be multiplied by a negative factor.');
        }

        return new self($this->cents * $factor);
    }

    /**
     * Multiplies by a decimal quantity expressed in thousandths (3 dp).
     * E.g. quantity 2.500 is 2500 thousandths. Rounds half-up to the cent.
     */
    public function multiplyQuantity(int $thousandths): self
    {
        if ($thousandths < 0) {
            throw new InvalidArgumentException('Quantity in thousandths cannot be negative.');
        }

        return new self(intdiv(($this->cents * $thousandths) + 500, 1000));
    }

    /**
     * Divides into evenly sized portions, rounding half-up to the cent.
     * Used for per-person prices (cost_total / clients).
     */
    public function divide(int $divisor): self
    {
        if ($divisor < 1) {
            throw new InvalidArgumentException('Money cannot be divided by zero or a negative value.');
        }

        return new self(intdiv(($this->cents * 2) + $divisor, $divisor * 2));
    }

    /**
     * Applies a percentage expressed in hundredths of a percent (2 dp of the
     * percentage). E.g. 18.00 % = 1800, 18.5 % = 1850. Rounds half-up to the
     * cent at the markup application point.
     */
    public function applyPercentHundredths(int $percentHundredths): self
    {
        if ($percentHundredths < 0) {
            throw new InvalidArgumentException('Percentage cannot be negative.');
        }

        return new self(intdiv(($this->cents * $percentHundredths) + 5000, 10000));
    }

    /**
     * Rounds to the nearest cent multiple, half-up. `centMultiple` of 100
     * rounds to the nearest dollar, 500 to the nearest 5 dollars, etc.
     */
    public function roundToCentMultiple(int $centMultiple): self
    {
        if ($centMultiple < 1) {
            throw new InvalidArgumentException('Cent multiple must be positive.');
        }

        if ($centMultiple === 1) {
            return $this;
        }

        return new self(intdiv($this->cents + intdiv($centMultiple, 2), $centMultiple) * $centMultiple);
    }

    public function isZero(): bool
    {
        return $this->cents === 0;
    }

    public function equals(Money $other): bool
    {
        return $this->cents === $other->cents;
    }

    public function toString(): string
    {
        return sprintf('%d.%02d', intdiv($this->cents, 100), $this->cents % 100);
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}