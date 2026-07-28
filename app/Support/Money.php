<?php

namespace App\Support;

use InvalidArgumentException;

final class Money
{
    public const SCALE = 2;

    public static function toMinor(mixed $amount): int
    {
        if ($amount === null || $amount === '') {
            return 0;
        }

        $value = trim((string) $amount);

        if (! preg_match('/^[+-]?\d+(?:\.\d{1,2})?$/', $value)) {
            throw new InvalidArgumentException(
                'Money values must be decimal numbers with at most two decimal places.'
            );
        }

        $isNegative = str_starts_with($value, '-');
        $unsignedValue = ltrim($value, '+-');
        [$whole, $fraction] = array_pad(
            explode('.', $unsignedValue, 2),
            2,
            ''
        );

        $minor = ((int) $whole * (10 ** self::SCALE))
            + (int) str_pad($fraction, self::SCALE, '0');

        return $isNegative ? -$minor : $minor;
    }

    public static function fromMinor(int $amount): string
    {
        $isNegative = $amount < 0;
        $absoluteAmount = abs($amount);
        $whole = intdiv($absoluteAmount, 10 ** self::SCALE);
        $fraction = $absoluteAmount % (10 ** self::SCALE);
        $value = $whole.'.'.str_pad(
            (string) $fraction,
            self::SCALE,
            '0',
            STR_PAD_LEFT
        );

        return $isNegative ? '-'.$value : $value;
    }
}
